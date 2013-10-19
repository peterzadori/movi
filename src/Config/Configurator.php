<?php

namespace movi\Config;

use Kdyby;
use Nette;
use movi;
use VojtechDobes\NetteForms\InputListExtension;
use movi\Packages\Collections\InstalledPackages;

final class Configurator extends Nette\Config\Configurator
{

	/** @var \Nette\Loaders\RobotLoader */
	private $robotLoader;


	/**
	 * @return \SystemContainer
	 */
	public function createContainer()
	{
		$this->setup();

		// Create and register the Robot Loader
		$this->createRobotLoader()->register();

		// Register packages
		$this->createPackages();

		// Create container
		$container = parent::createContainer();

		// Register Robot Loader as a service
		$container->addService('robotLoader', $this->robotLoader);

		return $container;
	}


	/**
	 * @return Nette\Config\Compiler
	 */
	protected function createCompiler()
	{
		$compiler = parent::createCompiler();

		// Register extensions
		$compiler->addExtension('movi', new movi\Config\Extensions\moviExtension);
		$compiler->addExtension('packages', new movi\Config\Extensions\PackagesExtension());

		$compiler->addExtension('database', new movi\Config\Extensions\DatabaseExtension());
		$compiler->addExtension('repositories', new movi\Config\Extensions\RepositoriesExtension());
		$compiler->addExtension('filters', new movi\Config\Extensions\FiltersExtension());

		$compiler->addExtension('extensions', new movi\Config\Extensions\ExtensionsExtension);
		$compiler->addExtension('forms', new movi\Forms\DI\FormsExtension());
		$compiler->addExtension('events', new Kdyby\Events\DI\EventsExtension());
		$compiler->addExtension('console', new Kdyby\Console\DI\ConsoleExtension());
		$compiler->addExtension('inputlist', new InputListExtension());

		return $compiler;
	}


	/**
	 * @return Nette\Loaders\RobotLoader
	 */
	public function createRobotLoader()
	{
		if ($this->robotLoader === NULL) {
			$loader = parent::createRobotLoader();
			$loader->addDirectory($this->parameters['packagesDir']);

			if (file_exists($this->parameters['libsDir'])) {
				$loader->addDirectory($this->parameters['libsDir']);
			}

			$this->robotLoader = $loader;
		}

		return $this->robotLoader;
	}


	/**
	 * Load packages and save them into parameters
	 */
	private function createPackages()
	{
		$collection = new InstalledPackages($this->parameters['packagesDir']);
		$packages = $collection->getPackages();

		foreach ($packages as $package)
		{
			if (!empty($package['config'])) {
				foreach($package['config'] as $config)
				{
					$this->addConfig($package['dir'] . $config, $this::NONE);
				}
			}
		}

		$this->parameters['packages'] = $packages;
	}


	private function setup()
	{
		$this->addParameters(array(
			'tempDir' => $this->parameters['appDir'] . '/temp',
			'packagesDir' => $this->parameters['appDir'] . '/packages',
			'resourcesDir' => $this->parameters['wwwDir'] . '/static',
			'libsDir' => $this->parameters['wwwDir'] . '/libs',
			'storageDir' => $this->parameters['wwwDir'] . '/storage',
			'templatesDir' => $this->parameters['appDir'] . '/templates',
			'logDir' => $this->parameters['appDir'] . '/log',
			'localeDir' => $this->parameters['appDir'] . '/locale'
		));

		$this->setTempDirectory($this->parameters['tempDir']);
		$this->enableDebugger($this->parameters['logDir']);
	}

}