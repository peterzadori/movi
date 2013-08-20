<?php

namespace movi\Components;

use Nette\Application\UI\PresenterComponent;
use Nette\Callback;
use movi;

final class Widgets extends PresenterComponent
{

	/** @var array */
	private $widgets;


	/**
	 * @param $name
	 * @param $widget
	 */
	public function addWidget($name, $widget)
	{
		$this->widgets[$name] = $widget;
	}


	/**
	 * @param $name
	 * @return \Nette\ComponentModel\IComponent
	 */
	protected function createComponent($name)
	{
		if (!array_key_exists($name, $this->widgets)) {
			return new movi\Components\Widgets\WidgetNotFoundControl($name);
		}

		$widget = $this->widgets[$name];

		if ($widget instanceOf Callback) {
			return $widget->invoke();
		} else {
			return $this->widgets[$name];
		}
	}
}