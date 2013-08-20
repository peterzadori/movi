<?php

namespace movi\Application\UI;

use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette;
use movi;

class Form extends Nette\Application\UI\Form
{

	public function __construct()
	{
		parent::__construct();

		$renderer = new BootstrapRenderer();
		$this->setRenderer($renderer);
	}


	/**
	 * @param $presenter
	 */
	public function attached($presenter)
	{
		parent::attached($presenter);

		$this->onInvalidSubmit = false;
		$this->onSuccess[] = callback($this, 'processForm');

		if ($presenter instanceOf Nette\Application\IPresenter) {
			$this->addProtection("Ouchie! Please try to submit the form again, the delivery boy forgot something!");

			if (isset($presenter->translator) && $presenter->translator instanceof Nette\Localization\ITranslator) {
				$this->setTranslator($presenter->translator);
			}

			if ($presenter->isAjax()) {
				$this->getElementPrototype()->class[] = 'ajax';
			}

			// Configure the form
			$this->configure();
		}
	}


	protected function configure()
	{

	}


	public function processForm($form)
	{

	}
}