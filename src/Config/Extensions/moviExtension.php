<?php

namespace movi\Config\Extensions;

use LeanMapper\Connection;
use Nette\Config\Compiler;
use Nette\DI\ContainerBuilder;
use Nette\Utils\PhpGenerator\ClassType;
use Nette\Utils\Validators;
use movi\Config\CompilerExtension;

final class moviExtension extends CompilerExtension
{

	/** @var array */
	private $defaults = [
		'language' => [
			'detect' => false
		],
		'project' => NULL,
		'password' => [
			'salt' => NULL,
			'algorithm' => 'sha512'
		],
		'macros' => []
	];


	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		if (isset($config['project'])) {
			$builder->getDefinition('session')
				->addSetup('setName', [$config['project']]);
		}

		$builder->getDefinition('nette.presenterFactory')
			->setClass('movi\Application\PresenterFactory', [$builder->parameters['appDir']]);

		$builder->addDefinition($this->prefix('cacheProvider'))
			->setClass('movi\Caching\CacheProvider');

		$this->initDatabase($builder);

		$this->initLocalization($builder);

		$this->initTemplating($builder, $config);

		$this->initSecurity($builder, $config);

		$this->initWidgets($builder);

		$this->initAssets($builder);
	}


	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		$this->registerRepositories($builder);

		$this->registerWidgets($builder);

		$this->registerHelpers($builder);

		$this->registerRoutes($builder);
	}


	public function afterCompile(ClassType $class)
	{
		$initialize = $class->methods['initialize'];
		$container = $this->getContainerBuilder();

		$initialize->addBody($container->formatPhp(
			'Nette\Diagnostics\Debugger::$bar->addPanel(?);',
			Compiler::filterArguments([new \Nette\DI\Statement('movi\Diagnostics\Stopwatch')])
		));
	}


	private function registerRoutes(ContainerBuilder $builder)
	{
		$router = $builder->getDefinition('router');

		foreach($this->getSortedServices('route') as $route)
		{
			$definition = $builder->getDefinition($route);
			$definition->setAutowired(false);

			$router->addSetup('$service[] = ?', $definition);
		}
	}


	private function initDatabase(ContainerBuilder $builder)
	{
		$connection = $builder->addDefinition($this->prefix('connection'))
			->setClass('LeanMapper\Connection', ['%database%']);

		$builder->addDefinition($this->prefix('mapper'))
			->setClass('movi\Model\Mapper');

		$translateFilter = $builder->addDefinition($this->prefix('translateFilter'))
			->setClass('movi\Model\Filters\TranslateFilter');

		$orderFilter = $builder->addDefinition($this->prefix('orderFilter'))
			->setClass('movi\Model\Filters\OrderFilter');

		$connection->addSetup('registerFilter', [
			'translate', [$translateFilter, 'translate'], Connection::WIRE_PROPERTY
		]);

		$connection->addSetup('registerFilter', ['order', [$orderFilter, 'modify']]);
	}


	private function registerRepositories(ContainerBuilder $builder)
	{
		$mapper = $builder->getDefinition($this->prefix('mapper'));

		foreach(array_keys($builder->findByTag('repository')) as $helper)
		{
			$definition = $builder->getDefinition($helper);
			$class = \Nette\Utils\PhpGenerator\Helpers::createObject($definition->class, array());

			foreach ($class->getEntities() as $table => $entity)
			{
				$mapper->addSetup('register', array($table, $entity));
			}
		}
	}


	private function initLocalization(ContainerBuilder $builder)
	{
		$builder->addDefinition($this->prefix('languagesRepository'))
			->setClass('movi\Model\Repositories\LanguagesRepository')
			->addSetup('setLocalDir', ['%localDir%'])
			->addTag('repository');

		$builder->addDefinition($this->prefix('language'))
			->setClass('movi\Localization\Language');

		$builder->addDefinition($this->prefix('languages'))
			->setClass('movi\Localization\Languages');

		$builder->addDefinition($this->prefix('translator'))
			->setClass('movi\Localization\Translator')
			->setArguments(['%localDir%'])
			->addTag('kdyby.subscriber');
	}


	private function initTemplating(ContainerBuilder $builder, $config)
	{
		$builder->addDefinition($this->prefix('templateManager'))
			->setClass('movi\Templating\TemplateManager', ['%templatesDir%']);

		$builder->addDefinition($this->prefix('helpers'))
			->setClass('movi\Templating\Helpers');

		$builder->addDefinition($this->prefix('thumbnailHelper'))
			->setClass('movi\Templating\Helpers\ThumbnailHelper', ['%wwwDir%'])
			->addTag('helper')
			->addTag('name', 'thumbnail');

		$latte = $builder->getDefinition('nette.latte');
		$latte->addSetup('movi\Templating\Macros\moviMacros::install(?->compiler)', ['@self']);

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

		foreach(array_keys($builder->findByTag('helper')) as $helper)
		{
			$definition = $builder->getDefinition($helper);
			$name = $definition->tags['name'];

			$helpers->addSetup('registerHelper', [$name, '@' . $helper]);
		}
	}


	private function initSecurity(ContainerBuilder $builder, $config)
	{
		// Password
		$builder->addDefinition($this->prefix('password'))
			->setClass('movi\Tools\Password', [$config['password']['salt'], $config['password']['algorithm']]);

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

		foreach(array_keys($builder->findByTag('widget')) as $widget)
		{
			$definition = $builder->getDefinition($widget);
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

}