<?php

namespace movi\Components\Grid\DataSources;

use movi\Model\Repositories\Repository;
use movi\Components\Grid\IDataSource;

class LeanMapperDataSource implements IDataSource
{

	/** @var \LeanMapper\Repository */
	private $repository;

	/** @var \DibiFluent */
	public $statement;


	public function __construct(Repository $repository)
	{
		$this->repository = $repository;
		$this->statement = $repository->getStatement();
	}


	public function fetch()
	{
		return $this->repository->fetchStatement($this->statement);
	}


	public function count()
	{
		$statement = clone $this->statement;
		$statement->removeClause('SELECT');
		$statement->select('COUNT(*)');

		return $statement->fetchSingle();
	}


	public function sort(array $sorting)
	{
		$this->statement->removeClause('ORDER BY');

		list($column, $sort) = $sorting;
		$column = sprintf('[%s]', $column);

		$this->statement->orderBy($column, $sort);
	}


	public function filter($condition = array())
	{
		call_user_func_array(array($this->statement, 'where'), $condition);
	}


	public function limit($limit, $offset)
	{
		$this->statement->limit(array($offset, $limit));
	}


	public function getClone()
	{
		return new $this($this->repository);
	}

}