<?php

namespace movi\Components\Grid\Columns;

class Email extends Column
{

	public function __construct()
	{
		parent::__construct();

		$this->setWidth(300);
	}

}