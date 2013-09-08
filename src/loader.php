<?php

use Nette\Diagnostics\Debugger;

require __DIR__ . '/Config/Configurator.php';

// Handy functions :)
function flog($message)
{
	Debugger::fireLog($message);
}