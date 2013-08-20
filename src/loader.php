<?php

require __DIR__ . '/Config/Configurator.php';

// Handy functions :)
use Nette\Diagnostics\Debugger;

function flog($message)
{
	Debugger::fireLog($message);
}