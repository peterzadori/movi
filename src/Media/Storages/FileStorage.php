<?php

namespace movi\Media\Storages;

use movi\Media\File;
use movi\Media\IMediaStorage;
use movi\Media\Media;
use Nette\Http\FileUpload;
use Nette\Http\Request;
use Nette\Object;

class FileStorage extends Object implements IMediaStorage
{

	/** @var string */
	protected $dir;

	/** @var string */
	protected $absolutePath;

	/** @var Media */
	protected $media;


	public function __construct($dir)
	{
		$this->dir = $dir;
	}


	public function load($filename)
	{
		$file = $this->absolutePath . '/' . $filename;

		if (!file_exists($file)) {
			return NULL;
		} else {
			return new File($filename, $file);
		}
	}


	public function setMedia(Media $media)
	{
		$this->media = $media;
	}


	public function getBaseUrl()
	{
		return $this->media->baseUrl . '/' . $this->dir;
	}


	public function setStorageDir($dir)
	{
		$this->absolutePath = $dir . '/' . $this->dir;

		if (!file_exists($this->absolutePath)) {
			umask(0000);
			@mkdir($this->absolutePath, 0777);
		}
	}


	public function save(FileUpload $file)
	{

	}


	public function getAbsolutePath()
	{
		return $this->absolutePath;
	}

}