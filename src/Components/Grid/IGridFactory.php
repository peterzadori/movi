<?php

namespace movi\Components\Grid;

use movi\Components\Grid\Grid;

interface IGridFactory
{

	/** @return Grid */
	public function createGrid();

	public function configure(Grid $grid);

}