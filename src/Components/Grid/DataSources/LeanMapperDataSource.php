<?php

namespace movi\Components\Grid\DataSources;

use movi\Components\Grid\IDataSource;
use movi\Model\Repository;

class LeanMapperDataSource implements IDataSource
{

	/** @var \LeanMapper\Repository */
	private $repository;

	/** @var \DibiFluent */
	public $statement;

	private $offset;

	private $limit;


	public function __construct(Repository $repository)
	{
		$this->repository = $repository;
		$this->statement = $repository->getStatement();
	}


	public function fetch()
	{
		$statement = clone $this->statement;
		$statement->limit([$this->offset, $this->limit]);

		return $this->repository->fetchStatement($statement);
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


	public function filter($condition = [])
	{
		call_user_func_array([$this->statement, 'where'], $condition);
	}


	public function limit($limit, $offset)
	{
		$this->limit = $limit;
		$this->offset = $offset;
	}

}