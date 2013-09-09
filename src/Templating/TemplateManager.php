<?php

namespace movi\Templating;

use Nette\Object;
use movi\Packages\Settings\Services\Settings;

class TemplateManager extends Object
{

	/** @var string */
	private $templatesDir;


	public function __construct($templatesDir)
	{
		$this->templatesDir = $templatesDir;
	}


	/**
	 * @param $dir
	 * @return $this
	 */
	public function setTemplatesDir($dir)
	{
		$this->templatesDir .= '/' . $dir;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getTemplatesDir()
	{
		return $this->templatesDir;
	}

}