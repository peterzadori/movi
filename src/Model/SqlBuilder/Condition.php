<?php

namespace movi\Model\SqlBuilder;

class Condition
{

	/** @var array */
	public $args;


	public function __construct()
	{
		$this->args = func_get_args();
	}

}