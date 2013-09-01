<?php

namespace movi\Forms;


use movi\Application\UI\Form;

interface IFormFactory
{

	/**
	 * @return Form
	 */
	public function createForm();

	public function configure(Form $form);

}