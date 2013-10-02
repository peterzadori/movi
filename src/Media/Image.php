<?php

namespace movi\Media;

class Image extends File
{

	/** @var integer */
	private $width;

	/** @var integer */
	private $height;


	public function __construct($filename, $absolutePath)
	{
		parent::__construct($filename, $absolutePath);

		list($this->width, $this->height) = getimagesize($absolutePath);
	}


	/**
	 * @return int
	 */
	public function getWidth()
	{
		return $this->width;
	}


	/**
	 * @return int
	 */
	public function getHeight()
	{
		return $this->height;
	}

}