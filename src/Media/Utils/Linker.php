<?php

namespace movi\Media\Utils;

use movi\Media\Media;
use Nette\ArrayHash;
use Nette\Image;

final class Linker
{

	/** @var \movi\Media\Media */
	private $media;

	private $defaults = [
		'storage' => NULL,
		'file' => NULL
	];

	public function __construct(Media $media)
	{
		$this->media = $media;
	}


	public function createLink()
	{
		$args = array_merge($this->defaults, func_get_args()[0]);
		$args = ArrayHash::from($args);

		$storage = $this->media->getStorage($args->storage);

		if (isset($args->namespace)) {
			$storage->setNamespace($args->namespace);
		}

		$image = $storage->load($args->file);

		if ($image !== NULL) {
			return $storage->getBaseUrl() . '/' . $image->filename;
		}
	}

}