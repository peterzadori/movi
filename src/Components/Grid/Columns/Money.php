<?php

namespace movi\Components\Grid\Columns;

use LeanMapper\Entity;

class Money extends Column
{

	public function render(Entity $row)
	{
		echo number_format($row->{$this->column}, 2, '.', '');
	}

}