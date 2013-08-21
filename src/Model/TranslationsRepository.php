<?php

namespace movi\Model\Repositories;

use Kdyby\Events\EventManager;
use LeanMapper\Connection;
use LeanMapper\Entity;
use LeanMapper\Fluent;
use LeanMapper\IMapper;
use movi\InvalidArgumentException;
use movi\Localization\Language;
use movi\Model\Entities\TranslatableEntity;

/**
 * @entity TranslatableEntity
 */
abstract class TranslationsRepository extends Repository
{

	/** @var \movi\Localization\Language */
	protected $language;


	public function __construct(Connection $connection, IMapper $mapper, EventManager $evm, Language $language)
	{
		parent::__construct($connection, $mapper, $evm);

		$this->language = $language;
	}


	public function getStatement()
	{
		$statement = parent::getStatement();
		$statement->applyFilter('translate', $this->getTable());

		return $statement;
	}


	/**
	 * @param Entity $entity
	 * @param \movi\Model\Entities\Language $language
	 * @return mixed|void
	 * @throws \movi\InvalidArgumentException
	 */
	public function persist(Entity $entity, \movi\Model\Entities\Language $language = NULL)
	{
		if (!($entity instanceof TranslatableEntity)) {
			throw new InvalidArgumentException('Only translatable entities can be persisted.');
		}

		if ($language !== NULL) {
			$this->language->setLanguage($language);
		}

		parent::persist($entity);
	}


	/**
	 * @param Entity $entity
	 * @return int|mixed
	 */
	protected function insertIntoDatabase(Entity $entity)
	{
		/** @var TranslatableEntity $entity */
		$table = $this->getTable();
		$primaryKey = $this->mapper->getPrimaryKey($table);
		$values = array_diff_key($entity->getModifiedRowData(), $entity->getTranslatableColumns());

		$this->connection->query(
			'INSERT INTO %n %v', $table, $values
		);

		$id = isset($values[$primaryKey]) ? $values[$primaryKey] : $this->connection->getInsertId();

		$this->insertTranslation($id, $entity);

		return $id;
	}


	/**
	 * @param Entity $entity
	 * @return mixed|void
	 */
	protected function updateInDatabase(Entity $entity)
	{
		/** @var TranslatableEntity $entity */
		$table = $this->getTable();
		$primaryKey = $this->mapper->getPrimaryKey($table);
		$idField = $this->mapper->getEntityField($table, $primaryKey);

		$values = array_diff_key($entity->getModifiedRowData(), $entity->getTranslatableColumns());

		if (!empty($values)) {
			$this->connection->query(
				'UPDATE %n SET %a WHERE %n = ?', $this->getTable(), $values, $primaryKey, $entity->$idField
			);
		}

		$this->insertTranslation($entity->$idField, $entity);
	}


	/**
	 * @param $id
	 * @param TranslatableEntity $entity
	 */
	private function insertTranslation($id, TranslatableEntity $entity)
	{
		$table = $this->getTable();
		$languageColumn = $this->mapper->getLanguageColumn();
		$translationsTable = $this->mapper->getTranslationsTable($table);
		$translationsViaColumn = $this->mapper->getTranslationsColumn($table);

		// Translation
		$translation = array_intersect_key($entity->getModifiedRowData(), $entity->getTranslatableColumns());
		$translation[$translationsViaColumn] = $id;
		$translation[$languageColumn] = $this->language->id;

		if ($entity->isDetached()) {
			$this->connection->query(
				'INSERT INTO %n %v', $translationsTable, $translation
			);
		} else {
			$row = $this->connection->select('*')
				->from($translationsTable)
				->where('%n = %i', $languageColumn, $this->language->id)
				->where('%n = %i', $translationsViaColumn, $id)
				->fetchSingle();

			if (!$row) {
				$this->connection->query(
					'INSERT INTO %n %v', $translationsTable, $translation
				);
			} else {
				$this->connection->query(
					'UPDATE %n SET %a WHERE %n = ? AND [language_id] = %s',
					$translationsTable, $translation, $translationsViaColumn, $id, $this->language->id
				);
			}
		}
	}


	/**
	 * @param TranslatableEntity $entity
	 * @return array
	 */
	public function getTranslations(TranslatableEntity $entity)
	{
		$table = $this->getTable();
		$languageColumn = $this->mapper->getLanguageColumn();
		$translationsTable = $this->mapper->getTranslationsTable($table);
		$translationsColumn = $this->mapper->getTranslationsColumn($table);
		$primaryKey = $this->mapper->getPrimaryKey($table);
		$idField = $this->mapper->getEntityField($table, $primaryKey);

		return $this->connection->select('*')
			->from($translationsTable)
			->where('%n = %i', $translationsColumn, $entity->$idField)
			->fetchAssoc($languageColumn);
	}


}