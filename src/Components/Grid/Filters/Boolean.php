<?php

namespace movi\Components\Grid\Filters;

class Boolean extends Select
{

	public function __construct()
	{
		$this->values = array(true => 'Áno', false => 'Nie');
	}


	public function createControl()
	{
		parent::createControl();

		$this->control->getControlPrototype()->class[] = 'input-small';
	}

}