<?php

namespace movi\Media;

use movi\InvalidArgumentException;
use Nette\Http\Request;
use Nette\Object;

class Media extends Object
{

	/** @var string */
	private $storageDir;

	/** @var string */
	private $wwwDir;

	/** @var string */
	public $baseUrl;

	/** @var IMediaStorage[] */
	private $storages = [];


	public function __construct($storageDir, $wwwDir, Request $httpRequest)
	{
		$this->storageDir = $storageDir;
		$this->wwwDir = $wwwDir;
		$this->baseUrl = $httpRequest->url->basePath . $storageDir;
	}


	/**
	 * @param $name
	 * @param IMediaStorage $storage
	 * @return $this
	 * @throws \movi\InvalidArgumentException
	 */
	public function addStorage($name, IMediaStorage $storage)
	{
		if (isset($this->storages[$name])) {
			throw new InvalidArgumentException("Storage '$name' is already added.");
		}

		$storage->setMedia($this);
		$storage->setStorageDir($this->wwwDir . '/' . $this->storageDir);

		$this->storages[$name] = $storage;

		return $this;
	}


	/**
	 * @param $name
	 * @return IMediaStorage
	 * @throws \movi\InvalidArgumentException
	 */
	public function getStorage($name)
	{
		if (!isset($this->storages[$name])) {
			throw new InvalidArgumentException("Storage '$name' was not found.");
		}

		return $this->storages[$name];
	}

}