<?php

namespace movi\Model;

use movi\InvalidArgumentException;

class EntityMapping
{

	/** @var array */
	private $entities;


	/**
	 * @param array $entities
	 * @throws \movi\InvalidArgumentException
	 */
	public function registerEntities(array $entities)
	{
		foreach ($entities as $table => $entity)
		{
			if (isset($this->entities[$table])) {
				throw new InvalidArgumentException("Entity for table $table is already registered.");
			}

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
			return NULL;
		} else {
			return $this->entities[$table];
		}
	}

}