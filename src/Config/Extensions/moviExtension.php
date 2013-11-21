<?php

namespace movi\Config\Extensions;

use movi\InvalidArgumentException;
use movi\InvalidStateException;
use Nette\Config\Compiler;
use Nette\DI\ContainerBuilder;
use Nette\Utils\PhpGenerator\ClassType;
use Nette\Utils\Validators;
use movi\Config\CompilerExtension;

final class moviExtension extends CompilerExtension
{

	const ROUTE_TAG = 'route',
		HELPER_TAG = 'helper',
		MEDIA_STORAGE_TAG = 'media.storage',
		WIDGET_TAG = 'widget';


	/** @var array */
	private $defaults = [
		'project' => NULL,
		'password' => [
			'salt' => NULL,
			'algorithm' => 'sha512'
		],
		'database' => [],
		'macros' => []
	];



	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		if (empty($config['project'])) {
			throw new InvalidStateException("Please specify the project's name");
		}

		$builder->getDefinition('session')
			->addSetup('setName', [$config['project']]);

		$builder->getDefinition('nette.presenterFactory')
			->setClass('movi\Application\PresenterFactory', [$builder->parameters['appDir']]);

		$builder->addDefinition($this->prefix('cacheProvider'))
			->setClass('movi\Caching\CacheProvider');

		$this->initDatabase($builder, $config);

		$this->initLocalization($builder);

		$this->initTemplating($builder, $config);

		$this->initSecurity($builder, $config);

		$this->initWidgets($builder);

		$this->initAssets($builder);

		$this->initMedia($builder);
	}


	private function initDatabase(ContainerBuilder $builder, $config)
	{
		$config = $config['database'];

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

		$connection = $builder->addDefinition($this->prefix('connection'))
			->setClass('movi\Model\Connection', array($config));

		$builder->addDefinition($this->prefix('mapper'))
			->setClass('movi\Model\Mapper');

		$builder->addDefinition($this->prefix('entityMapping'))
			->setClass('movi\Model\EntityMapping');

		$builder->addDefinition($this->prefix('entityFactory'))
			->setClass('movi\Model\EntityFactory');

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


	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		$this->registerWidgets($builder);

		$this->registerHelpers($builder);

		$this->registerMediaStorages($builder);

		$this->registerRoutes($builder);
	}


	public function afterCompile(ClassType $class)
	{
		$initialize = $class->methods['initialize'];
		$container = $this->getContainerBuilder();

		$initialize->addBody($container->formatPhp(
			'Nette\Diagnostics\Debugger::$bar->addPanel(?);',
			Compiler::filterArguments([new \Nette\DI\Statement('movi\Diagnostics\Stopwatch\Stopwatch')])
		));
	}


	private function registerRoutes(ContainerBuilder $builder)
	{
		$router = $builder->getDefinition('router');

		foreach($this->getSortedServices(self::ROUTE_TAG) as $route)
		{
			$definition = $builder->getDefinition($route);
			$definition->setAutowired(false);

			$router->addSetup('$service[] = ?', $definition);
		}
	}


	private function initLocalization(ContainerBuilder $builder)
	{
		$builder->addDefinition($this->prefix('language'))
			->setClass('movi\Localization\Language');

		$builder->addDefinition($this->prefix('languages'))
			->setClass('movi\Localization\Languages');

		$builder->addDefinition($this->prefix('translations'))
			->setClass('movi\Localization\Translations')
			->setArguments(['%localeDir%']);

		$builder->addDefinition($this->prefix('translator'))
			->setClass('movi\Localization\Translator');
	}


	private function initTemplating(ContainerBuilder $builder, $config)
	{
		$builder->addDefinition($this->prefix('templateManager'))
			->setClass('movi\Templating\TemplateManager', ['%templatesDir%']);

		$builder->addDefinition($this->prefix('helpers'))
			->setClass('movi\Templating\Helpers');

		$latte = $builder->getDefinition('nette.latte');
		$latte->addSetup('movi\Templating\Macros\moviMacros::install(?->compiler)', ['@self']);
		$latte->addSetup('movi\Templating\Macros\MediaMacros::install(?->compiler)', ['@self']);

		foreach ($config['macros'] as $macro) {
			if (strpos($macro, '::') === FALSE && class_exists($macro)) {
				$macro .= '::install';
			} else {
				Validators::isCallable($macro);
			}

			$latte->addSetup($macro . '(?->compiler)', array('@self'));
		}
	}


	private function registerHelpers(ContainerBuilder $builder)
	{
		$helpers = $builder->getDefinition($this->prefix('helpers'));

		foreach(array_keys($builder->findByTag(self::HELPER_TAG)) as $helper)
		{
			$definition = $builder->getDefinition($helper);

			if (!isset($definition->tags['name'])) {
				throw new InvalidArgumentException('Missing name for helper ' . $helper);
			}

			$name = $definition->tags['name'];

			$helpers->addSetup('registerHelper', [$name, '@' . $helper]);
		}
	}


	private function registerMediaStorages(ContainerBuilder $builder)
	{
		$media = $builder->getDefinition($this->prefix('media'));

		foreach(array_keys($builder->findByTag(self::MEDIA_STORAGE_TAG)) as $storage)
		{
			$definition = $builder->getDefinition($storage);

			if (!isset($definition->tags['name'])) {
				throw new InvalidArgumentException('Missing name for storage ' . $storage);
			}

			$name = $definition->tags['name'];

			$media->addSetup('addStorage', [$name, '@' . $storage]);
		}
	}


	private function initSecurity(ContainerBuilder $builder, $config)
	{
		// Password
		$builder->addDefinition($this->prefix('password'))
			->setClass('movi\Security\Password', [$config['password']['salt'], $config['password']['algorithm']]);

		$builder->getDefinition('nette.userStorage')
			->setClass('movi\Security\UserStorage');

		$builder->addDefinition($this->prefix('authenticator'))
			->setClass('movi\Security\Authenticator');
	}


	private function initWidgets(ContainerBuilder $builder)
	{
		$builder->addDefinition($this->prefix('widgets'))
			->setClass('movi\Components\Widgets')
			->setShared(false);
	}


	private function registerWidgets(ContainerBuilder $builder)
	{
		$widgets = $builder->getDefinition($this->prefix('widgets'));

		foreach(array_keys($builder->findByTag(self::WIDGET_TAG)) as $widget)
		{
			$definition = $builder->getDefinition($widget);

			if (!isset($definition->tags['name'])) {
				throw new InvalidArgumentException('Missing name for widget ' . $widget);
			}

			$name = $definition->tags['name'];

			$widgets->addSetup('addWidget', [$name, '@' . $widget]);
		}
	}


	private function initAssets(ContainerBuilder $builder)
	{
		$builder->addDefinition('assets')
			->setClass('movi\Components\Assets\AssetsManager', ['%resourcesDir%']);

		$builder->addDefinition($this->prefix('assetsControl'))
			->setClass('movi\Components\Assets\AssetsControl')
			->setShared(false);

		$builder->addDefinition($this->prefix('assetsCommand'))
			->setClass('movi\Console\Commands\AssetsCommand')
			->addTag('kdyby.console.command');
	}


	private function initMedia(ContainerBuilder $builder)
	{
		$builder->addDefinition($this->prefix('media'))
			->setClass('movi\Media\Media', ['storage', '%wwwDir%']);

		$builder->addDefinition('thumbnailer')
			->setClass('movi\Media\Utils\Thumbnailer');

		$builder->addDefinition('linker')
			->setClass('movi\Media\Utils\Linker');
	}

}