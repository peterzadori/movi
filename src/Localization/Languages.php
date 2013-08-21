<?php

namespace movi\Localization;

use movi\Model\Repositories\LanguagesRepository;
use Nette\Object;
use movi\Model\Entities\Language as LanguageEntity;

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

		foreach ($this->languages as $language)
		{
			if ($language->default === true) {
				$this->setCurrent($language);
				$this->default = $language;
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