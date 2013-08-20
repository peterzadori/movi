<?php

namespace movi\Components;

use Nette;
use movi;

final class Uploadify extends Nette\Application\UI\Control
{

	/** @var bool */
	private $debug;

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
		if ($presenter instanceOf Nette\Application\IPresenter) {
			$this->debug = $presenter->context->params['debugMode'];
		}
	}


	public function handleUpload()
	{
		$file = new Nette\Http\FileUpload($_FILES['Filedata']);

		if ($file->isOk()) {
			$this->onUpload($file);
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
		$template->fileTypeExts = $this->fileTypeExts;
		$template->fileTypeDesc = $this->fileTypeDesc;
		$template->buttonText = $this->buttonText;

		$template->render();
	}


	protected function createComponentForm()
	{
		$form = new movi\Application\UI\Form();

		$form->addUpload('file', 'Súbor:');

		$form->addSubmit('upload', 'Nahrať');

		return $form;
	}

	public function setLegend($legend)
	{
		$this->legend = $legend;

		return $this;
	}


	public function getLegend()
	{
		return $this->legend;
	}


	public function setSwf($file)
	{
		$this->swf = $file;

		return $this;
	}


	public function getSwf()
	{
		return $this->swf;
	}


	public function setAuto($auto)
	{
		$this->auto = $auto;

		return $this;
	}


	public function isAuto()
	{
		return $this->auto;
	}


	public function setButtonText($text)
	{
		$this->buttonText = $text;

		return $this;
	}


	public function getButtonText()
	{
		return $this->buttonText;
	}


	public function setFileTypeExts($extensions)
	{
		$this->fileTypeExts = $extensions;

		return $this;
	}


	public function getFileTypeExts()
	{
		return $this->fileTypeExts;
	}


	public function setFileTypeDesc($description)
	{
		$this->fileTypeDesc = $description;

		return $this;
	}


	public function getFileTypeDesc()
	{
		return $this->fileTypeDesc;
	}


	public function setData(array $data)
	{
		$this->data = $data;

		return $this;
	}


	public function getData()
	{
		return $this;
	}

}