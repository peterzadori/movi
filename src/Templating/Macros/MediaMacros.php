<?php

namespace movi\Templating\Macros;

use Nette\Image;
use Nette\Latte\Compiler;
use Nette\Latte\MacroNode;
use Nette\Latte\Macros\MacroSet;
use Nette\Latte\PhpWriter;

class MediaMacros extends MacroSet
{

	public static function install(Compiler $compiler)
	{
		$macroset = new static($compiler);
		$macroset->addMacro('image', [$macroset, 'macroImage']);
		$macroset->addMacro('file', [$macroset, 'macroFile']);
	}


	public static function macroImage(MacroNode $node, PhpWriter $writer)
	{
		$str = '$thumbFile = $presenter->context->thumbnailer->createThumbnail(%node.array);'
			. 'echo "<img src=\"$thumbFile\">"';

		return $writer->write($str);
	}


	public static function macroFile(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write('echo $presenter->context->linker->createLink(%node.array);');
	}

}