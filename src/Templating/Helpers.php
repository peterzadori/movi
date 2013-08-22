<?php

namespace movi\Templating;

use Nette\Callback;
use Nette\Utils\Strings;
use movi\InvalidArgumentException;

class Helpers
{

	/** @var array */
	private $helpers;


	/**
	 * @param $name
	 * @param IHelper $helper
	 * @throws \movi\InvalidArgumentException
	 */
	public function registerHelper($name, IHelper $helper)
	{
		if (isset($this->helpers[$name])) {
			throw new InvalidArgumentException("Helper '$name' is already registered.");
		}

		$this->helpers[$name] = $helper;
	}


	/**
	 * @param $helper
	 * @return callable
	 * @throws \movi\InvalidArgumentException
	 */
	public function loader($helper)
	{
		if (!isset($this->helpers[$helper])) {
			throw new InvalidArgumentException("Unknown helper: '$helper'.");
		}

		return Callback::create($this->helpers[$helper], 'process');
	}


}