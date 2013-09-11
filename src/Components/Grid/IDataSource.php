<?php

namespace movi\Components\Grid;

interface IDataSource
{

	public function fetch();

	public function count();

	public function sort(array $sorting);

	public function filter($condition = []);

	public function limit($limit, $offset);

	/**
	 * @return IDataSource
	 */
	public function getClone();

}