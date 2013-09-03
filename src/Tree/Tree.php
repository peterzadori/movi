<?php

namespace movi\Tree;

use Nette\Object;
use Nette\Utils\Strings;
use movi\InvalidArgumentException;
use movi\Model\TreeRepository;

abstract class Tree extends Object
{

	const PATH_SEPARATOR = '/',
		TAB = '-';

	/** @var TreeRepository */
	protected $repository;

	/** @var array */
	protected $nodes = array();

	/** @var array */
	protected $parents = array();

	/** @var array */
	protected $children = array();

	protected $root;

	protected $built = false;

	public $onBuild;

	public $onFetch;

	public $onAdd;

	public $onMove;

	public $onDelete;


	/**
	 * @param TreeRepository $repository
	 * @return $this
	 */
	public function setRepository(TreeRepository $repository)
	{
		$this->repository = $repository;

		return $this;
	}


	/**
	 * @param array $nodes
	 */
	public function build(array $nodes = NULL)
	{
		if ($this->built === false) {
			if ($nodes === NULL) {
				$nodes = $this->repository->findAll();
			}

			foreach ($nodes as $node)
			{
				$this->onFetch($node);

				$this->nodes[$node->id] = $node;

				if ($node->parent === NULL) {
					$this->parents[$node->order] = $node;
				} else {
					$this->children[$node->parent->id][$node->order] = $node;
				}

				if ($node->root === true) {
					$this->root = $node;
				}
			}

			$this->onBuild($this);

			$this->built = true;
		}
	}


	public function rebuild()
	{
		$this->built = false;
		$this->nodes = array();
		$this->children = array();
		$this->parents = array();

		$this->build();
	}


	/**
	 * @return mixed
	 */
	public function getNodes()
	{
		return $this->nodes;
	}


	/**
	 * @param $id
	 * @return mixed|TreeEntity
	 * @throws NodeNotFound
	 */
	public function getNode($id)
	{
		if (is_int($id)) {
			if (isset($this->nodes[$id])) {
				return $this->nodes[$id];
			} else {
				throw new NodeNotFound('Node not found.');
			}
		}

		$children = $this->parents;

		do {
			$nodes = explode(self::PATH_SEPARATOR, $id);
			$node = array_shift($nodes);

			$node = $this->findNode($node, $children);

			if ($node === NULL) {
				throw new NodeNotFound('Node not found.');
			}

			if ($node->path !== $id) {
				$id = implode(self::PATH_SEPARATOR, $nodes);
				$children = $this->getChildren($node);
			} else {
				break;
			}
		} while (true);

		return $node;
	}


	/**
	 * @param $child
	 * @param $parent
	 * @return null
	 */
	public function addChild($child, $parent = NULL)
	{
		if (!$child->isDetached()) {
			if (($child->parent === NULL && $parent === NULL) || ($child->parent !== NULL && $parent !== NULL && $child->parent->id === $parent->id)) {
				return NULL;
			}
		}

		if ($parent === NULL) {
			$children = $this->parents;
		} else {
			$children = $this->getChildren($parent);
		}

		$child->parent = $parent;

		if (empty($children)) {
			$child->order = 1;
		} else {
			$max = end($children);
			$child->order = $max->order + 1;
		}

		if ($parent === NULL && $child->order === 1) {
			$child->root = true;
		}

		$this->onAdd($child, $parent);

		$this->repository->persist($child);

		$this->rebuild();
	}


	/**
	 * @param $node
	 * @param $parent
	 * @return bool
	 */
	public function moveNode($node, $parent = NULL)
	{
		if ($parent !== NULL) {
			// Get node's children
			$children = $this->getChildrenRecursively($node);

			foreach ($children as $child)
			{
				if ($child->id === $parent->id) {
					return false;
				}
			}
		}

		// Reorder
		$this->reorder($node);
		$this->addChild($node, $parent);

		$this->onMove($node, $parent);
	}


	/**
	 * @param $node
	 * @param bool $reorder
	 * @return mixed
	 */
	public function deleteNode($node, $reorder = true)
	{
		if (!isset($this->nodes[$node->id])) {
			return false;
		}

		if ($reorder === true) {
			$this->reorder($node);
		}

		if ($node->root === true) {
			$siblings = $this->getSiblings($node);
			$root = array_shift($siblings);

			if (!empty($root)) {
				$this->setRoot($root);
			}
		}

		$this->repository->delete($node);
		$children = $this->getChildren($node);

		if (!empty($children)) {
			foreach ($children as $child)
			{
				return $this->deleteNode($child, false);
			}
		}

		$this->rebuild();
	}


	/**
	 * @param $node
	 * @return mixed
	 */
	public function saveNode($node)
	{
		$this->repository->persist($node);

		return $node;
	}


	public function setRoot($node)
	{
		if ($this->root !== NULL) {
			$this->root->root = false;
			$this->repository->persist($this->root);
		}

		$node->root = true;
		$this->repository->persist($node);

		$this->root = $node;
	}


	public function getRoot()
	{
		return $this->root;
	}


	/**
	 * @param $node
	 */
	private function reorder($node)
	{
		$siblings = $this->getSiblings($node);

		if (!empty($siblings)) {
			$siblings = array_slice($siblings, $node->order - 1);

			if (!empty($siblings)) {
				foreach ($siblings as $sibling)
				{
					$sibling->order = $sibling->order - 1;
					$this->repository->persist($sibling);
				}
			}
		}
	}


	/**
	 * @param $slug
	 * @param $nodes
	 * @return mixed
	 */
	private function findNode($slug, $nodes)
	{
		foreach ($nodes as $node)
		{
			if ($node->path == $slug) {
				return $node;
			}
		}
	}


	/**
	 * @param $node
	 * @return TreeEntity[]
	 */
	public function getParents($node = NULL)
	{
		if ($node === NULL) {
			return $this->parents;
		}

		$parents = array();
		while ($node->parent !== NULL)
		{
			$node = $this->nodes[$node->parent->id];

			$parents[] = $node;
		}

		krsort($parents);
		return $parents;
	}


	/**
	 * @param $node
	 * @return TreeEntity[]
	 */
	public function getChildren($node = NULL)
	{
		if ($node === NULL) {
			return $this->parents;
		} else {
			if (isset($this->children[$node->id])) {
				return $this->children[$node->id];
			} else {
				return array();
			}
		}
	}


	/**
	 * @param $node
	 * @param array $tree
	 * @return array
	 */
	public function getChildrenRecursively($node, &$tree = array())
	{
		$children = $this->getChildren($node);

		if (!empty($children)) {
			foreach ($children as $child)
			{
				$tree[$child->id] = $this->getNode($child->id);

				$this->getChildrenRecursively($child, $tree);
			}
		}

		return $tree;
	}


	/**
	 * @param $node
	 * @return array
	 */
	public function getSiblings($node)
	{
		$siblings = array();
		$nodes = $this->getChildren($node->parent);

		foreach ($nodes as $sibling)
		{
			if ($sibling->id === $node->id) {
				continue;
			}

			$siblings[] = $sibling;
		}

		return $siblings;
	}


	/**
	 * @param $node
	 * @return array
	 */
	public function getPath($node)
	{
		$nodes = $this->getParents($node);
		$nodes[] = $node;
		$path = array();

		foreach ($nodes as $part)
		{
			$path[] = $part->path;
		}

		return implode('/', $path);
	}


	/**
	 * @param null $exclude
	 * @param array $nodes
	 * @param int $level
	 * @param array $options
	 * @return array
	 */
	public function getArray($exclude = NULL, $nodes = array(), $level = 0, &$options = array())
	{
		if ($level === 0) {
			$nodes = $this->parents;
		}

		if (!empty($nodes)) {
			foreach ($nodes as $child)
			{
				if (($exclude !== NULL && $exclude === $child->id) || $child->hidden === true) {
					continue;
				}

				$child->name = sprintf('%s %s', str_repeat(self::TAB, $level), $child->name);
				$options[$child->id] = $child;

				$children = $this->getChildren($child);
				if (!empty($children)) {
					$this->getArray($exclude, $children, $level + 1, $options);
				}
			}
		}

		return $options;
	}


	/**
	 * @param array $children
	 * @param array $tree
	 * @return array
	 */
	public function getTree($children = array(), &$tree = array())
	{
		if (empty($children) && empty($tree)) {
			$children = $this->parents;
		}

		if (!empty($children)) {
			foreach ($children as $child)
			{
				if ($child->hidden === true) {
					continue;
				}

				$tree[$child->id] = $child;

				$children = $this->getChildren($child);
				if (!empty($children)) {
					$this->getTree($children, $tree);
				}
			}
		}

		return $tree;
	}


	/**
	 * @param $callback
	 * @param null $nodes
	 * @throws \movi\InvalidArgumentException
	 */
	public function walk($callback, $nodes = NULL)
	{
		if (!is_callable($callback)) {
			throw new InvalidArgumentException('Callback is not callable!');
		}

		if ($nodes === NULL) {
			$nodes = $this->parents;
		}

		foreach ($nodes as $node)
		{
			$callback($node);

			$children = $this->getChildren($node);
			if (!empty($children)) {
				$this->walk($callback, $children);
			}
		}
	}

}