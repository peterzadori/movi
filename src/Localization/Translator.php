<?php

namespace movi\Localization;

use Nette\Localization\ITranslator;

final class Translator implements ITranslator
{

	/** @var \movi\Localization\Translations */
	private $translations;


	public function __construct(Translations $translations)
	{
		$this->translations = $translations;
	}


	/**
	 * @param $message
	 * @param null $count
	 * @return string
	 */
	public function translate($message, $count = NULL)
	{
		return $this->translations->getTranslation($message);
	}

}