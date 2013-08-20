<?php

namespace movi\Packages;

use Nette\Config\Compiler;
use Nette\Config\Configurator;
use Nette\DI\Container;
use movi\Model\Mapper;

abstract class Package
{

	/** @var string */
	public $name;

	/** @var string */
	public $title;

	/** @var array */
	public $require;

	/** @var array */
	public $config;

	/** @var array */
	public $resources;

	/** @var array */
	public $sql;

	/** @var string */
	public $dir;

	public $extensions;


	public function __construct($data)
	{
		$this->name = $data['name'];
		$this->title = $data['title'];
		$this->require = $data['require'];
		$this->config = $data['config'];
		$this->resources = $data['resources'];
		$this->sql = $data['sql'];
		$this->dir = $data['dir'];
		$this->extensions = $data['extensions'];
	}


	/**
	 * @param Compiler $compiler
	 */
	public function compile(Compiler $compiler)
	{

	}


	/**
	 * Check if package's installation is valid
	 */
	public abstract  function isValid(Container $container);


	/**
	 * @param Container $container
	 */
	public function install(Container $container)
	{

	}

}