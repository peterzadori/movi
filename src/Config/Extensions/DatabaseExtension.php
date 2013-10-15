<?php

namespace movi\Config\Extensions;

use LeanMapper\Connection;
use movi\Config\CompilerExtension;

class DatabaseExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$useProfiler = isset($config['profiler'])
			? $config['profiler']
			: !$builder->parameters['productionMode'];

		unset($config['profiler']);

		if (isset($config['flags'])) {
			$flags = 0;
			foreach ((array) $config['flags'] as $flag) {
				$flags |= constant($flag);
			}
			$config['flags'] = $flags;
		}

		$connection = $builder->addDefinition('movi.connection')
			->setClass('movi\Model\Connection', array($config));

		$builder->addDefinition($this->prefix('mapper'))
			->setClass('movi\Model\Mapper');

		$builder->addDefinition('movi.entityMapping')
			->setClass('movi\Model\EntityMapping');

		// Filters
		$builder->addDefinition($this->prefix('translateFilter'))
			->setClass('movi\Model\Filters\TranslateFilter')
			->addTag('name', 'translate')
			->addTag('callback', 'translate')
			->addTag('wire', 'p')
			->addTag('database.filter');

		$builder->addDefinition($this->prefix('orderFilter'))
			->setClass('movi\Model\Filters\OrderFilter')
			->addTag('name', 'order')
			->addTag('database.filter');

		if ($useProfiler) {
			$panel = $builder->addDefinition($this->prefix('panel'))
				->setClass('DibiNettePanel')
				->addSetup('Nette\Diagnostics\Debugger::$bar->addPanel(?)', array('@self'))
				->addSetup('Nette\Diagnostics\Debugger::$blueScreen->addPanel(?)', array('DibiNettePanel::renderException'));

			$connection->addSetup('$service->onEvent[] = ?', array(array($panel, 'logEvent')));
		}
	}

}