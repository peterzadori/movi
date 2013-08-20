<?php

namespace movi\Caching;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;

class CacheProvider
{

	/** @var IStorage */
	private $storage;


	public function __construct(IStorage $storage)
	{
		$this->storage = $storage;
	}


	/**
	 * @param null $namespace
	 * @return Cache
	 */
	public function create($namespace = NULL)
	{
		return new Cache($this->storage, $namespace);
	}


}