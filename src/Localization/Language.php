<?php

namespace movi\Localization;

use Nette\Object;
use movi\Model\Entities\Language as LanguageEntity;

final class Language extends Object
{

	/** @var LanguageEntity */
	private $language;

	public $onSet;


	public function setLanguage(LanguageEntity $language)
	{
		if ($this->language == NULL || $language->code != $this->language->code) {
			$this->language = $language;

			$this->onSet($language);
		}
	}


	/**
	 * @return LanguageEntity
	 */
	public function getCurrent()
	{
		return $this->language;
	}


	/**
	 * @return LanguageEntity
	 */
	public function getLanguage()
	{
		return $this->language;
	}


	/**
	 * @return bool
	 */
	public function isLanguageSet()
	{
		return ($this->language === NULL) ? false : true;
	}

}