<?php

namespace movi\Packages;

use Nette\Utils\Finder;
use movi\PackageRegisteredException;

class Installed extends Collection
{

	/** @var string */
	private $packagesDir;


	public function __construct($packagesDir)
	{
		$this->packagesDir = $packagesDir;

		$this->findPackages();
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