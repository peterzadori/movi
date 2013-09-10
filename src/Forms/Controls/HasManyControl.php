<?php

namespace movi\Forms\Controls;

use Nette\Forms\Container;
use movi\InvalidArgumentException;
use movi\Model\IdentifiedEntity;
use movi\Model\Repository;

final class HasManyControl extends Container
{

	/** @var Repository */
	private $repository;

	/** @var string */
	private $column;

	/** @var array */
	private $items;

	/** @var bool */
	private $loaded = false;

	/** @var IdentifiedEntity[] */
	private $selected = [];


	public function __construct($column = NULL, array $items = NULL)
	{
		if ($column === NULL) {
			$column = 'name';
		}

		$this->column = $column;

		if (!empty($items)) {
			$this->setItems($items);
		}
	}


	/**
	 * @param Repository $repository
	 * @return $this
	 */
	public function setRepository(Repository $repository)
	{
		$this->repository = $repository;

		$this->setItems($repository->findAll());

		return $this;
	}


	/**
	 * @param array|\Nette\Forms\Traversable $values
	 * @param bool $erase
	 * @return Container|void
	 */
	public function setValues($values, $erase = FALSE)
	{
		foreach ($values as $item)
		{
			$this[$item->id]->setValue(true);
		}
	}


	/**
	 * @param bool $asArray
	 * @return array|\movi\Model\IdentifiedEntity[]|\Nette\ArrayHash
	 */
	public function getValues($asArray = FALSE)
	{
		$values = parent::getValues(false);

		foreach ($values as $id => $selected)
		{
			if ($selected) {
				$this->selected[$id] = $this->items[$id];
			}
		}

		return $this->selected;
	}


	/**
	 * @param array $items
	 * @return $this
	 * @throws \movi\InvalidArgumentException
	 */
	public function setItems(array $items)
	{
		foreach ($items as $item)
		{
			if (!($item instanceof IdentifiedEntity)) {
				throw new InvalidArgumentException('Entity must be an instance of IdentifiedEntity!');
			}
		}

		$this->items = $items;

		$this->loadItems();

		return $this;
	}


	private function loadItems()
	{
		if ($this->loaded === false) {
			foreach ($this->items as $item)
			{
				$this->addCheckbox($item->id, $item->{$this->column});
			}

			$this->loaded = true;
		}
	}

}