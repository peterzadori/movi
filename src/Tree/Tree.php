<?php

namespace movi\Tree;

use Nette\Object;
use movi\Model\Repositories\Repository;
use movi\Tree\NodeNotFound;
use movi\InvalidStateException;

abstract class Tree extends Object
{

	public $tab = '-';

	/** @var \movi\Model\Repositories\Repository */
	protected $repository;

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

			foreach ($nodes as $row)
			{
				// Save the row
				$this->nodes[$row->id] = $row;

				if ($row->parent === NULL) {
					$this->parents[$row->id] = $row;
				} else {
					$this->children[$row->parent->id][$row->id] = $row;
				}
			}

			$this->built = true;

			$this->onBuild($this);
		}

		return $this->nodes;
	}


	/**
	 * @return array
	 */
	public function rebuild()
	{
		$this->built = false;
		$this->nodes = array();
		$this->parents = array();
		$this->children = array();

		return $this->build();
	}


	/**
	 * @param $id
	 * @return null
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
	 * @param null $node
	 * @return array
	 */
	public function getParents($node = NULL)
	{
		if ($node === NULL) {
			return $this->parents;
		}

		$parents = array();

		while ($node->parent !== NULL) {
			$node = $this->getNode($node->parent->id);

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
				$tree[$id] = $child;

				$this->getChildrenRecursively($child, $tree);
			}
		}

		return $tree;
	}


    /**
     * @param $children
     * @param $callback
     */
    public function traverseTree($children, $callback)
    {
        if (count($children) > 0) {
            foreach ($children as $child)
            {
                $callback($child);

                if (array_key_exists($child->id, $this->children)) {
                    $this->getTree($this->children[$child->id], $callback);
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

		$parent = $node;
		while ($parent->parent !== NULL) {
			$parent = $this->getNode($parent->parent->id);

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
		// Get parents
		if (empty($children) && empty($tree)) {
			$children = $this->parents;
		}

		if (count($children) > 0) {
			foreach ($children as $id => $child)
			{
				if ($child->hidden == true) {
					continue;
				}

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

			if ($child->parent->id == $parent) {
				if (isset($this->children[$id])) {
					$children = $this->getTreeRecursively($this->children[$id], $id);

					if ($children) {
						$child->children = $children;
					}
				}

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
		// First run
		if ($level == 0) {
			$children = $this->parents;
		}

		if (count($children) > 0) {
			foreach (array_values($children) as $child)
			{
				if ($child->id == $exclude || $child->hidden) {
					continue;
				}

				$options[$child->id] = sprintf('%s %s', str_repeat($this->tab, $level), $child->title);

				if (array_key_exists($child->id, $this->children)) {
					$this->getArray($exclude, $this->children[$child->id], $level + 1, $options);
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
			foreach (array_values($this->children[$node->id]) as $child)
			{
				$this->delete($child);
			}
		}
	}


	public function getRow($id)
	{
		return $this->nodes[$id];
	}

}