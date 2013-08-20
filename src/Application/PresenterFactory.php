<?php

namespace movi\Application;

use Nette;


final class PresenterFactory extends Nette\Application\PresenterFactory
{

	/** @var string */
	private $namespace = 'movi';

	/** @var Nette\DI\Container */
	private $container;

	private $baseDir;


	public function __construct($baseDir, Nette\DI\Container $container)
	{
		$this->baseDir = $baseDir;
		$this->container = $container;
	}


	public function createPresenter($name)
	{
		$class = $this->getPresenterClass($name);
		if (($services = $this->container->getByType($class, false)) !== NULL) {
			$presenter = $this->container->createInstance($services[0]);
		} else {
			$presenter = $this->container->createInstance($class);
		}

		if (method_exists($presenter, 'setContext')) {
			$this->container->callMethod(array($presenter, 'setContext'));
		}
		foreach (array_reverse(get_class_methods($presenter)) as $method) {
			if (substr($method, 0, 6) === 'inject') {
				$this->container->callMethod(array($presenter, $method));
			}
		}

		if ($presenter instanceof UI\Presenter && $presenter->invalidLinkMode === NULL) {
			$presenter->invalidLinkMode = $this->container->parameters['debugMode'] ? UI\Presenter::INVALID_LINK_WARNING : UI\Presenter::INVALID_LINK_SILENT;
		}
		return $presenter;
	}


	/**
	 * @param $presenter
	 * @return string
	 */
	public function formatPresenterClass($presenter)
	{
		$class = str_replace(':', 'Module\\', $presenter) . 'Presenter';

		if (!Nette\Utils\Strings::contains($presenter, 'Kdyby')) {
			return $this->namespace . '\\' . $class;
		}

		return $class;
	}


	/**
	 * @param $class
	 * @return mixed|string
	 */
	public function unformatPresenterClass($class)
	{
		$class = str_replace('Module\\', ':', substr($class, 0, -9));
		$class = str_replace($this->namespace . '\\', '', $class);

		return $class;
	}

}