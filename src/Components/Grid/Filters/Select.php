<?php

namespace movi\Components\Grid\Filters;

use movi\Components\Grid\IDataSource;

class Select extends Filter
{

	/** @var array */
	public $values;


	public function __construct($values)
	{
		$this->values = $values;
	}


	public function createControl()
	{
		$control = $this->grid['form']['filter']
			->addSelect($this->column->getColumn(), NULL)
			->setItems($this->values)
			->setPrompt('Vyberte');

		$this->control = $control;
	}


	/**
	 * @param $value
	 * @param IDataSource $dataSource
	 * @return mixed|IDataSource
	 */
	public function filter($value, IDataSource $dataSource)
	{
		if (!is_bool($value)) {
			if ($value === 'true') $value = true;
			if ($value === 'false') $value = false;
		}

		$dataSource->filter(['%n = %s', $this->column->getColumn(), $value]);
	}

}