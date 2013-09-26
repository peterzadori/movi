<?php

namespace movi\Model;

class EntityMapping
{

	/** @var array */
	private $entities;


	/**
	 * @param array $entities
	 */
	public function registerEntities(array $entities)
	{
		foreach ($entities as $table => $entity)
		{
			$this->entities[$table] = $entity;
		}
	}


	/**
	 * @param $table
	 * @return bool
	 */
	public function getEntity($table)
	{
		if (!array_key_exists($table, $this->entities)) {
			return false;
		} else {
			return $this->entities[$table];
		}
	}

}