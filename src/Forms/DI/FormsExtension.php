<?php

namespace movi\Forms\DI;

use Nette\Config\CompilerExtension;
use Nette\Utils\PhpGenerator\ClassType;

final class FormsExtension extends CompilerExtension
{

	public function afterCompile(ClassType $class)
	{
		$init = $class->methods['initialize'];
		$init->addBody('movi\Forms\FormExtension::register();');
	}

}