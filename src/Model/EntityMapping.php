<?php

namespace movi\Model;

class EntityMapping
{

	private static $entities;


	public static function registerRepository(Repository $repository)
	{
		foreach ($repository->getEntities() as $table => $entity)
		{
			self::$entities[$table] = $entity;
		}
	}


	public static function getEntity($table)
	{
		if (!array_key_exists($table, self::$entities)) {
			return false;
		} else {
			return self::$entities[$table];
		}
	}

}