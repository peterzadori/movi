<?php

namespace movi\Application\Routers;

use movi\Localization\Languages;
use Nette\Application\Routers\Route;

class LangRoute extends Route
{

	private $languageMask = '[<lang>/]';


	public function __construct($mask, $metadata = array(), Languages $languages)
	{
		if (count($languages->getLanguages()) > 1) {
			$mask = $this->languageMask . $mask;
		}

		parent::__construct($mask, $metadata);
	}

}