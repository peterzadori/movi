<?php

namespace movi\Media\Storages;

use movi\Media\File;
use movi\Media\IFile;
use movi\Media\IMediaStorage;
use movi\Media\Media;
use Nette\Http\FileUpload;
use Nette\Http\Request;
use Nette\Object;
use Nette\Utils\Finder;
use Nette\Utils\Strings;

class FileStorage extends Object implements IMediaStorage
{

	/** @var string */
	protected $dir;

	/** @var string */
	protected $absolutePath;

	/** @var Media */
	protected $media;

	/** @var string */
	protected $namespace;


	public function __construct($dir)
	{
		$this->dir = $dir;
	}


	/**
	 * @param $filename
	 * @return File|null
	 */
	public function load($filename)
	{
		$file = $this->getAbsolutePath() . '/' . $filename;

		if (!file_exists($file)) {
			return NULL;
		} else {
			return new File($filename, $file);
		}
	}


	/**
	 * @param FileUpload $file
	 * @return string
	 */
	public function save(FileUpload $file)
	{
		do {
			$filename = Strings::random(5) . '-' . $file->getSanitizedName();

			$targetFile = $this->getAbsolutePath() . '/' . $filename;
		} while (file_exists($targetFile));

		$image = $file->toImage();
		$image->save($targetFile);

		return $filename;
	}


	/**
	 * @param IFile $file
	 * @return bool
	 */
	public function delete(IFile $file)
	{
		return @unlink($file->getAbsolutePath());
	}


	public function flush()
	{
		/** @var $file \SplFileInfo */
		foreach (Finder::findFiles('*')->in($this->getAbsolutePath()) as $file)
		{
			@unlink($file->getRealPath());
		}
	}


	/**
	 * @param Media $media
	 * @return $this
	 */
	public function setMedia(Media $media)
	{
		$this->media = $media;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getBaseUrl()
	{
		$path = $this->media->baseUrl  . '/' . $this->dir;

		if ($this->namespace !== NULL) {
			$path .= '/' . $this->namespace;
		}

		return $path;
	}


	/**
	 * @param $dir
	 */
	public function setStorageDir($dir)
	{
		$this->absolutePath = $dir . '/' . $this->dir;

		if (!file_exists($this->absolutePath)) {
			umask(0000);
			@mkdir($this->absolutePath, 0777);
		}
	}


	/**
	 * @param $namespace
	 * @return $this
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;

		$path = $this->getAbsolutePath();

		if (!file_exists($path)) {
			umask(0000);
			@mkdir($path, 0777);
		}

		return $this;
	}


	/**
	 * @return string
	 */
	public function getAbsolutePath()
	{
		$path = $this->absolutePath;

		if ($this->namespace !== NULL) {
			$path .= '/' . $this->namespace;
		}

		return $path;
	}

}