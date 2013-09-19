<?php

namespace movi\Model\SqlBuilder;

use Nette\Object;

class Query extends Object
{

	/** @var Condition[] */
	private $conditions;

	/** @var Limit */
	private $limit;

	/** @var Sorting */
	private $sorting;


	public function __construct()
	{
		$this->conditions = [];
	}


	/**
	 * @param Condition $condition
	 * @return $this
	 */
	public function addCondition(Condition $condition)
	{
		$this->conditions[] = $condition;

		return $this;
	}


	/**
	 * @return array|Condition[]
	 */
	public function getConditions()
	{
		return $this->conditions;
	}


	/**
	 * @param Limit $limit
	 * @return $this
	 */
	public function setLimit(Limit $limit)
	{
		$this->limit = $limit;

		return $this;
	}


	/**
	 * @return Limit
	 */
	public function getLimit()
	{
		return $this->limit;
	}


	/**
	 * @param Sorting $sorting
	 * @return $this
	 */
	public function setSorting(Sorting $sorting)
	{
		$this->sorting = $sorting;

		return $this;
	}


	/**
	 * @return Sorting
	 */
	public function getSorting()
	{
		return $this->sorting;
	}

}