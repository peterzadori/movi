<?php

namespace movi\Application\UI;

use Nette;

abstract class Control extends Nette\Application\UI\Control
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

		$file = $this->getTemplateFile();

		if (file_exists($file) && $template instanceof Nette\Templating\IFileTemplate) {
			$template->setFile($file);
		}

		return $template;
	}


	/**
	 * @return Nette\Security\User
	 */
	public function getUser()
	{
		return $this->presenter->getUser();
	}


	/**
	 * @return string
	 */
	private function getTemplateFile()
	{
		$reflection = $this->getReflection();

		return dirname($reflection->getFileName()) . '/' . $reflection->getName() . '.latte';
	}

}