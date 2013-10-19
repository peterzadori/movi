<?php

namespace movi\Localization;

use movi\EntityNotFound;
use movi\InvalidStateException;
use Nette\Object;
use movi\Model\Entities\Language as LanguageEntity;

final class Languages extends Object
{

	private $languagesRepository;

	/** @var Language[] */
	private $languages;

	/** @var Language */
	private $current;

	/** @var LanguageEntity */
	private $default;

	public $onSet;


	public function __construct(Language $language, ILanguagesRepository $languagesRepository)
	{
		$this->languagesRepository = $languagesRepository;
		$this->languages = $languagesRepository->getActive();

		if (count($this->languages) > 0) {
			$this->current = $language;

			foreach ($this->languages as $language)
			{
				if ($language->default === true) {
					$this->default = $language;

					$this->setCurrent($language);
				}
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
	public function setCurrent($language)
	{
		if (!($language instanceof LanguageEntity)) {
			try {
				$language = $this->languagesRepository->findByCode($language);
			} catch (EntityNotFound $e) {
				$language = $this->default;
			}
		}

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