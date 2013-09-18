<?php

namespace movi\Application\Routers;

use movi\Localization\Languages;
use Nette\Application\Routers\Route;

class LangRoute extends Route
{

	/** @var string */
	private $languageMask = '[<lang>/]';


	public function __construct($mask, $metadata = [], Languages $languages)
	{
		if (count($languages->getLanguages()) > 1) {
			$mask = $this->languageMask . $mask;
		}

		parent::__construct($mask, $metadata);
	}

}