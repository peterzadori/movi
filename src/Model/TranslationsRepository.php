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

	public $onTranslationPersist;


	public function __construct(Connection $connection, IMapper $mapper, EventManager $evm, Language $language)
	{
		parent::__construct($connection, $mapper, $evm);

		$this->language = $language;
	}


	/**
	 * @param Fluent $statement
	 * @return $this|void
	 */
	public function beforeFetch(Fluent $statement)
	{
		return $statement->applyFilter('translate', $this->getTable());
	}


	/**
	 * @param Entity $entity
	 * @param null $language
	 * @return \DibiResult|int|mixed|null
	 * @throws \movi\InvalidArgumentException
	 */
	public function persist(Entity $entity, $language = NULL)
	{
		if (!($entity instanceof TranslatableEntity)) {
			throw new InvalidArgumentException('Only translatable entity can be persisted.');
		}

		parent::persist($entity);

		$this->persistTranslation($entity, $language);
	}


	/**
	 * @param TranslatableEntity $entity
	 * @param null $language
	 */
	private function persistTranslation(TranslatableEntity $entity, $language = NULL)
	{
		$translation = $entity->getModifiedTranslationsData();

		$table = $this->getTable();
		$translationsTable = $this->mapper->getTranslationsTable($table);
		$translationsColumn = $this->mapper->getTranslationsColumn($table);
		$primaryKey = $this->mapper->getPrimaryKey($this->getTable());
		$idField = $this->mapper->getEntityField($this->getTable(), $primaryKey);

		$language = ($language !== NULL) ? $language : $this->language->getLanguage();

		$translation[$translationsColumn] = $entity->$idField;
		$translation['language_id'] = $language->id;

		$row = $this->connection->select('%n', $translationsColumn)
			->from($translationsTable)
			->where('language_id = %i', $language->id)
			->where('%n = %i', $translationsColumn, $entity->$idField)
			->fetchSingle();

		if (!$row) {
			$this->connection->query(
				'INSERT INTO %n %v', $translationsTable, $translation
			);
		} else {
			$this->connection->query(
				'UPDATE %n SET %a WHERE %n = ? AND [language_id] = %s',
				$translationsTable, $translation, $translationsColumn, $entity->$idField, $language->id
			);
		}

		$this->onTranslationPersist($entity, $language);
	}


	/**
	 * @param TranslatableEntity $entity
	 * @return array
	 */
	public function getTranslations(TranslatableEntity $entity)
	{
		$table = $this->getTable();
		$translationsTable = $this->mapper->getTranslationsTable($table);
		$translationsColumn = $this->mapper->getTranslationsColumn($table);

		return $this->connection->select('*')
			->from($translationsTable)
			->where('%n = %i', $translationsColumn, $entity->id)
			->fetchAssoc('language_id');
	}


}