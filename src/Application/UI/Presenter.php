<?php

namespace movi\Application\UI;

use movi\Application\Application;
use movi\Localization\Language;
use movi\Localization\Translator;
use movi\Packages\Settings\Services\Settings;
use movi\Templating\Helpers;
use movi\Templating\TemplateManager;
use Nette\DI\Container;

abstract class Presenter extends \Nette\Application\UI\Presenter
{

	/** @var \movi\Localization\Language */
	protected $language;

	/** @var \movi\Localization\Translator */
	protected $translator;

	/** @var \movi\Templating\Helpers */
	protected $helpers;

	/** @var array */
	protected $languages;

	/** @var Settings */
	protected $settings;

	/** @var TemplateManager */
	protected $templateManager;


	public function injectServices(Language $language, Translator $translator, Settings $settings)
	{
		$this->language = $language;
		$this->translator = $translator;
		$this->settings = $settings;
		$this->languages = Application::$languages;
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
	 * @return null|Language
	 */
	public function getDefaultLanguage()
	{
		foreach ($this->languages as $language)
		{
			if ($language->default == true) {
				return $language;
			}
		}

		return NULL;
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
	 * @return Settings
	 */
	public function getSettings()
	{
		return $this->settings;
	}


	/**
	 * @return TemplateManager
	 */
	public function getTemplateManager()
	{
		return $this->templateManager;
	}

}