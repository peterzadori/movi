<?php

namespace movi\Components;

use Nette\Http\IRequest;
use movi\Application\UI\Control;
use movi\Application\UI\Form;
use movi\Application\UI\Presenter;

final class Uploadify extends Control
{

	/** @var IRequest */
	private $httpRequest;

	/** @var bool */
	private $debug = false;

	/** @var string */
	private $swf = 'uploadify.swf';

	/** @var bool */
	private $auto = false;

	/** @var array */
	private $fileTypeExts;

	/** @var string */
	private $fileTypeDesc;

	/** @var string */
	private $buttonText = 'Vybrať súbory';

	private $legend = 'Nahrať fotky';

	public $onUpload;

	public $onSuccess;

	public $onError;


	public function attached($presenter)
	{
		parent::attached($presenter);

		if ($presenter instanceof Presenter) {
			$this->httpRequest = $presenter->context->getByType('Nette\Http\IRequest');
		}
	}


	public function handleUpload()
	{
		$file = $this->httpRequest->getFile('Filedata');

		if ($file->isOk()) {
			$this->onUpload($file, $this->httpRequest->getPost());
		} else {
			$this->onError($file->getError());
		}

		$this->presenter->terminate();
	}


	public function handleSuccess()
	{
		$this->onSuccess();
	}


	public function render()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/Uploadify.latte');

		$template->swf = $this->swf;
		$template->auto = $this->auto;
		$template->debug = $this->debug;

		$template->legend = $this->legend;
		$template->debug = $this->debug;
		$template->fileTypeExts = $this->fileTypeExts;
		$template->fileTypeDesc = $this->fileTypeDesc;
		$template->buttonText = $this->buttonText;

		$template->render();
	}


	/**
	 * @return Form
	 */
	protected function createComponentForm()
	{
		$form = new Form();

		$form->addUpload('file', 'Súbor:');

		$form->addSubmit('upload', 'Nahrať');

		return $form;
	}


	/**
	 * @param $legend
	 * @return $this
	 */
	public function setLegend($legend)
	{
		$this->legend = $legend;

		return $this;
	}


	/**
	 * @param $file
	 * @return $this
	 */
	public function setSwf($file)
	{
		$this->swf = $file;

		return $this;
	}


	/**
	 * @param $auto
	 * @return $this
	 */
	public function setAuto($auto)
	{
		$this->auto = $auto;

		return $this;
	}


	/**
	 * @return bool
	 */
	public function isAuto()
	{
		return $this->auto;
	}


	/**
	 * @param $text
	 * @return $this
	 */
	public function setButtonText($text)
	{
		$this->buttonText = $text;

		return $this;
	}


	/**
	 * @param $extensions
	 * @return $this
	 */
	public function setFileTypeExts($extensions)
	{
		$this->fileTypeExts = $extensions;

		return $this;
	}


	/**
	 * @param $description
	 * @return $this
	 */
	public function setFileTypeDesc($description)
	{
		$this->fileTypeDesc = $description;

		return $this;
	}


	/**
	 * @param $debug
	 * @return $this
	 */
	public function setDebug($debug)
	{
		$this->debug = $debug;

		return $this;
	}

}