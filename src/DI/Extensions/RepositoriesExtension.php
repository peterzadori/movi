<?php

namespace movi\DI\Extensions;

use movi\DI\CompilerExtension;
use Nette\Reflection\ClassType;

final class RepositoriesExtension extends CompilerExtension
{

	const REPOSITORY_TAG = 'repository';


	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$repositories = $this->getConfig();

		foreach ($repositories as $name => $repository)
		{
			$def = $builder->addDefinition($name);
			$this->compiler->parseService($def, $repository);

			if (is_string($repository)) {
				$class = $builder->normalizeEntity($def->factory->entity);
				$def->class = $class;
			}

			$def->addTag(self::REPOSITORY_TAG);
		}
	}


	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$mapping = $builder->getDefinition('movi.entityMapping');

		foreach(array_keys($builder->findByTag(self::REPOSITORY_TAG)) as $repository)
		{
			$reflection = ClassType::from($builder->getDefinition($repository)->class);
			$class = $reflection->newInstanceWithoutConstructor();

			$mapping->addSetup('registerEntities', array($class->getEntities()));
		}
	}

}