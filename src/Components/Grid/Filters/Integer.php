<?php

namespace movi\Components\Grid\Filters;

use movi\Components\Grid\IDataSource;

final class Integer extends Filter
{

	/**
	 * @param $value
	 * @param IDataSource $dataSource
	 * @return IDataSource
	 */
	public function filter($value, IDataSource $dataSource)
	{
		$dataSource->filter(array('%n = %i', $this->column->getColumn(), $value));
	}

}