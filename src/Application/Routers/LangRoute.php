<?php

namespace movi\Application\Routers;

use movi\EntityNotFound;
use movi\Localization\Languages;
use movi\Model\Repositories\LanguagesRepository;
use Nette\Application\Routers\Route;

class LangRoute extends Route
{

	/** @var string */
	private $languageMask = '[<lang>/]';


	public function __construct($mask, $metadata = [], Languages $languages, LanguagesRepository $languagesRepository)
	{
		if (count($languages->getLanguages()) > 1) {
			$mask = $this->languageMask . $mask;

			if (!isset(Route::$styles['lang'])) {
				Route::addStyle('lang');
				Route::setStyleProperty('lang', Route::FILTER_IN, function ($code) use ($languages, $languagesRepository) {
					try {
						$row = $languagesRepository->findBy(array('[code] = %s' => $code));

						$languages->setCurrent($row);
					} catch (EntityNotFound $e) {

					}

					return $languages->getCurrent()->code;
				});
			}
		}

		parent::__construct($mask, $metadata);
	}

}