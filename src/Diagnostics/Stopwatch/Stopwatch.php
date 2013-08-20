<?php

namespace movi\Diagnostics;

use Nette\Diagnostics\Debugger;
use Nette\Diagnostics\IBarPanel;

final class Stopwatch implements IBarPanel
{

	/** @var array */
	private static $timers = array();


	public static function start($name)
	{
		self::$timers[$name] = 0;

		Debugger::timer($name);
	}


	public static function stop($name)
	{
		$time = Debugger::timer($name);

		self::$timers[$name] = $time;
	}


	public function getTab()
	{
		ob_start();
		require __DIR__ . '/templates/tab.phtml';
		return ob_get_clean();
	}


	public function getPanel()
	{
		ob_start();
		require __DIR__ . '/templates/panel.phtml';
		return ob_get_clean();
	}

}