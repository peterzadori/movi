<?php

namespace movi\Forms\Controls;

use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;
use movi\InvalidArgumentException;
use movi\Model\IdentifiedEntity;
use movi\Model\Repository;

final class HasOneControl extends BaseControl
{

    /** @var Repository */
    private $repository;

    /** @var string */
    private $column;

    /** @var array */
    private $items;

    /** @var IdentifiedEntity */
    private $item;

    /** @var NULL|string */
    private $prompt;


    public function __construct($label = NULL, $column = NULL, array $items = NULL)
    {
        parent::__construct($label);

        if ($column === NULL) {
            $column = 'name';
        }

        $this->column = $column;

        if (!empty($items)) {
            $this->setItems($items);
        }

        $this->control->setName('select');
    }


    /**
     * @param Repository $repository
     * @return $this
     */
    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;

        $this->setItems($repository->findAll());

        return $this;
    }


    /**
     * @param $value
     * @return $this|bool|BaseControl
     */
    public function setValue($value)
    {
        parent::setValue($value);

        if ($value instanceof IdentifiedEntity) {
            if (isset($this->items[$value->id])) {
                $this->item = $this->items[$value->id];
            }
        } else {
            if (isset($this->items[$this->value])) {
                $this->item = $this->items[$this->value];
            }
        }

        return $this;
    }


    /**
     * @return mixed|IdentifiedEntity
     */
    public function getValue()
    {
        return $this->item;
    }


    /**
     * @param array $items
     * @return $this
     * @throws \movi\InvalidArgumentException
     */
    public function setItems(array $items)
    {
        foreach ($items as $item)
        {
            if (!($item instanceof IdentifiedEntity)) {
                throw new InvalidArgumentException('Entity must be an instance of IdentifiedEntity!');
            }
        }

        $this->items = $items;

        return $this;
    }


    /**
     * @param $prompt
     * @return $this
     */
    public function setPrompt($prompt)
    {
        $this->prompt = $prompt;

        return $this;
    }


    /**
     * @return Html
     */
    public function getControl()
    {
        $selected = $this->getValue();
        $selected = $selected === NULL ? NULL : array($selected->id => TRUE);
        $control = parent::getControl();
        $option = Html::el('option');

        if ($this->prompt !== NULL) {
            $control->add((string) $option->value('')->setText($this->prompt));
        }

        foreach ($this->items as $key => $value)
        {
            $option->value($key)->setText($value->{$this->column})->selected(isset($selected[$key]));

            $control->add((string) $option);
        }

        return $control;
    }

}