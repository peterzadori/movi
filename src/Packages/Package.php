<?php

namespace movi\Packages;

use Nette\Config\Compiler;
use Nette\DI\Container;
use Nette\Object;

class Package extends Object
{

	/** @var string */
	public $name;

	/** @var array */
	public $config;

	/** @var array */
	public $resources;

	/** @var string */
	public $dir;

	/** @var array */
	public $extensions;

	/** @var array */
	public $schemas;


	public function __construct($data)
	{
		$this->name = $data['name'];
		$this->config = $data['config'];
		$this->resources = $data['resources'];
		$this->dir = $data['dir'];
		$this->extensions = $data['extensions'];
		$this->schemas = $data['schemas'];
	}


	/**
	 * @param Compiler $compiler
	 */
	public function onCompile(Compiler $compiler)
	{

	}

}