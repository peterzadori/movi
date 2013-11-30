<?php

namespace movi\DI;

abstract class CompilerExtension extends \Nette\DI\CompilerExtension
{

	/**
	 * @param $tag
	 * @return array
	 */
	protected function getSortedServices($tag)
	{
		$builder = $this->getContainerBuilder();
		$sorted = [];

		foreach(array_keys($builder->findByTag($tag)) as $service)
		{
			$definition = $builder->getDefinition($service);
			$tags = $definition->tags;

			if (isset($tags['priority'])) {
				$sorted[$tags['priority']] = $service;
			}
		}

		ksort($sorted);
		return $sorted;
	}

}