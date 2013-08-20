<?php

namespace movi\Templating;

use Nette\Utils\Strings;
use movi\InvalidArgumentException;

class Helpers
{

	/** @var array */
	private $helpers;


	public function registerHelper($name, IHelper $factory)
	{
		$name = Strings::lower($name);

		if (isset($this->helpers[$name])) {
			throw new InvalidArgumentException("Helper '$name' is already registered.");
		}

		$this->helpers[$name] = $factory;
	}


	public function loader($helper)
	{
		$helper = Strings::lower($helper);

		if (!isset($this->helpers[$helper])) {
			return;
		}

		return callback($this->helpers[$helper], 'process');
	}


}