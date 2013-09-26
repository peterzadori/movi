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
			->setClass('movi\Packages\Installer')
			->addTag('kdyby.subscriber');

		$builder->addDefinition($this->prefix('resourceInstaller'))
			->setClass('movi\Packages\Installers\ResourceInstaller', ['%resourcesDir%'])
			->addTag('packages.installer');

		$builder->addDefinition($this->prefix('schemaInstaller'))
			->setClass('movi\Packages\Installers\SchemaInstaller')
			->addTag('packages.installer');

		// Process packages
		$this->processPackages($builder);
	}


	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$installer = $builder->getDefinition($this->prefix('installer'));

		foreach (array_keys($builder->findByTag('packages.installer')) as $service)
		{
			$installer->addSetup('registerInstaller', ['@' . $service]);
		}
	}


	/**
	 * @param ContainerBuilder $builder
	 * @throws \movi\InvalidArgumentException
	 */
	private function processPackages(ContainerBuilder $builder)
	{
		$manager = $builder->getDefinition($this->prefix('manager'));
		$packages = $builder->parameters['packages'];

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
					$package = new Package($data);
				}

				// Compile the package
				$package->onCompile($this->compiler);

				$this->processExtensions($package->extensions);

				$manager->addSetup('addPackage', [$package]);
			}
		}
	}


	/**
	 * @param array $extensions
	 */
	private function processExtensions(array $extensions)
	{
		foreach ($extensions as $name => $extension)
		{
			$this->compiler->addExtension($name, new $extension);
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