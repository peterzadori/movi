<?php

namespace movi\DI;

use Kdyby;
use Nette;
use movi;

final class Configurator extends Nette\Configurator
{

	/** @var \Nette\Loaders\RobotLoader */
	private $robotLoader;


	/**
	 * @return Nette\DI\Container
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
	 * @return \Nette\DI\Compiler
	 */
	protected function createCompiler()
	{
		$compiler = parent::createCompiler();

		// Register extensions
		$compiler->addExtension('movi', new movi\DI\Extensions\moviExtension);
		$compiler->addExtension('packages', new movi\DI\Extensions\PackagesExtension());

		$compiler->addExtension('repositories', new movi\DI\Extensions\RepositoriesExtension());
		$compiler->addExtension('filters', new movi\DI\Extensions\FiltersExtension());

		$compiler->addExtension('forms', new movi\Forms\DI\FormsExtension());
		$compiler->addExtension('events', new Kdyby\Events\DI\EventsExtension());
		$compiler->addExtension('console', new Kdyby\Console\DI\ConsoleExtension());

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

			$this->robotLoader = $loader;
		}

		return $this->robotLoader;
	}


	/**
	 * Load packages and save them into parameters
	 */
	private function createPackages()
	{
		$packages = new movi\Packages\Packages($this, $this->parameters['packagesDir']);
		$packages->setCacheStorage(new Nette\Caching\Storages\FileStorage($this->getCacheDirectory()));

		$this->parameters['packages'] = $packages->getPackages();
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