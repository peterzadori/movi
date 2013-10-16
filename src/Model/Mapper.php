<?php

namespace movi\Model;

use Doctrine\Common\Inflector\Inflector;
use LeanMapper\DefaultMapper;
use LeanMapper\Row;
use Nette\Utils\Strings;

class Mapper extends DefaultMapper
{

	/** @var string */
	protected $defaultEntityNamespace = '\movi\Model\Entities';

	/** @var EntityMapping */
	private $entityMapping;


	public function __construct(EntityMapping $entityMapping)
	{
		$this->entityMapping = $entityMapping;
	}


	/**
	 * @param string $table
	 * @param Row $row
	 * @return string
	 */
	public function getEntityClass($table, Row $row = NULL)
	{
		return $this->entityMapping->getEntity($table);
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
	 * @return string
	 */
	public function getLanguageColumn()
	{
		return 'language_id';
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