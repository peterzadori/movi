<?php

namespace movi\Config\Extensions;

use Nette\Config\CompilerExtension;
use Nette\Utils\PhpGenerator\ClassType;
use Nette\Config\Compiler;
use LeanMapper\Connection;

final class moviExtension extends CompilerExtension
{

	/** @var array */
	private $defaults = array(
		'language' => array(
			'detect' => false
		),
		'project' => NULL,
		'password' => array(
			'salt' => NULL,
			'algorithm' => 'sha512'
		));


	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		if (isset($config['project'])) {
			$builder->getDefinition('session')
				->addSetup('setName', array($config['project']));
		}

		$builder->getDefinition('nette.presenterFactory')
			->setClass('movi\Application\PresenterFactory', array($builder->parameters['appDir']));

		$builder->addDefinition($this->prefix('cacheProvider'))
			->setClass('movi\Caching\CacheProvider');

		$this->initDatabase();

		$this->initLocalization();

		$this->initTemplating();

		$this->initSecurity();

		$this->initWidgets();

		$this->initAssets();
	}


	public function beforeCompile()
	{
		$this->registerRepositories();

		$this->registerWidgets();

		$this->registerHelpers();

		$this->registerRoutes();
	}


	public function afterCompile(ClassType $class)
	{
		$initialize = $class->methods['initialize'];
		$container = $this->getContainerBuilder();

		$initialize->addBody($container->formatPhp(
			'Nette\Diagnostics\Debugger::$bar->addPanel(?);',
			Compiler::filterArguments(array(new \Nette\DI\Statement('movi\Diagnostics\Stopwatch')))
		));
	}


	private function registerRoutes()
	{
		$builder = $this->getContainerBuilder();
		$router = $builder->getDefinition('router');

		foreach($this->getSortedServices('route') as $route)
		{
			$definition = $builder->getDefinition($route);
			$definition->setAutowired(false);

			$router->addSetup('$service[] = ?', $definition);
		}
	}


	private function initDatabase()
	{
		$builder = $this->getContainerBuilder();

		$connection = $builder->addDefinition($this->prefix('connection'))
			->setClass('LeanMapper\Connection', array('%database%'));

		$translateFilter = $builder->addDefinition($this->prefix('translateFilter'))
			->setClass('movi\Model\Filters\TranslateFilter');

		$orderFilter = $builder->addDefinition($this->prefix('orderFilter'))
			->setClass('movi\Model\Filters\OrderFilter');

		$connection->addSetup('registerFilter', array(
			'translate',
			array(
				$translateFilter,
				'translate'
			),
			Connection::WIRE_PROPERTY
		));

		$connection->addSetup('registerFilter', array(
			'order',
			array(
				$orderFilter,
				'modify'
			)
		));

		$builder->addDefinition($this->prefix('mapper'))
			->setClass('movi\Model\Mapper');
	}


	private function registerRepositories()
	{
		$builder = $this->getContainerBuilder();
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


	private function initLocalization()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('languagesRepository'))
			->setClass('movi\Model\Repositories\LanguagesRepository')
			->addTag('repository');

		$builder->addDefinition($this->prefix('language'))
			->setClass('movi\Localization\Language');

		$builder->addDefinition($this->prefix('languages'))
			->setClass('movi\Localization\Languages');

		$builder->addDefinition($this->prefix('translator'))
			->setClass('movi\Localization\Translator')
			->setArguments(array('%appDir%'))
			->addTag('kdyby.subscriber');
	}


	private function initTemplating()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('templateManager'))
			->setClass('movi\Templating\TemplateManager', array('%templatesDir%'));

		$builder->addDefinition($this->prefix('helpers'))
			->setClass('movi\Templating\Helpers');

		$builder->addDefinition($this->prefix('thumbnailHelper'))
			->setClass('movi\Templating\Helpers\ThumbnailHelper', array('%wwwDir%'))
			->addTag('helper')
			->addTag('name', 'thumbnail');
	}


	private function registerHelpers()
	{
		$builder = $this->getContainerBuilder();
		$helpers = $builder->getDefinition($this->prefix('helpers'));

		foreach(array_keys($builder->findByTag('helper')) as $helper)
		{
			$definition = $builder->getDefinition($helper);
			$name = $definition->tags['name'];

			$helpers->addSetup('registerHelper', array($name, '@' . $helper));
		}
	}


	private function initSecurity()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		// Password
		$builder->addDefinition($this->prefix('password'))
			->setClass('movi\Tools\Password', array($config['password']['salt'], $config['password']['algorithm']));

		$builder->getDefinition('nette.userStorage')
			->setClass('movi\Security\UserStorage');

		$builder->addDefinition($this->prefix('authenticator'))
			->setClass('movi\Security\Authenticator');
	}


	private function initWidgets()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('widgets'))
			->setClass('movi\Components\Widgets')
			->setShared(false);
	}


	private function registerWidgets()
	{
		$builder = $this->getContainerBuilder();
		$widgets = $builder->getDefinition($this->prefix('widgets'));

		foreach(array_keys($builder->findByTag('widget')) as $widget)
		{
			$definition = $builder->getDefinition($widget);
			$name = $definition->tags['name'];

			$widgets->addSetup('addWidget', array($name, '@' . $widget));
		}
	}


	private function initAssets()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition('assets')
			->setClass('movi\Components\Assets\AssetsManager', array('%resourcesDir%'));

		$builder->addDefinition($this->prefix('assetsControl'))
			->setClass('movi\Components\Assets\AssetsControl')
			->setShared(false);

		$builder->addDefinition($this->prefix('assetsCommand'))
			->setClass('movi\Console\Commands\AssetsCommand')
			->addTag('kdyby.console.command');
	}


	private function getSortedServices($tag)
	{
		$builder = $this->getContainerBuilder();
		$sorted = array();

		foreach(array_keys($builder->findByTag($tag)) as $service)
		{
			$definition = $builder->getDefinition($service);
			$tags = $definition->tags;

			if (isset($tags['priority'])) {
				$sorted[$tags['priority']] = $service;
			}
		}

		ksort($sorted);

		return $sorted;
	}

}