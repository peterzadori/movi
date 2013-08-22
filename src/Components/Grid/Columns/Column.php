<?php

namespace movi\Components\Grid\Columns;

use LeanMapper\Entity;
use movi\Components\Grid\Component;
use movi\Components\Grid\Filters\Filter;
use movi\InvalidArgumentException;
use Nette\Callback;
use Nette\Utils\Html;

class Column extends Component
{

	/** @var string */
	protected $label;

	/** @var string */
	protected $column;

	/** @var bool */
	protected $sortable = true;

	/** @var bool */
	protected $sorting = false;

	/** @var Callback */
	protected $renderer;

	/** @var Filter */
	protected $filter;

	/** @var Html */
	protected $head;

	/** @var Html */
	protected $cell;

	/** @var \Closure */
	protected $headFactory;

	/** @var \Closure */
	protected $cellFactory;


	public function __construct()
	{
		$this->headFactory = function() {
			return Html::el('th');
		};

		$this->cellFactory = function() {
			return Html::el('td');
		};

		// Default renderer
		$this->renderer = function($row) {
			return $row->{$this->column};
		};
	}


	public function render($row)
	{
		echo $this->renderer->__invoke($row);
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


	public function getLabel()
	{
		return $this->label;
	}


	/**
	 * @param $column
	 * @return $this
	 */
	public function setColumn($column)
	{
		$this->column = $column;

		return $this;
	}


	public function getColumn()
	{
		return $this->column;
	}


	/**
	 * @return $this
	 */
	public function setPrimary()
	{
		$this->getGrid()->setPrimaryKey($this->column);

		return $this;
	}


	/**
	 * @param $sortable
	 * @return $this
	 */
	public function setSortable($sortable)
	{
		$this->sortable = $sortable;

		return $this;
	}


	public function setSorting()
	{
		$this->sorting = true;

		return $this;
	}


	public function isSorting()
	{
		return $this->sorting;
	}


	/**
	 * @return bool
	 */
	public function isSortable()
	{
		return $this->sortable;
	}


	/**
	 * @param $callback
	 * @return $this
	 * @throws \movi\InvalidArgumentException
	 */
	public function setRenderer($callback)
	{
		if (!is_callable($callback)) {
			throw new InvalidArgumentException('Renderer is not callable');
		}

		$this->renderer = Callback::create($callback);

		return $this;
	}


	/**
	 * @param Filter $filter
	 * @return $this
	 */
	public function setFilter(Filter $filter)
	{
		$filter->setGrid($this->getGrid());
		$filter->setColumn($this);

		$this->filter = $filter;

		// Add filter to the grid
		$this->getGrid()->addFilter($filter);

		return $this;
	}


	public function getFilter()
	{
		return $this->filter;
	}


	/**
	 * @return bool
	 */
	public function hasFilter()
	{
		return ($this->filter === NULL) ? false : true;
	}


	/**
	 * @param $width
	 * @return $this
	 */
	public function setWidth($width)
	{
		$this->getHead()->width = $width;

		return $this;
	}


	public function setAlign($align)
	{
		$this->getHead()->align = $align;
		$this->getCell()->align = $align;

		return $this;
	}


	/**
	 * @return Html
	 */
	public function getHead()
	{
		if ($this->head === NULL) {
			$this->head = $this->headFactory->__invoke();
		}

		return $this->head;
	}


	/**
	 * @return Html
	 */
	public function getCell()
	{
		if ($this->cell === NULL) {
			$this->cell = $this->cellFactory->__invoke();
		}

		return $this->cell;
	}

}