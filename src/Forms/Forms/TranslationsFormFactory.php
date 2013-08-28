<?php

namespace movi\Forms;

use movi\Application\UI\Form;
use movi\Localization\Languages;
use movi\Model\TranslatableEntity;

abstract class TranslationsFormFactory extends EntityFormFactory
{

	/** @var \movi\Model\Entities\Language[] */
	protected $languages;


	public function __construct(Languages $languages)
	{
		$this->languages = $languages->getLanguages();
	}


	/**
	 * @return Form
	 */
	public function createForm()
	{
		$form = parent::createForm();

		$this->setTranslations($form);

		$form->onSuccess[] = $this->processTranslations;

		return $form;
	}


	/**
	 * @param Form $form
	 */
	public function processTranslations(Form $form)
	{
		$values = $form->getValues();

		/** @var $entity TranslatableEntity */
		$entity = $this->entity;
		$columns = $entity->getTranslatableColumns();

		foreach ($this->languages as $language)
		{
			if (isset($values->translations[$language->id])) {
				$translation = $values->translations[$language->id];

				foreach ($translation as $key => $value)
				{
					if (array_key_exists($key, $columns)) {
						$this->entity->{$key} = $value;
					}
				}

				$this->entity->language = $language;

				$this->repository->persist($this->entity);
			}
		}
	}


	/**
	 * @param Form $form
	 */
	private function setTranslations(Form $form)
	{
		if ($this->entity !== NULL && !$this->entity->isDetached()) {
			if ($translationsContainer = $form->getComponent('translations', false)) {
				$translationsContainer->setDefaults($this->repository->getTranslations($this->entity));
			}
		}
	}

}