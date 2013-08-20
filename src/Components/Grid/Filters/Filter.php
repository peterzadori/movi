<?php

namespace movi\Components\Grid\Filters;

use movi\Components\Grid\Columns\Column;
use movi\Components\Grid\Component;
use movi\Components\Grid\Grid;
use movi\Components\Grid\IDataSource;
use Nette\Forms\IControl;
use Nette\Object;

abstract class Filter extends Object
{

	/** @var Grid */
	protected $grid;

	/** @var Column */
	protected $column;

	/** @var object */
	protected $control;


	public function setGrid(Grid $grid)
	{
		$this->grid = $grid;

		return $this;
	}


	/**
	 * @param Column $column
	 * @return $this
	 */
	public function setColumn(Column $column)
	{
		$this->column = $column;
		$this->createControl();

		return $this;
	}


	/**
	 * @return Column
	 */
	public function getColumn()
	{
		return $this->column;
	}


	/**
	 * @param IControl $control
	 * @return $this
	 */
	public function setControl(IControl $control)
	{
		$this->control = $control;

		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getControl()
	{
		return $this->control->getControl();
	}


	public function createControl()
	{
		$control = $this->grid['form']['filter']->addText($this->column->getName());
		$control->getControlPrototype()->class[] = 'input-block-level';

		$this->control = $control;
	}


	/**
	 * @param $value
	 * @param IDataSource $dataSource
	 * @return mixed
	 */
	public abstract function filter($value, IDataSource $dataSource);

}