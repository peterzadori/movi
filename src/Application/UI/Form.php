<?php

namespace movi\Application\UI;

use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette;

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

		if ($presenter instanceOf Presenter) {
            $this->setTranslator($presenter->getTranslator());

			if ($presenter->isAjax()) {
				$this->getElementPrototype()->class[] = 'ajax';
			}
		}
	}
}