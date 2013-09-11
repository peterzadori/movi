<?php

namespace movi\Templating\Macros;

use Nette\Latte\CompileException;
use Nette\Latte\Compiler;
use Nette\Latte\MacroNode;
use Nette\Latte\Macros\MacroSet;
use Nette\Latte\PhpWriter;
use Nette\Utils\Strings;

class moviMacros extends MacroSet
{

	public static function install(Compiler $compiler)
	{
		$set = new static($compiler);
		$set->addMacro('widget', [$set, 'macroWidget']);
		$set->addMacro('icon', "echo Nette\\Utils\\Html::el('i')->class(implode('-', ['icon', %node.word])); ");
	}


	public function macroWidget(MacroNode $node, PhpWriter $writer)
	{
		$pair = $node->tokenizer->fetchWord();
		if ($pair === FALSE) {
			throw new CompileException("Missing widget name in {widget}");
		}
		$pair = explode(':', $pair, 2);
		$name = $writer->formatWord($pair[0]);
		$method = isset($pair[1]) ? ucfirst($pair[1]) : '';
		$method = Strings::match($method, '#^\w*\z#') ? "render$method" : "{\"render$method\"}";
		$param = $writer->formatArray();
		if (!Strings::contains($node->args, '=>')) {
			$param = substr($param, 6, -1); // removes array()
		}
		return ($name[0] === '$' ? "if (is_object($name)) \$_ctrl = $name; else " : '')
		. '$_ctrl = $presenter->getComponent("widgets")->getComponent(' . $name . '); '
		. 'if ($_ctrl instanceof Nette\Application\UI\IRenderable) $_ctrl->validateControl(); '
		. "\$_ctrl->$method($param)";
	}

}