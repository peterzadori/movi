<?php

namespace movi\Application\Routers;

use movi\Localization\Languages;
use Nette\Application\Routers\Route;

class LanguageRoute extends Route
{

	/** @var string */
	private $languageMask = '[<lang>/]';


	public function __construct($mask, $metadata = [], Languages $languages)
	{
		if (count($languages->getLanguages()) > 1) {
			$mask = $this->languageMask . $mask;

			if (!isset(Route::$styles['lang'])) {
				Route::addStyle('lang');
				Route::setStyleProperty('lang', Route::FILTER_IN, function ($code) use ($languages) {
					$languages->setCurrent($code);

					return $languages->getCurrent()->code;
				});
			}
		}

		parent::__construct($mask, $metadata);
	}

}