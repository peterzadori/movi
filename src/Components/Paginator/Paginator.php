<?php

namespace movi\Components;

use movi\Application\UI\Control;
use Nette\Utils\Paginator as NettePaginator;

class Paginator extends Control
{

	/** @var NettePaginator */
	private $paginator;

	/** @var $templateFile */
	private $templateFile;

	/** @persistent */
	public $page = 1;


	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		$template->setFile((empty($this->templateFile) ? __DIR__ . '/Paginator.latte' : $this->templateFile));

		return $template;
	}


	public function render()
	{
		$paginator = $this->getPaginator();
		$page = $paginator->page;

		if ($paginator->pageCount < 2) {
			$steps = array($page);
		} else {
			$arr = range(max($paginator->firstPage, $page - 3), min($paginator->lastPage, $page + 3));
			$count = 4;
			$quotient = ($paginator->pageCount - 1) / $count;

			for ($i = 0; $i <= $count; $i++) {
				$arr[] = round($quotient * $i) + $paginator->firstPage;
			}

			sort($arr);
			$steps = array_values(array_unique($arr));
		}

		$template = $this->template;

		$template->steps = $steps;
		$template->paginator = $paginator;

		$template->render();
	}

	/**
	 * @param  array
	 * @return void
	 */
	public function loadState(array $params)
	{
		parent::loadState($params);

		$this->getPaginator()->page = $this->page;
	}


	/**
	 * @return NettePaginator
	 */
	public function getPaginator()
	{
		if (!$this->paginator) {
			$this->paginator = new NettePaginator;
		}

		return $this->paginator;
	}


	/**
	 * @param $file
	 * @return $this
	 */
	public function setTemplateFile($file)
	{
		$this->templateFile = $file;

		return $this;
	}

}