<?php

namespace movi\Components\Grid\Columns;

use movi\Model\Entity;

class Money extends Column
{

	/**
	 * @param Entity $row
	 */
	public function render(Entity $row)
	{
		echo number_format($row->{$this->column}, 2, '.', '');
	}

}