<?php

namespace movi\Forms;

use Nette\Forms\Container;
use Nette\Http\FileUpload;
use movi\Application\UI\Form;
use movi\Model\Entity;
use movi\Model\Repository;

abstract class EntityFormFactory extends FormFactory
{

	/** @var Entity */
	protected $entity;

	/** @var Repository */
	protected $repository;


	/**
	 * @param Entity $entity
	 * @return $this
	 */
	public function setEntity(Entity $entity)
	{
		$this->entity = $entity;

		return $this;
	}


	/**
	 * @param Repository $repository
	 * @return $this
	 */
	public function setRepository(Repository $repository)
	{
		$this->repository = $repository;

		return $this;
	}


	/**
	 * @return Form
	 */
	public function createForm()
	{
		$form = parent::createForm();

		$this->setData($form);

		$form->onSuccess[] = $this->processValues;

		return $form;
	}


	/**
	 * @param Form $form
	 */
	public function processValues(Form $form)
	{
		$values = $form->getValues();
		$properties = $this->entity->getReflection()->getEntityProperties();
		$modified = $this->entity->getModifiedRowData();

		foreach ($values as $key => $value)
		{
			if ($value instanceof FileUpload || $value instanceof \Traversable) {
				continue;
			}

			if (array_key_exists($key, $properties)) {
				$property = $properties[$key];

				if ($property->hasRelationship() === false && $property->isBasicType() && array_key_exists($key, $modified)) {
					$this->entity->{$key} = $value;
				}
			}
		}

		if ($this->entity->isModified()) {
			$this->repository->persist($this->entity);
		}
	}


	/**
	 * @return Entity
	 */
	public function getEntity()
	{
		return $this->entity;
	}


	/**
	 * @param Form $form
	 */
	private function setData(Form $form)
	{
		if (!$this->entity->isDetached()) {
			foreach ($form->getComponents() as $control)
			{
				$name = $control->getName();

				if (isset($this->entity->{$name})) {
					if ($control instanceof Container) {
						$control->setDefaults($this->entity->{$name});
					} else {
						$control->setDefaultValue($this->entity->{$name});
					}
				}
			}
		}
	}

}