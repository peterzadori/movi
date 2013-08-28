<?php

namespace movi\Components\Grid;

use Nette\Callback;
use Nette\Utils\Html;
use movi\InvalidArgumentException;
use movi\Model\Entity;

class Button extends Component
{

	/** @persistent */
	public $row;

	/** @var string */
	private $label;

	/** @var string */
	private $class;

	/** @var Callback */
	private $callback;

	/** @var bool */
	private $ajax = false;

	/** @var string|Callback */
	private $confirmation;

	/** @var Callback, If true is returned, don't show the button */
	private $disabled;


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
		$button = Html::el('a');
		$button->class[] = 'btn';
		$button->class[] = 'btn-mini';
		$button->class[] = $this->class;

		// Check if disabled
		if ($this->disabled !== NULL) {
			if ($this->disabled->invoke($row) === true) {
				return;
			}
		}

		if ($this->ajax === true) {
			$button->class[] = 'ajax';
		}

		if ($this->confirmation !== NULL) {
			$button->data['confirm'] = (is_callable($this->confirmation) ? $this->confirmation->invoke($row) : $this->confirmation);
		}

		if ($this->callback !== NULL) {
			$primaryKey = $this->getGrid()->getPrimaryKey();

			$button->href($this->link('click', array('row' => $row->{$primaryKey})));
		}

		$button->setText($this->label);

		echo $button;
	}


	/**
	 * @param $label
	 * @return $this
	 */
	public function setLabel($label)
	{
		$this->label = $label;

		return $this;
	}


	/**
	 * @param $class
	 * @return $this
	 */
	public function setClass($class)
	{
		$this->class = $class;

		return $this;
	}


	/**
	 * @param $callback
	 * @return $this
	 * @throws \movi\InvalidArgumentException
	 */
	public function setCallback($callback)
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
	 * @return $this
	 */
	public function ajaxify()
	{
		$this->ajax = true;

		return $this;
	}


	/**
	 * @param $message
	 * @return $this
	 */
	public function setConfirmation($message)
	{
		if (is_callable($message)) {
			$message = Callback::create($message);
		}

		$this->confirmation = $message;

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

}