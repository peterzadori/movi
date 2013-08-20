<?php

namespace movi\Components\Grid;

use Nette\Application\UI\PresenterComponent;
use movi\Components\Grid\Grid;

abstract class Component extends PresenterComponent
{

	/**
	 * @return NULL|Grid
	 */
	public function getGrid()
	{
		return $this->lookup('movi\Components\Grid\Grid');
	}

}