<?php

namespace movi\Components\Widgets;

use movi\Application\UI\Control;

class WidgetNotFoundControl extends Control
{

	private $widget;


	public function __construct($widget)
	{
		$this->widget = $widget;
	}


	public function render()
	{
		echo "Widget '$this->widget' not found.";
	}

}