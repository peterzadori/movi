<?php

namespace movi\Application\UI;

use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Localization\ITranslator;
use Nette;
use movi;

class Form extends Nette\Application\UI\Form
{

	/**
	 * @param $presenter
	 */
	public function attached($presenter)
	{
		parent::attached($presenter);

		$renderer = new BootstrapRenderer();
		$this->setRenderer($renderer);

		$this->onInvalidSubmit = false;

		if ($presenter instanceOf Presenter) {
			if (isset($presenter->translator) && $presenter->translator instanceof ITranslator) {
				$this->setTranslator($presenter->translator);
			}

			if ($presenter->isAjax()) {
				$this->getElementPrototype()->class[] = 'ajax';
			}
		}
	}
}