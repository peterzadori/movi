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

		if ($this->presenter instanceOf Nette\Application\IPresenter) {
			$this->presenter->templatePrepareFilters($template);

			$template->setTranslator($this->presenter->translator);
			$template->registerHelperLoader(callback($this->presenter->helpers, 'loader'));
		}

		return $template;
	}

}