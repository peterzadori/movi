<?php

namespace movi\Components\Grid;

use Nette\Application\UI\PresenterComponent;

final class ColumnsContainer extends PresenterComponent
{

	/**
	 * @return \ArrayIterator
	 */
	public function getColumns()
	{
		return $this->getComponents(true, 'movi\Components\Grid\Columns\Column');
	}

}