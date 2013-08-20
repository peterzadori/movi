<?php

namespace movi\Forms\Controls;

use movi\Application\Application;
use Nette\Callback;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;

class TranslationsControl extends Container
{

	private $cb;

	private $languages;


	public function __construct(\Closure $cb)
	{
		$this->cb = Callback::create($cb);

		$this->languages = Application::$languages;
	}


	public function attached($form)
	{
		parent::attached($form);
	}


	public function setDefaults($values, $erase = false)
	{

	}


	protected function createComponent($name)
	{
		$container = new Container();

		$group = $this->form->addGroup($name);
		$container->setCurrentGroup($group);

		$this->cb->invoke($container);

		return $container;
	}

}