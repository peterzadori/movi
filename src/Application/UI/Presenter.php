<?php

namespace movi\Application\UI;

use movi\Localization\Languages;
use movi\Localization\Translator;
use movi\Templating\Helpers;
use movi\Templating\TemplateManager;
use Nette\DI\Container;
use movi\Model\Entities\Language;

abstract class Presenter extends \Nette\Application\UI\Presenter
{

	/** @var Language */
	protected $language;

	/** @var \movi\Localization\Translator */
	protected $translator;

	/** @var \movi\Templating\Helpers */
	protected $helpers;

	/** @var array */
	protected $languages;

	/** @var TemplateManager */
	protected $templateManager;


	public function injectLocalization(Languages $languages, Translator $translator)
	{
		$this->languages = $languages->getLanguages();
		$this->language = $languages->getLanguage();
		$this->translator = $translator;
	}


	public function injectTemplating(Helpers $helpers, TemplateManager $templateManager)
	{
		$this->helpers = $helpers;
		$this->templateManager = $templateManager;
	}


	public function injectContainer(Container $context)
	{
		if ($context && $this->invalidLinkMode === NULL) {
			$this->invalidLinkMode = $context->parameters['productionMode'] ? self::INVALID_LINK_SILENT : self::INVALID_LINK_WARNING;
		}
	}


	/**
	 * @param null $class
	 * @return \Nette\Templating\ITemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);

		$template->setTranslator($this->translator);
		$template->registerHelperLoader(callback($this->helpers, 'loader'));

		return $template;
	}


	/**
	 * @return string
	 */
	protected function getTemplatesDir()
	{
		return $this->templateManager->getTemplatesDir();
	}


	/**
	 * @return array
	 */
	public function getLanguages()
	{
		return $this->languages;
	}


	/**
	 * @return Language
	 */
	public function getLanguage()
	{
		return $this->language;
	}


	/**
	 * @return Translator
	 */
	public function getTranslator()
	{
		return $this->translator;
	}


	/**
	 * @return Helpers
	 */
	public function getHelpers()
	{
		return $this->helpers;
	}


	/**
	 * @return TemplateManager
	 */
	public function getTemplateManager()
	{
		return $this->templateManager;
	}

}