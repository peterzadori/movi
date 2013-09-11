<?php

namespace movi\Components\Grid\DataSources;

use movi\Components\Grid\IDataSource;
use movi\Tree\Tree;

class TreeDataSource implements IDataSource
{

	/** @var \movi\Tree\Tree  */
	private $tree;


	public function __construct(Tree $tree)
	{
		$this->tree = $tree;
		$this->tree->build();
	}

	public function fetch()
	{
		return $this->tree->getTree();
	}


	public function count()
	{
		return count($this->tree->getNodes());
	}


	public function sort(array $sorting)
	{

	}


	public function filter($condition = [])
	{

	}


	public function limit($limit, $offset)
	{

	}


	public function getClone()
	{
		return new $this($this->tree);
	}

}