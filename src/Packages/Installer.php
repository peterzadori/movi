<?php

namespace movi\Packages;

use Nette\Caching\Cache;
use Nette\DI\Container;
use Nette\Utils\Json;
use movi\Caching\CacheProvider;

final class Installer
{

	/** @var \movi\Packages\Manager */
	private $manager;

	/** @var \Nette\Caching\Cache */
	private $cache;

	/** @var IInstaller[] */
	private $installers;


	public function __construct(Manager $manager, CacheProvider $cacheProvider)
	{
		$this->manager = $manager;

		$this->cache = $cacheProvider->create('movi.packages.installer');
	}


	/**
	 * @param IInstaller $installer
	 */
	public function registerInstaller(IInstaller $installer)
	{
		$this->installers[] = $installer;
	}


	public function install()
	{
		/** @var $package \movi\Packages\Package */
		foreach (array_values($this->manager->getPackages()) as $package)
		{
			$hash = sha1(Json::encode($package));

			// Load cache
			if ($this->cache->load($package->name) === NULL || $this->cache->load($package->name) !== $hash) {
				$this->installPackage($package);

				$this->cache->save($package->name, $hash);
			}
		}
	}


	/**
	 * @param Package $package
	 */
	private function installPackage(Package $package)
	{
		foreach ($this->installers as $installer)
		{
			$installer->install($package);
		}
	}

}