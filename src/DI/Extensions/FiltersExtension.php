<?php

namespace movi\DI\Extensions;

use movi\DI\CompilerExtension;
use Nette\Reflection\ClassType;
use Nette\Utils\Arrays;

class FiltersExtension extends CompilerExtension
{

	const FILTER_TAG = 'database.filter';


	private $defaults = [
		'callback' => 'modify',
		'wire' => []
	];

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

			$def->addTag(self::FILTER_TAG);
		}
	}


	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$connection = $builder->getDefinition('movi.connection');

		foreach(array_keys($builder->findByTag(self::FILTER_TAG)) as $filter)
		{
			$def = $builder->getDefinition($filter);
			$tags = Arrays::mergeTree($def->tags, $this->defaults);

			$connection->addSetup('registerFilter', [
				$tags['name'], ['@' . $filter, $tags['callback']], (!empty($tags['wire']) ? $tags['wire'] : NULL)
			]);
		}
	}

}