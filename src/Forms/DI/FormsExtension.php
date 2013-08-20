<?php

namespace movi\Forms\DI;

use Nette\Config\CompilerExtension;
use Nette\Utils\PhpGenerator\ClassType;

final class FormsExtension extends CompilerExtension
{

	public function afterCompile(ClassType $class)
	{
		parent::afterCompile($class);

		$init = $class->methods['initialize'];
		$init->addBody('Kdyby\Replicator\Container::register();');
		$init->addBody('movi\Forms\FormExtension::register();');
	}

}