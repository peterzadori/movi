<?php

namespace movi\Templating\Helpers;

use Nette\Image;
use movi\Templating\IHelper;

final class ThumbnailHelper implements IHelper
{

	/** @var string */
	private $wwwDir;


	public function __construct($wwwDir)
	{
		$this->wwwDir = $wwwDir;
	}


	/**
	 * @param $file
	 * @param $width
	 * @param null $height
	 * @return null|string
	 */
	public function process($file, $width, $height = NULL)
	{
		// Set current folder
		chdir($this->wwwDir);

		$imageDir = dirname($file);

		if (!file_exists($file) || !is_file($file)) {
			return NULL;
		}

		if (!file_exists($imageDir) || !is_dir($imageDir)) {
			return NULL;
		}

		if (!is_writeable($imageDir)) {
			chmod($imageDir, 0777);
		}

		// Create thumbnail's name
		$name = $this->createThumbnailName($file, $width, $height);
		$thumb = sprintf('%s/%s', $imageDir, $name);

		if (!file_exists($thumb)) {
			// Create thumbnail
			$image = Image::fromFile($file);

			if (empty($height)) {
				$height = $width;
			}

			$image->resize($width, $height, $image::EXACT);
			$image->save($thumb);
		}

		return sprintf('%s/%s', $imageDir, $name);
	}


	/**
	 * @param $image
	 * @param $width
	 * @param $height
	 * @return string
	 */
	public function createThumbnailName($image, $width, $height = NULL)
	{
		$name = array();

		$image = basename($image);
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