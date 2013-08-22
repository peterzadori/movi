<?php

namespace movi\Config;

abstract class CompilerExtension extends \Nette\Config\CompilerExtension
{

	/**
	 * @param $tag
	 * @return array
	 */
	protected function getSortedServices($tag)
	{
		$builder = $this->getContainerBuilder();
		$sorted = array();

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