<?php

namespace movi\Packages;

use movi\InvalidArgumentException;
use movi\PackageNotFoundException;

final class Manager
{

	/** @var array */
	private $packages = [];


	/**
	 * @param Package $package
	 */
	public function addPackage(Package $package)
	{
		$this->packages[$package->name] = $package;
	}



	/**
	 * @return array
	 */
	public function getPackages()
	{
		return $this->packages;
	}


	/**
	 * @param $name
	 * @param $value
	 * @throws \movi\InvalidArgumentException
	 */
	public function __set($name, $value)
	{
		throw new InvalidArgumentException('You can not create new packages.');
	}


	/**
	 * @param $name
	 * @return mixed
	 * @throws \movi\PackageNotFoundException
	 */
	public function __get($name)
	{
		if (!isset($this->packages[$name])) {
			throw new PackageNotFoundException("Package '$name' not found.");
		}

		return $this->packages[$name];
	}

}