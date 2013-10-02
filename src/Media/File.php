<?php

namespace movi\Media;

use Nette\Object;

class File extends Object implements IFile
{

	/** @var string */
	protected $filename;

	/** @var string */
	protected $absolutePath;


	public function __construct($filename, $absolutePath)
	{
		$this->filename = $filename;
		$this->absolutePath = $absolutePath;
	}


	public function getFilename()
	{
		return $this->filename;
	}


	public function getAbsolutePath()
	{
		return $this->absolutePath;
	}

}