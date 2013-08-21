<?php

namespace movi\Model\Filters;

use LeanMapper\Fluent;
use LeanMapper\Reflection\Property;
use movi\Localization\Languages;
use movi\Model\Mapper;

class TranslateFilter
{

	/** @var Languages */
	private $languages;

	/** @var Mapper */
	private $mapper;


	public function __construct(Languages $languages, Mapper $mapper)
	{
		$this->languages = $languages;
		$this->mapper = $mapper;
	}


	public function translate()
	{
		$args = func_get_args();
		$statement = $args[0];
		$property = $args[1];

		if ($property instanceof Property) {
			if ($property->hasRelationship()) {
				$relationship = $property->getRelationship();
				$table = $relationship->getTargetTable();

				$this->modifyStatement($statement, $table);
			}
		} else {
			$this->modifyStatement($statement, $property);
		}

		return $statement;
	}


	/**
	 * @param Fluent $statement
	 * @param $table
	 */
	private function modifyStatement(Fluent $statement, $table)
	{
		$statement
			->select('t.*')
			->leftJoin('%n t', $this->mapper->getTranslationsTable($table))
			->on('t.%n = %n.%n', $this->mapper->getTranslationsColumn($table), $table, $this->mapper->getPrimaryKey($table))
			->where('language_id = %s', $this->languages->getLanguage()->id);
	}

}