<?php

namespace movi\Packages;

use Nette\DI\Container;

final class BasePackage extends Package
{

	/**
	 * @param Container $mapper
	 * @return bool
	 */
	public function isValid(Container $mapper)
	{
		return false;
	}

}