<?php

namespace movi\Components\Grid\Columns;

use movi\InvalidArgumentException;
use movi\Model\Entity;
use Nette\Callback;
use Nette\Utils\Html;

class Boolean extends Column
{

	/** @persistent */
	public $row;

	/** @var Callback */
	private $callback;

	/** @var Callback*/
	private $disabled;

	/** @var bool */
	private $ajax = false;


	public function __construct()
	{
		parent::__construct();

		$this->setWidth(120);
		$this->setAlign('center');
	}


	public function handleClick()
	{
		$rows = $this->getGrid()->getRows();

		if (isset($rows[$this->row])) {
			$row = $rows[$this->row];

			if (($this->disabled !== NULL && $this->disabled->invoke($row) === true) || $this->disabled === NULL) {
				$this->callback->invoke($row);
			}
		}

		$this->row = NULL;
	}


	/**
	 * @param Entity $row
	 */
	public function render(Entity $row)
	{
		$el = Html::el('i');

		if ($row->{$this->column} === true) {
			$el->class[] = 'icon-ok';
		} else {
			$el->class[] = 'icon-remove';
		}

		if ($this->callback !== NULL) {
			$icon = $el;
			$primaryKey = $this->getGrid()->getPrimaryKey();

			if (($this->disabled !== NULL && $this->disabled->invoke($row) === true) || $this->disabled === NULL) {
				$el = Html::el('a')->href($this->link('click', array('row' => $row->{$primaryKey})));
				$el->class[] = ($this->ajax === true) ? 'ajax' : NULL;
				$el->add($icon);
			}
		}

		echo $el;
	}


	/**
	 * @param $callback
	 * @return $this
	 * @throws \movi\InvalidArgumentException
	 */
	public function setCallback($callback = NULL)
	{
		if ($callback !== NULL) {
			if (!is_callable($callback)) {
				throw new InvalidArgumentException('Callback is not callable');
			}

			$this->callback = Callback::create($callback);
		}

		return $this;
	}


	/**
	 * @param $callback
	 * @return $this
	 * @throws \movi\InvalidArgumentException
	 */
	public function setDisabled($callback)
	{
		if (!is_callable($callback)) {
			throw new InvalidArgumentException('Callback is not callable');
		}

		$this->disabled = Callback::create($callback);

		return $this;
	}


	/**
	 * @return $this
	 */
	public function ajaxify()
	{
		$this->ajax = true;

		return $this;
	}

}