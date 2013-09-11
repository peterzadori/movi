<?php

namespace movi\Components\Grid\Filters;

use movi\Components\Grid\IDataSource;

final class String extends Filter
{

	/**
	 * @param $value
	 * @param $dataSource
	 * @return mixed
	 */
	public function filter($value, IDataSource $dataSource)
	{
		$dataSource->filter(['LOWER(%n) LIKE %~like~', $this->column->getColumn(), $value]);
	}

}