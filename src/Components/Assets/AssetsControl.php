<?php

namespace movi\Components\Assets;

use movi\Application\UI\Control;

class AssetsControl extends Control
{

	/** @var \movi\Components\Assets\AssetsManager */
	private $assetsManager;


	public function __construct(AssetsManager $manager)
	{
		$this->assetsManager = $manager;
	}


	public function attached($presenter)
	{
		parent::attached($presenter);

		$this->assetsManager->build();
	}


	public function renderCss()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/templates/css.latte');

		$template->files = $this->assetsManager->getCss();

		$template->render();
	}


	public function renderJs()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/templates/js.latte');

		$template->files = $this->assetsManager->getJs();

		$template->render();
	}

}