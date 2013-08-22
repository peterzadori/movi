<?php

namespace movi\Tree;

use Nette\ArrayHash;
use Nette\Object;
use movi\Model\Repositories\Repository;
use movi\Tree\NodeNotFound;
use movi\InvalidStateException;
use movi\Model\Entities\Entity;

abstract class Tree extends Object
{

	public $tab = '-';

	/** @var \movi\Model\Repositories\Repository */
	protected $repository;

	/** @var array */
	protected $rows = array();

	/** @var array */
	protected $nodes = array();

	/** @var array */
	protected $parents = array();

	/** @var array */
	protected $children = array();

	/** @var bool */
	protected $built = false;

	public $onBuild;

	public $onDelete;


    /**
     * @param Repository $repository
     */
    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;

		$this->build();
    }


	/**
	 * @return array
	 * @throws InvalidStateException
	 */
	public function build()
	{
		if ($this->repository === NULL) {
			throw new InvalidStateException('Repository is not set.');
		}

		if (!$this->built) {
			$nodes = $this->repository->findAll();

			foreach ($nodes as $node)
			{
				$row = ArrayHash::from($node->getRowData());

				$this->nodes[$row->id] = $node;
				$this->rows[$row->id] = $row;

				if ($row->parent_id === NULL) {
					$this->parents[$row->id] = $row;
				} else {
					$this->children[$row->parent_id][$row->id] = $row;
				}
			}

			$this->built = true;

			$this->onBuild($this);
		}
	}


	/**
	 * @return array
	 */
	public function rebuild()
	{
		$this->built = false;
		$this->rows = array();
		$this->nodes = array();
		$this->parents = array();
		$this->children = array();

		return $this->build();
	}


	/**
	 * @param $id
	 * @return Entity|NULL
	 */
	public function getNode($id)
	{
		if (isset($this->nodes[$id])) {
			return $this->nodes[$id];
		} else {
			return NULL;
		}
	}


	/**
	 * @return array
	 */
	public function getNodes()
	{
		return $this->nodes;
	}


	/**
	 * @param $id
	 * @return ArrayHash
	 */
	public function getRow($id)
	{
		if ($id instanceof Entity) {
			$id = $id->id;
		}

		if (isset($this->rows[$id])) {
			return $this->rows[$id];
		} else {
			return NULL;
		}
	}


	/**
	 * @param null $node
	 * @return array
	 */
	public function getParents($node = NULL)
	{
		if ($node === NULL) {
			return $this->parents;
		}

		$node = $this->getRow($node);
		$parents = array();

		while ($node->parent_id !== NULL) {
			$node = $this->getRow($node->parent_id);

			$parents[$node->id] = $node;
		}

		krsort($parents);
		return $parents;
	}


	/**
	 * @param null $node
	 * @return array
	 */
	public function getChildren($node = NULL)
	{
		if ($node === NULL) {
			return $this->children;
		}

		if (array_key_exists($node->id, $this->children)) {
			return $this->children[$node->id];
		} else {
			return array();
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

		if (count($children) > 0) {
			foreach ($children as $id => $child)
			{
				$tree[$id] = $this->getNode($id);

				$this->getChildrenRecursively($child, $tree);
			}
		}

		return $tree;
	}


	/**
	 * @param $children
	 * @param callable $callback
	 */
	public function traverseTree($children, \Closure $callback)
    {
        if (count($children) > 0) {
            foreach ($children as $id => $child)
            {
				$child = $this->getNode($id);

                $callback($child);

                if (array_key_exists($id, $this->children)) {
                    $this->traverseTree($this->children[$id], $callback);
                }
            }
        }
    }


    /**
     * @param $url
     * @param array $children
     * @return bool
     * @throws NodeNotFound
     */
    public function findNode($url, $children = array())
	{
		if (empty($children)) {
			$children = $this->parents;
		}

		do {
			$nodes = explode('/', $url);
			$node = array_shift($nodes);

			$node = $this->findByPath($node, $children);

			if ($node === NULL) {
				throw new NodeNotFound();
			}

			if ($node->path !== $url) {
				$url = implode('/', $nodes);
				$children = $this->getChildren($node);
			} else {
				break;
			}
		} while (true);

		return $node;
	}


	/**
	 * @param $path
	 * @param $nodes
	 * @return bool
	 */
	private function findByPath($path, $nodes)
	{
		foreach ($nodes as $node)
		{
			if ($node->path == $path) {
				return $node;
			}
		}

		return NULL;
	}


	/**
	 * @param $node
	 * @return array
	 */
	public function getPath($node)
	{
		// Return only paths
		$path = array();

		$node = $this->getRow($node);
		$parent = $node;

		while ($parent->parent_id !== NULL) {
			$parent = $this->getRow($parent->parent_id);

			$path[] = $parent->path;
		}

		krsort($path);
		$path[] = $node->path;

		return $path;
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

		if (count($children) > 0) {
			foreach ($children as $id => $child)
			{
				if ($child->hidden == true) {
					continue;
				}

				$child = $this->getNode($id);
				$tree[$id] = $child;

				if (array_key_exists($id, $this->children)) {
					$this->getTree($this->children[$id], $tree);
				}
			}
		}

		return $tree;
	}


	/**
	 * @param $children
	 * @param int $parent
	 * @return array
	 */
	function getTreeRecursively($children = array(), $parent = 0)
	{
		$branch = array();

		if ($parent == 0) {
			$children = $this->parents;
		}

		foreach ($children as $id => $child)
		{
			$child->children = array();

			if ($child->parent_id == $parent) {
				if (isset($this->children[$id])) {
					$children = $this->getTreeRecursively($this->children[$id], $id);

					if ($children) {
						$child->children = $children;
					}
				}

				$child = $this->getNode($id);
				$branch[$id] = $child;
			}
		}

		return $branch;
	}


    /**
     * @param null $exclude
     * @param array $children
     * @param int $level
     * @param array $options
     * @return array
     */
    public function getArray($exclude = NULL, $children = array(), $level = 0, &$options = array())
	{
		if ($level === 0) {
			$children = $this->parents;
		}

		if (count($children) > 0) {
			foreach ($children as $id => $child)
			{
				if ($id == $exclude || $child->hidden) {
					continue;
				}

				$options[$id] = sprintf('%s %s', str_repeat($this->tab, $level), $child->title);

				if (array_key_exists($id, $this->children)) {
					$this->getArray($exclude, $this->children[$id], $level + 1, $options);
				}
			}
		}

		return $options;
	}


	/**
	 * @param $node
	 */
	public function delete($node)
	{
		// Delete the node
		$this->onDelete($node);

		if (array_key_exists($node->id, $this->children)) {
			foreach ($this->children[$node->id] as $id => $child)
			{
				$child = $this->getNode($id);
				$this->delete($child);
			}
		}
	}

}