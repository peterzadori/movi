<?php

namespace movi\Components\Grid;

abstract class GridFactory implements IGridFactory
{

	/**
	 * @return Grid
	 */
	public function createGrid()
	{
		$grid = new Grid();

		$this->configure($grid);

		return $grid;
	}

}