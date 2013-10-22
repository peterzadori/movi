<?php

namespace movi\Packages;

use movi\Packages\Collections\InstalledPackages;
use Nette\Caching\IStorage;
use movi\Config\Configurator;

final class Packages
{

	/** @var \movi\Config\Configurator */
	private $configurator;

	/** @var \movi\Packages\Collections\InstalledPackages */
	private $collection;

	/** @var IStorage */
	private $cacheStorage;


	public function __construct(Configurator $configurator, $packagesDir)
	{
		$this->configurator = $configurator;

		$this->collection = new InstalledPackages($packagesDir);
	}


	public function getPackages()
	{
		$packages = $this->collection->getPackages();

		foreach ($packages as $package)
		{
			if (!empty($package['config'])) {
				foreach($package['config'] as $config)
				{
					$this->configurator->addConfig($package['dir'] . $config, Configurator::NONE);
				}
			}
		}

		return $packages;
	}


	public function setCacheStorage(IStorage $cacheStorage)
	{
		$this->cacheStorage = $cacheStorage;
		$this->collection->setCacheStorage($cacheStorage);
	}

}