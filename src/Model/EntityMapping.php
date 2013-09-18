<?php

namespace movi\Model;

class EntityMapping
{

	/** @var array */
	private static $entities;


	/**
	 * @param Repository $repository
	 */
	public static function registerRepository(Repository $repository)
	{
		foreach ($repository->getEntities() as $table => $entity)
		{
			self::$entities[$table] = $entity;
		}
	}


	/**
	 * @param $table
	 * @return bool
	 */
	public static function getEntity($table)
	{
		if (!array_key_exists($table, self::$entities)) {
			return false;
		} else {
			return self::$entities[$table];
		}
	}

}