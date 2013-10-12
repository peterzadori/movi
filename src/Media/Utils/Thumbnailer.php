<?php

namespace movi\Media\Utils;

use movi\Media\Media;
use Nette\ArrayHash;
use Nette\Image;

final class Thumbnailer
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


	public function createThumbnail()
	{
		$args = array_merge($this->defaults, func_get_args()[0]);
		$args = ArrayHash::from($args);

		$storage = $this->media->getStorage($args->storage);

		if (isset($args->namespace)) {
			$storage->setNamespace($args->namespace);
		}

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

			return $src;
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

}