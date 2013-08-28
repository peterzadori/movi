<?php

namespace movi\Localization;

use Nette\Object;
use movi\Model\Entities\Language as LanguageEntity;
use movi\Model\Repositories\LanguagesRepository;

final class Languages extends Object
{

	/** @var Language[] */
	private $languages;

	/** @var Language */
	private $current;

	/** @var LanguageEntity */
	private $default;

	public $onSet;


	public function __construct(Language $language, LanguagesRepository $languagesRepository)
	{
		$this->languages = $languagesRepository->findActive();
		$this->current = $language;

		foreach ($this->languages as $language)
		{
			if ($language->default === true) {
				$this->default = $language;

				$this->setCurrent($language);
			}
		}
	}


	/**
	 * @return array|\movi\Model\Entities\Language[]
	 */
	public function getLanguages()
	{
		return $this->languages;
	}


	/**
	 * @param LanguageEntity $language
	 * @return $this
	 */
	public function setCurrent(LanguageEntity $language)
	{
		$this->current->setLanguage($language);

		return $this;
	}


	/**
	 * @return Language
	 */
	public function getCurrent()
	{
		return $this->current->getLanguage();
	}


	/**
	 * @return Language
	 */
	public function getLanguage()
	{
		return $this->current->getLanguage();
	}


	public function isLanguageSet()
	{
		return ($this->current === NULL) ? false : true;
	}


	/**
	 * @return Language
	 */
	public function getDefault()
	{
		return $this->default;
	}

}