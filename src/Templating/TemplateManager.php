<?php

namespace movi\Templating;

use Nette\Object;
use movi\Packages\Settings\Services\Settings;

class TemplateManager extends Object
{

	/** @var string */
	private $templatesDir;


	public function __construct($templatesDir, Settings $settings)
	{
		$this->templatesDir = $templatesDir . '/' . $settings->templating->template;
	}


	/**
	 * @return string
	 */
	public function getTemplatesDir()
	{
		return $this->templatesDir;
	}

}