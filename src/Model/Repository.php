<?php

namespace movi\Model;

use Kdyby\Events\EventManager;
use LeanMapper\Connection;
use LeanMapper\Events;
use LeanMapper\Fluent;
use LeanMapper\IMapper;
use Nette\ObjectMixin;
use Nette\Reflection\ClassType;
use movi\EntityNotFound;
use movi\Model\Entity;

abstract class Repository extends \LeanMapper\Repository
{

	/** @var array */
	protected $annotations;

	/** @var \Kdyby\Events\EventManager */
	protected $evm;


	public function __construct(Connection $connection, IMapper $mapper, EventManager $evm)
	{
		parent::__construct($connection, $mapper);

		$this->evm = $evm;
		$this->initKdybyEvents();
	}


	/**
	 * @return Fluent
	 */
	public function getStatement()
	{
		return $this->connection->select('*')->from($this->getTable());
	}


	/**
	 * @param $id
	 * @return Entity
	 */
	public function find($id)
	{
		return $this->findBy(['[id] = %i' => $id]);
	}


	/**
	 * @param array $conditions
	 * @return Entity
	 * @throws \movi\EntityNotFound
	 */
	public function findBy(array $conditions)
	{
		$statement = $this->getStatement();

		if (count($conditions) > 0) {
			foreach ($conditions as $condition => $value)
			{
				$statement->where($condition, $value);
			}
		}

		$row = $statement->fetch();

		if (!$row) {
			throw new EntityNotFound(get_called_class() . ': Entity not found.');
		}

		return $this->createEntity($row);
	}


	/**
	 * @param array $conditions
	 * @param array $sorting
	 * @param null $offset
	 * @param null $limit
	 * @return Entity[]
	 */
	public function findAll(array $conditions = NULL, array $sorting = [], $offset = NULL, $limit = NULL)
	{
		$statement = $this->getStatement();

		if ($conditions !== NULL && count($conditions) > 0) {
			foreach ($conditions as $condition => $value)
			{
				$statement->where($condition, $value);
			}
		}

		foreach ($sorting as $column => $order) {
			$column = (strpos($column, '[') === false) ? '[' . $column . ']' : $column;
			$statement->orderBy($column, ($order === NULL) ? 'ASC' : $order);
		}

		return $this->createEntities(
			$statement->fetchAll($offset, $limit)
		);
	}


	/**
	 * @param Fluent $statement
	 * @return Entity[]
	 */
	public function fetchStatement(Fluent $statement)
	{
		return $this->createEntities($statement->fetchAll());
	}


	/**
	 * @param null $key
	 * @param null $value
	 * @return array
	 */
	public function fetchPairs($key = NULL, $value = NULL)
	{
		return $this->getStatement()->fetchPairs($key, $value);
	}


	private function initKdybyEvents()
	{
		static $events = [
			Events::EVENT_BEFORE_PERSIST,
			Events::EVENT_BEFORE_CREATE,
			Events::EVENT_BEFORE_UPDATE,
			Events::EVENT_BEFORE_DELETE,
			Events::EVENT_AFTER_PERSIST,
			Events::EVENT_AFTER_CREATE,
			Events::EVENT_AFTER_UPDATE,
			Events::EVENT_AFTER_DELETE,
		];

		foreach ($events as $eventName) {
			$ns = get_called_class();
			$event = $this->evm->createEvent($ns . '::' . $eventName);
			$this->events->registerCallback($eventName, $event);
		}
	}


	/**
	 * @return array|\Nette\Reflection\IAnnotation[]
	 */
	public function getAnnotations()
	{
		if ($this->annotations == NULL) {
			$reflection = new ClassType(get_called_class());
			$annotations = $reflection->getAnnotations();

			$this->annotations = $annotations;
		}

		return $this->annotations;
	}


	/**
	 * @return array
	 */
	public function getEntities()
	{
		if (isset($this->getAnnotations()['entity'])) {
			return [$this->getTable() => $this->getAnnotations()['entity'][0]];
		} else {
			return [];
		}
	}


	/**
	 * @param $method
	 * @param $args
	 */
	public function __call($method, $args)
	{
		ObjectMixin::call($this, $method, $args);
	}

}