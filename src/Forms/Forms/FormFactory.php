<?php

namespace movi\Forms;

use Nette\Object;
use movi\Application\UI\Form;

abstract class FormFactory extends Object implements IFormFactory
{

    /**
     * @return Form
     */
    public function createForm()
    {
        $form = new Form();

        $this->configure($form);
        $this->loadValues($form);
        $this->attachHandlers($form);

        return $form;
    }


    protected function loadValues(Form $form)
    {

    }


    protected function attachHandlers(Form $form)
    {
        if (method_exists($this, 'validateForm')) {
            $form->onValidate[] = $this->validateForm;
        }

        if (method_exists($this, 'processForm')) {
            $form->onSuccess[] = $this->processForm;
        }

        if (method_exists($this, 'processErrors')) {
            $form->onError[] = $this->processErrors;
        }
    }

}