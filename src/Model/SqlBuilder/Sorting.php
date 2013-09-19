<?php

namespace movi\Model\SqlBuilder;

class Sorting
{

	/** @var string */
	private $column;

	/** @var string */
	private $sort;

	private $allowedSorting = ['ASC', 'DESC'];


	public function __construct($column, $sort = 'ASC')
	{
		$this->setColumn($column);
		$this->setSorting($sort);
	}


	/**
	 * @param $column
	 * @return $this
	 */
	public function setColumn($column)
	{
		if (!preg_match('#[\s]#', $column)) {
			$column = sprintf('[%s]', $column);
		}

		$this->column = $column;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getColumn()
	{
		return $this->column;
	}


	/**
	 * @param string $sort
	 * @return $this
	 * @throws \InvalidArgumentException
	 */
	public function setSorting($sort = 'ASC')
	{
		if (!in_array($sort, $this->allowedSorting)) {
			throw new \InvalidArgumentException('Invalid sorting');
		}

		$this->sort = $sort;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getSorting()
	{
		return $this->sort;
	}

}