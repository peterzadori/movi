<?php

namespace movi\Packages\Collections;

use movi\PackageRegisteredException;
use movi\Packages\ICollection;
use movi\Packages\Loader;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Caching\Storages\DevNullStorage;
use Nette\Utils\Finder;

class InstalledPackages implements ICollection
{

	private $packages = [];

	/** @var string */
	private $packagesDir;

	private $cacheStorage;


	public function __construct($packagesDir)
	{
		$this->packagesDir = $packagesDir;
	}


	public function setCacheStorage(IStorage $cacheStorage)
	{
		$this->cacheStorage = $cacheStorage;

		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getPackages()
	{
		return $this->getCache()->load('packages', callback($this, 'findPackages'));
	}


	public function findPackages()
	{
		$packages = [];

		foreach (Finder::findDirectories('*')->in($this->packagesDir) as $package)
		{
			$package = $this->register($package);
			$packages[$package['name']] = $package;
		}

		return $packages;
	}


	/**
	 * @param \SplFileInfo $package
	 * @return mixed
	 * @throws \movi\PackageRegisteredException
	 */
	private function register(\SplFileInfo $package)
	{
		$loader = new Loader();
		$package = $loader->getPackage($package);

		if (isset($this->packages[$package['name']])) {
			throw new PackageRegisteredException("Package '" . $package['name'] . "' is already registered.");
		}

		return $package;
	}


	public function getCacheStorage()
	{
		return $this->cacheStorage;
	}


	/**
	 * @return Cache
	 */
	protected function getCache()
	{
		return new Cache($this->cacheStorage, 'movi.installedPackages');
	}

}