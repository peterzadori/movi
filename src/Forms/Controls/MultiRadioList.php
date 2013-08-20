<?php
/**
 * User: Peter Zádori
 * Date: 6.5.2013
 * Time: 20:52
 * To change this template use File | Settings | File Templates.
 */

namespace movi\Forms\Controls;

use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

class MultiRadioList extends BaseControl
{

	/** @var array */
	private $items;


	public function __construct($label = NULL, array $items = array())
	{
		parent::__construct($label);
		$this->control->type = 'radio';

		if ($items !== NULL) {
			$this->setItems($items);
		}
	}


	/**
	 * @param array $items
	 * @return $this
	 */
	public function setItems(array $items)
	{
		$this->items = $items;

		return $this;
	}


	/**
	 * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}


	/**
	 * @param bool $raw
	 * @return bool|float|int|mixed|null|string
	 */
	public function getValue($raw = false)
	{
		return is_scalar($this->value) && ($raw || isset($this->items[$this->value])) ? $this->value : NULL;
	}


	/**
	 * @param null $key
	 * @return Html
	 */
	public function getControl($key = NULL)
	{
		if ($key !== NULL) {
			return $this->getKeyControl($key);
		}

		$container = Html::el();

		foreach ($this->items as $value => $label)
		{
			$container->add((string) $this->getKeyControl($value) . (string) $this->getLabel($label, $value));
		}

		return $container;
	}


	/**
	 * @param null $caption
	 * @param null $key
	 * @return Html
	 */
	public function getLabel($caption = NULL, $key = NULL)
	{
		if ($key !== NULL) {
			return $this->getKeyLabel($key);
		}

		$label = parent::getLabel($caption);
		return $label;
	}


	/**
	 * @param $key
	 * @return Html
	 */
	public function getKeyControl($key)
	{
		$control = parent::getControl();

		if (!isset($this->items[$key])) {
			return $control;
		}

		if ($this->getValue() == $key) {
			$control->checked = 'checked';
		}

		$control->id .= '-' . $key;
		$control->value = $key;

		return $control;
	}


	/**
	 * @param null $key
	 * @return Html
	 */
	public function getKeyLabel($key = NULL)
	{
		$label = parent::getLabel();

		if (!isset($this->items[$key])) {
			return $label;
		}

		$label->setText($this->items[$key]);
		$label->for .= '-' . $key;

		return $label;
	}

}