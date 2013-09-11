<?php

namespace movi\Components\Grid;

use Nette\Application\UI\Presenter;
use Nette\ArrayHash;
use Nette\Callback;
use Nette\ComponentModel\Container;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use movi\Application\UI\Control;
use movi\Application\UI\Form;
use movi\Components\Grid\Columns\Boolean;
use movi\Components\Grid\Columns\Column;
use movi\Components\Grid\Columns\Date;
use movi\Components\Grid\Columns\Email;
use movi\Components\Grid\Columns\Money;
use movi\Components\Grid\Filters\Filter;
use movi\Components\Paginator;
use movi\InvalidArgumentException;
use movi\InvalidStateException;

class Grid extends Control
{

	/** @persistent */
	public $sorting;

	/** @persistent */
	public $filter = [];

	/** @var array */
	private $defaultSorting;

	/** @var int */
	private $itemsPerPage = 20;

	/** @var string */
	private $primaryKey = 'id';

	/** @var IDataSource */
	private $dataSource;

	/** @var array */
	private $actions = [];

	/** @var Html */
	private $table;

	/** @var \Closure */
	private $tableFactory;

	/** @var \Closure */
	private $rowFactory;

	/** @var array */
	private $rows;

	/** @var array */
	private $filters;

	public $onFetch;

	public $onRender;


	public function __construct()
	{
		// Create factories
		$this->tableFactory = function() {
			$table = Html::el('table');
			$table->class[] = 'table';
			$table->class[] = 'table-striped';
			$table->class[] = 'table-hover';

			return $table;
		};

		$this->rowFactory = function() {
			$row = Html::el('tr');

			return $row;
		};

		// Set default sorting
		$this->defaultSorting = [$this->primaryKey, 'DESC'];
	}


	/**
	 * @param $factory
	 * @return $this
	 * @throws \movi\InvalidArgumentException
	 */
	public function setRowFactory($factory)
	{
		if (!is_callable($factory)) {
			throw new InvalidArgumentException('Row factory is not callable!');
		}

		$this->rowFactory = $factory;

		return $this;
	}


	/**
	 * @param $factory
	 * @return $this
	 * @throws \movi\InvalidArgumentException
	 */
	public function setTableFactory($factory)
	{
		if (!is_callable($factory)) {
			throw new InvalidArgumentException('Table factory is not callable!');
		}

		$this->tableFactory = $factory;

		return $this;
	}


	/**
	 * @param IDataSource $dataSource
	 * @return $this
	 */
	public function setDataSource(IDataSource $dataSource)
	{
		$this->dataSource = $dataSource;

		return $this;
	}


	/**
	 * @param array $sorting
	 * @return $this
	 */
	public function setDefaultSorting(array $sorting)
	{
		$this->defaultSorting = $sorting;

		return $this;
	}


	public function setItemsPerPage($count)
	{
		if ($count < 1) {
			$count = 1;
		}

		$this->itemsPerPage = $count;
	}


	/**
	 * @param $key
	 * @return $this
	 */
	public function setPrimaryKey($key)
	{
		$this->primaryKey = $key;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}


	public function render()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/Grid.latte');

		$template->rows = $this->getRows();
		$template->actions = $this->actions;

		$this->onRender($this, $template);

		$template->render();
	}


	/**
	 * @return array
	 */
	public function getRows()
	{
		$this->rows = [];

		$dataSource = $this->dataSource->getClone();

		$this->filter($dataSource);
		$this->sort($dataSource);

		/** @var \Nette\Utils\Paginator $paginator */
		$paginator = $this['paginator']->getPaginator();
		$paginator->itemsPerPage = $this->itemsPerPage;
		$paginator->itemCount = $dataSource->count();

		$dataSource->limit($this->itemsPerPage, $paginator->offset);

		$this->onFetch($dataSource);

		$rows = $dataSource->fetch();

		foreach ($rows as $row)
		{
			$this->rows[$row->{$this->primaryKey}] = $row;
		}

		$this->addCheckboxes();

		return $this->rows;
	}


	private function addCheckboxes()
	{
		if (count($this->actions) > 0) {
			foreach (array_keys($this->rows) as $row)
			{
				if (!isset($this['form']['item'][$row])) {
					$this['form']['item']->addCheckbox($row);
				}
			}
		}
	}


	private function sort(IDataSource $dataSource)
	{
		if ($this->sorting === NULL) {
			$this->sorting = $this->defaultSorting;
		}

		if ($this->sorting === NULL) {
			return;
		}

		// Check column
		list($column) = $this->sorting;

		if ($column != $this->primaryKey) {
			$column = $this['columns'][$column];

			if (!$column->isSortable()) {
				throw new InvalidArgumentException("Column '$column->name' is not sortable");
			}

			$column->setSorting();
		}

		$dataSource->sort($this->sorting);
	}


	private function filter(IDataSource $dataSource)
	{
		foreach ($this->filter as $column => $value)
		{
			$column = $this['columns'][$column];
			$column->getFilter()->filter($value, $dataSource);
		}
	}


	/*********************** COLUMNS ***********************/


	/**
	 * @param $name
	 * @param null $label
	 * @param Column $column
	 * @return Column
	 */
	public function addColumn($name, $label = NULL, Column $column = NULL)
	{
		$column = ($column !== NULL ? $column : new Column());
		$column->setLabel($label);
		$column->setColumn($name);

		// Attach column
		$this['columns']->addComponent($column, $name);

		return $column;
	}


	/**
	 * @param $name
	 * @param null $label
	 * @param null $format
	 * @return Date
	 */
	public function addDateColumn($name, $label = NULL, $format = NULL)
	{
		$column = new Date();
		$column->setFormat($format);
		$column->setWidth(200);

		return $this->addColumn($name, $label, $column);
	}


	/**
	 * @param $name
	 * @param null $label
	 * @return Email
	 */
	public function addEmailColumn($name, $label = NULL)
	{
		$column = new Email();

		return $this->addColumn($name, $label, $column);
	}


	/**
	 * @param $name
	 * @param null $label
	 * @return Money
	 */
	public function addMoneyColumn($name, $label = NULL)
	{
		$column = new Money();

		return $this->addColumn($name, $label, $column);
	}


	/**
	 * @param $name
	 * @param null $label
	 * @param null $callback
	 * @return \movi\Components\Grid\Columns\Boolean
	 */
	public function addBooleanColumn($name, $label = NULL, $callback = NULL)
	{
		$column = new Boolean();
		$column->setCallback($callback);

		return $this->addColumn($name, $label, $column);
	}


	/*********************** FILTERS ***********************/

	/**
	 * @param Filter $filter
	 */
	public function addFilter(Filter $filter)
	{
		$this->filters[] = $filter;
	}


	public function isFiltering()
	{
		return (count($this->filter) > 0) ? true : false;
	}


	/**
	 * @return bool
	 */
	public function hasFilters()
	{
		return (count($this->filters) > 0) ? true : false;
	}


	/*********************** ACTIONS ***********************/

	/**
	 * @param $name
	 * @param $label
	 * @param $callback
	 * @throws \movi\InvalidArgumentException
	 */
	public function addAction($name, $label, $callback)
	{
		if (isset($this->actions[$name])) {
			throw new InvalidArgumentException("Action '$name' is already added.'");
		}

		if (!is_callable($callback)) {
			throw new InvalidArgumentException('Callback is not callable');
		}

		$this->actions[$name] = Callback::create($callback);

		// Add submit
		$this['form']['action']->addSubmit($name, $label);
	}


	/**
	 * @return bool
	 */
	public function hasActions()
	{
		return (count($this->actions) === 0) ? false : true;
	}


	/*********************** BUTTONS ***********************/

	public function addButton($name, $label = NULL, $callback = NULL)
	{
		$button = new Button();
		$button->setLabel($label);
		$button->setCallback($callback);

		$this['buttons']->addComponent($button, $name);

		return $button;
	}


	public function getButtons()
	{
		return $this['buttons']->getComponents();
	}


	/**
	 * @return bool
	 */
	public function hasButtons()
	{
		return (count($this['buttons']->getComponents()) > 0 ? true : false);
	}


	/*********************** COMPONENTS ***********************/

	/**
	 * @return ColumnsContainer
	 */
	protected function createComponentColumns()
	{
		return new ColumnsContainer();
	}


	/**
	 * @return Container
	 */
	protected function createComponentButtons()
	{
		return new Container();
	}


	/**
	 * @return Paginator
	 */
	protected function createComponentPaginator()
	{
		$paginator = new Paginator();
		$paginator->setTemplateFile(__DIR__ . '/Paginator.latte');

		return $paginator;
	}


	/**
	 * @return Form
	 */
	protected function createComponentForm()
	{
		$form = new Form();

		$item = $form->addContainer('item');

			$item->addCheckbox('all');

		$filter = $form->addContainer('filter');

			$filter->addSubmit('filter', 'Filtrovať');

		$form->addContainer('action');

		$form->onSuccess[] = $this->processForm;

		return $form;
	}


	public function processForm(Form $form)
	{
		if ($form['filter']['filter']->isSubmittedBy()) {
			$filter = $form->values->filter;

			foreach ($filter as $column => $value) {
				if (empty($value)) {
					unset($filter[$column]);
				}
			}

			$this->filter = $filter;
		} else {
			$values = ArrayHash::from($form->getHttpData());

			if (!isset($values->item)) {
				$this->flashMessage('Nezvolili ste záznamy', 'alert-error');

				return false;
			}

			$items = $values->item;
			$action = $form->submitted->name;

			if (!isset($this->actions[$action])) {
				throw new InvalidArgumentException("Action '$action' not found");
			}

			if (count($items) > 0) {
				$data = $this->getRows();
				$rows = [];

				foreach ($items as $item => $checked)
				{
					if ($item == 'all') {
						continue;
					}

					if ($checked !== 'on') {
						continue;
					}

					$rows[] = $data[$item];
				}

				$this->actions[$action]->invoke($rows);

				$this->flashMessage('Akcia bola vykonaná.', 'alert-success');
				$this->invalidateControl();
			}
		}
	}


	public function attached($presenter)
	{
		parent::attached($presenter);

		if ($presenter instanceof Presenter) {
			// Check validity
			$this->validate();

			// Invalidate control
			if ($presenter->isAjax()) {
				$this->invalidateControl();
			}
		}
	}


	private function validate()
	{
		if ($this->dataSource === NULL) {
			throw new InvalidStateException('Data source is not set');
		}

		if (count($this['columns']->getColumns()) == 0) {
			throw new InvalidArgumentException('No columns are set');
		}
	}


	/**
	 * @return Html
	 */
	public function getTable()
	{
		if ($this->table === NULL) {
			$this->table = $this->tableFactory->__invoke();
		}

		return $this->table;
	}


	/**
	 * @param $row
	 * @return mixed
	 */
	public function getRow($row)
	{
		return $this->rowFactory->__invoke($row);
	}

}