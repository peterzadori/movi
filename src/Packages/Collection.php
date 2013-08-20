<?php

namespace movi\Packages;

use Nette\Object;

abstract class Collection extends Object
{

	/** @var array */
	protected $packages;


	public function getPackages()
	{
		return $this->packages;
	}

}