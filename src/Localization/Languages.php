<?php

namespace movi\Localization;

use movi\Model\Repositories\LanguagesRepository;
use Nette\Object;
use movi\Model\Entities\Language;

final class Languages extends Object
{

	/** @var Language[] */
	private $languages;

	/** @var Language */
	private $current;

	/** @var Language */
	private $default;

	public $onSet;


	public function __construct(LanguagesRepository $languagesRepository)
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
	 * @param Language $language
	 * @return $this
	 */
	public function setCurrent(Language $language)
	{
		if ($this->current == NULL || $language->code != $this->current->code) {
			$this->current = $language;

			$this->onSet($language);
		}

		return $this;
	}


	/**
	 * @return Language
	 */
	public function getCurrent()
	{
		return $this->current;
	}


	/**
	 * @return Language
	 */
	public function getLanguage()
	{
		return $this->current;
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