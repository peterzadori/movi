<?php

namespace movi\Localization;

use Nette\Object;
use movi;

final class Language extends Object
{

	/** @var \movi\Model\Entities\Language */
	private $defaultLanguage = NULL;

	/** @var \movi\Model\Entities\Language */
	private $language = NULL;

	/** @var array */
	public $onSet;


	public function setDefaultLanguage($language)
	{
		$this->defaultLanguage = $language;
	}


	/**
	 * @param $language
	 */
	public function setLanguage(movi\Model\Entities\Language $language)
	{
		if ($this->language == NULL || $language->code != $this->language->code) {
			$this->language = $language;

			$this->onSet($language);
		}
	}


	/**
	 * @return DibiRow|null
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
		return (bool) $this->language;
	}


	/**
	 * @param $name
	 * @return mixed
	 * @throws \movi\InvalidStateException
	 */
	public function &__get($name)
	{
		if ($this->language == NULL) {
			throw new movi\InvalidStateException('Language is not set!');
		}

		$value = $this->language->{$name};
		return $value;
	}

}