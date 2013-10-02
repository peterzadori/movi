<?php

namespace movi\Templating\Macros;

use movi\Media\Media;
use Nette\ArrayHash;
use Nette\Image;
use Nette\Latte\Compiler;
use Nette\Latte\Engine;
use Nette\Latte\MacroNode;
use Nette\Latte\Macros\MacroSet;
use Nette\Utils\Html;

class MediaMacros
{

	/** @var \movi\Media\Media */
	private $media;

	private $defaults = [
		'storage' => NULL,
		'file' => NULL,
		'width' => NULL,
		'height' => NULL,
		'flag' => 'EXACT'
	];


	public function __construct(Media $media)
	{
		$this->media = $media;
	}


	public function install(Engine $engine)
	{
		$compiler = $engine->getCompiler();

		$macroset = new MacroSet($compiler);
		$macroset->addMacro('thumb', [$this, 'macroThumbnail']);
		$macroset->addMacro('file', [$this, 'macroFile']);
	}


	public function macroThumbnail(MacroNode $node)
	{
		$args = $this->parseToken($node->tokenizer->fetchAll());

		$storage = $this->media->getStorage($args->storage);
		$image = $storage->load($args->file);
		$width = $args->width;
		$height = $args->height;

		if ($image !== NULL) {
			if ($width && $width != $image->width) {
				$name = $this->createThumbnailName($image, $width, $height);
				$thumb = $storage->absolutePath . '/' . $name;
				$src = NULL;

				if (!file_exists($thumb)) {
					$image = Image::fromFile($image->absolutePath);

					if (empty($height)) {
						$height = $width;
					}

					$image->resize($width, $height, constant('Nette\Image::' . strtoupper($args->flag)));
					$image->save($thumb);
				}

				$image = $storage->load($name);
			}

			$src = $storage->getBaseUrl() . '/' . $image->filename;

			$el = Html::el('img');
			$el->src = $src;

			return "echo '$el';";
		}
	}


	public function macroFile(MacroNode $node)
	{
		$args = $this->parseToken($node->tokenizer->fetchAll());

		$storage = $this->media->getStorage($args->storage);
		$file = $storage->load($args->file);

		if ($file !== NULL) {
			$link = $storage->getBaseUrl() . '/' . $file->filename;

			return "echo '$link';";
		}
	}


	public function createThumbnailName($image, $width, $height = NULL)
	{
		$name = [];

		$image = $image->filename;
		$image = explode('.', $image);
		$extension = array_pop($image);

		$image = implode('.', $image);

		$name[] = $image;
		$name[] = $width;

		if (!empty($height) && $height > 0) {
			$name[] = $height;
		}

		return sprintf('%s.%s', implode('-', $name), $extension);
	}


	/**
	 * @param $token
	 * @return ArrayHash
	 */
	private function parseToken($token)
	{
		$token = str_replace(' ', '', $token);
		$args = [];

		foreach (explode(',', $token) as $part)
		{
			list($key, $value) = explode('=>', $part);
			$args[$key] = $value;
		}

		$args = array_merge($this->defaults, $args);
		$args = ArrayHash::from($args);

		return $args;
	}

}