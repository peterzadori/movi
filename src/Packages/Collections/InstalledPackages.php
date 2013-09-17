<?php

namespace movi\Packages\Collections;

use movi\PackageRegisteredException;
use movi\Packages\ICollection;
use movi\Packages\Loader;
use Nette\Utils\Finder;

class InstalledPackages implements ICollection
{

	private $packages = [];

	/** @var string */
	private $packagesDir;


	public function __construct($packagesDir)
	{
		$this->packagesDir = $packagesDir;

		$this->findPackages();
	}


	/**
	 * @return mixed
	 */
	public function getPackages()
	{
		return $this->packages;
	}


	private function findPackages()
	{
		foreach (Finder::findDirectories('*')->in($this->packagesDir) as $package)
		{
			$this->register($package);
		}
	}


	/**
	 * @param \SplFileInfo $package
	 * @throws \movi\PackageRegisteredException
	 */
	private function register(\SplFileInfo $package)
	{
		$loader = new Loader();
		$package = $loader->getPackage($package);

		if (isset($this->packages[$package['name']])) {
			throw new PackageRegisteredException("Package '" . $package['name'] . "' is already registered.");
		}

		$this->packages[$package['name']] = $package;
	}

}