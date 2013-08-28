<?php

namespace movi\Forms;

use Nette\Forms\Container;
use Nette\Forms\Form;
use Kdyby;
use movi\Forms\Controls\HasOneControl;

final class FormExtension
{

	public static function register()
	{
		Kdyby\Replicator\Container::register();

        Container::extensionMethod('addHasOne', function(Container $container, $name, $label = NULL, $column = NULL, array $items = NULL) {
            $control = $container[$name] = new HasOneControl($label, $column, $items);

            return $control;
        });

		// WYSIWYG
		Container::extensionMethod('addWysiwyg', function(Container $container, $name, $label = NULL, $rows = NULL, $cols = NULL) {
			$control = $container->addTextArea($name, $label, $cols, $rows);
			$control->getControlPrototype()->class('wysiwyg');

			return $control;
		});

		// E-mail
		Container::extensionMethod('addEmail', function(Container $container, $name, $label = NULL, $cols = NULL, $maxLength = NULL) {
			$control = $container->addText($name, $label, $cols, $maxLength);
			$control->setAttribute('type', 'email');
			$control->addCondition(Form::FILLED)
				->addRule(Form::EMAIL);

			return $control;
		});

		// URL
		Container::extensionMethod('addUrl', function(Container $container, $name, $label = NULL, $cols = NULL, $maxLength = NULL) {
			$control = $container->addText($name, $label, $cols, $maxLength);
			$control->setAttribute('type', 'url');
			$control->addCondition(Form::FILLED)
				->addRule(Form::URL);

			return $control;
		});

		// Color
		Container::extensionMethod('addColor', function(Container $container, $name, $label = NULL, $cols = NULL, $maxLength = NULL) {
			$control = $container->addText($name, $label, $cols, $maxLength);
			$control->setAttribute('type', 'color');

			return $control;
		});

		// Number
		Container::extensionMethod('addNumber', function(Container $container, $name, $label = NULL, $step = NULL, $min = NULL, $max = NULL, $cols = NULL, $maxLength = NULL) {
			$control = $container->addText($name, $label, $cols, $maxLength);
			$control->setAttribute('type', 'number');
			$control->setAttribute('step', $step);
			$control->addCondition(Form::FILLED)
				->addRule(Form::NUMERIC);

			$range = array();
			if ($min !== NULL) {
				$control->setAttribute('min', $min);
				$range[0] = $min;
			}

			if ($max !== NULL) {
				$control->setAttribute('max', $max);
				$range[1] = $max;
			}

			if ($range != array(NULL, NULL)) {
				$control->addCondition(Form::FILLED)
					->addRule(Form::RANGE, NULL, $range);
			}

			return $control;
		});
	}

}