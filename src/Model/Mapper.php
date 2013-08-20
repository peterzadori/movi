<?php

namespace movi\Model;

use Doctrine\Common\Inflector\Inflector;
use LeanMapper\DefaultMapper;
use LeanMapper\Row;
use Nette\Utils\Strings;

class Mapper extends DefaultMapper
{

	protected $defaultEntityNamespace = '\movi\Model\Entities';

	/** @var array */
	private $registry = array();


	/**
	 * @param $table
	 * @param $entity
	 */
	public function register($table, $entity)
	{
		$this->registry[$table] = $this->formatEntityClassName($entity);
	}


	/**
	 * @param $table
	 * @param Row $row
	 * @return string
	 */
	public function getEntityClass($table, Row $row = NULL)
	{
		if (array_key_exists($table, $this->registry)) {
			return $this->registry[$table];
		} else {
			return $this->formatEntityClassName($table);
		}
	}


	/**
	 * @param $entity
	 * @return string
	 */
	private function formatEntityClassName($entity)
	{
		if (substr($entity, 0, 1) != '\\') {
			return $this->defaultEntityNamespace . '\\' . $entity;
		} else {
			return $entity;
		}
	}


	/**
	 * @param $table
	 * @return string
	 */
	public function getTranslationsTable($table)
	{
		return Inflector::singularize($table) . '_translations';
	}


	/**
	 * @param $table
	 * @return string
	 */
	public function getTranslationsColumn($table)
	{
		return Inflector::singularize($table) . '_id';
	}


}