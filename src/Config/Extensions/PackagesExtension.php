<?php

namespace movi\Config\Extensions;

use Nette\Config\Helpers;
use Nette\DI\ContainerBuilder;
use Nette\Utils\PhpGenerator\ClassType;
use movi\Config\CompilerExtension;
use movi\InvalidArgumentException;
use movi\Packages\BasePackage;
use movi\Packages\Package;

final class PackagesExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		// Register services
		$builder->addDefinition($this->prefix('manager'))
			->setClass('movi\Packages\Manager');

		$builder->addDefinition($this->prefix('installer'))
			->setClass('movi\Packages\Installer');

		$builder->addDefinition($this->prefix('resourceInstaller'))
			->setClass('movi\Packages\Installers\ResourceInstaller', ['%resourcesDir%'])
			->addTag('installer');

		// Process packages
		$this->processPackages($builder);
	}


	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$installer = $builder->getDefinition($this->prefix('installer'));

		foreach (array_keys($builder->findByTag('installer')) as $service)
		{
			$installer->addSetup('register', ['@' . $service]);
		}
	}


	public function afterCompile(ClassType $container)
	{
		$initialize = $container->methods['initialize'];

		$initialize->addBody('$this->getService("packages.installer")->install();');
	}


	/**
	 * @param ContainerBuilder $builder
	 * @throws \movi\InvalidArgumentException
	 */
	private function processPackages(ContainerBuilder $builder)
	{
		$manager = $builder->getDefinition($this->prefix('manager'));
		$packages = $builder->parameters['packages']; // array

		if (count($packages) > 0) {
			foreach (array_values($packages) as $data)
			{
				$class = $this->getPackageClass($data);

				if (class_exists($class)) {
					$package = new $class($data);

					if (!($package instanceof Package)) {
						throw new InvalidArgumentException("Package class must be an instance of movi\\Packages\\Package.");
					}
				} else {
					$package = new BasePackage($data);
				}

				// Compile the package
				$package->compile($this->compiler);

				// Process package's extensions
				$this->processExtensions($package);

				// Process package's configuration
				$this->processConfig($package, $builder);

				// Add package to manager
				$manager->addSetup('addPackage', [$package]);
			}
		}
	}


	/**
	 * @param Package $package
	 */
	private function processExtensions(Package $package)
	{
		if (count($package->extensions) > 0) {
			foreach ($package->extensions as $name => $extension)
			{
				$this->compiler->addExtension($name, new $extension);
			}
		}
	}


	/**
	 * @param Package $package
	 * @param ContainerBuilder $builder
	 */
	private function processConfig(Package $package, ContainerBuilder $builder)
	{
		if (!empty($package->config)) {
			foreach ($package->config as $file)
			{
				$file = ltrim($file, '/');
				$file = $package->dir . '/' . $file;

				$config = $this->loadFromFile($file);

				// Parameters
				if (isset($config['parameters'])) {
					$builder->parameters = Helpers::merge($builder->parameters, $config['parameters']);
				}

				// Parse configuration file
				$this->compiler->parseServices($builder, $config);
			}
		}
	}


	/**
	 * @param $package
	 * @return string
	 */
	private function getPackageClass($package)
	{
		return 'movi\\Packages\\' . basename($package['dir']);
	}

}