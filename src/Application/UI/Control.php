<?php

namespace movi\Application\UI;

use Nette;

class Control extends Nette\Application\UI\Control
{

	/**
	 * @param null $class
	 * @return Nette\Templating\ITemplate
	 */
	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);

		if ($this->presenter instanceOf Presenter) {
			$this->presenter->templatePrepareFilters($template);

			$template->setTranslator($this->presenter->getTranslator());
			$template->registerHelperLoader(callback($this->presenter->getHelpers(), 'loader'));
		}

		return $template;
	}

}