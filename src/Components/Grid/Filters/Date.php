<?php

namespace movi\Components\Grid\Filters;

use movi\Components\Grid\IDataSource;
use movi\Components\Grid\Columns\Date as DateColumn;
use Nette\DateTime;

final class Date extends Filter
{

	/**
	 * @param $value
	 * @param $dataSource
	 * @return mixed
	 */
	public function filter($value, IDataSource $dataSource)
	{
		$value = strtotime($value);
		$begin = strtotime('today', $value);
		$end = strtotime('+1 day', $begin);

		$dataSource->filter(array('%n BETWEEN %s AND %s', $this->column->getColumn(), DateTime::from($begin), DateTime::from($end)));
	}


	/**
	 * @return mixed
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$control->class[] = 'date-picker';

		if ($this->getColumn() instanceof DateColumn) {
			$control->data['date-format'] = $this->getColumn()->format;
		}

		return $control;
	}

}