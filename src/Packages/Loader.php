<?php

namespace movi\Packages;

use Nette\Utils\Json;
use movi\FileNotFoundException;
use movi\InvalidArgumentException;

final class Loader
{

	const JSON_FILE = 'package.json';


	/** @var \SplFileInfo */
	private $package;


	public function getPackage(\SplFileInfo $package)
	{
		$this->package = $package;

		return $this->createPackage();
	}


	/**
	 * @return BasePackage|Package
	 * @throws \movi\InvalidArgumentException
	 */
	private function createPackage()
	{
		$data = $this->parseJson();

		if (!isset($data->name)) {
			throw new InvalidArgumentException("Unknown package name.");
		}

		// Parse JSON data
		$data->title = (isset($data->title)) ? $data->title : NULL;
		$data->require = (isset($data->require)) ? $data->require : array();
		$data->config = (isset($data->config)) ? $data->config : array();
		$data->resources = (isset($data->resources)) ? $data->resources : array();
		$data->sql = (isset($data->sql)) ? $data->sql : array();
		$data->dir = $this->package->getPathname();
		$data->extensions = (isset($data->extensions)) ? $data->extensions : array();

		return (array) $data;
	}


	/**
	 * @return mixed
	 * @throws \movi\FileNotFoundException
	 */
	private function parseJson()
	{
		$file = $this->package->getPathname() . '/'. self::JSON_FILE;

		if (!file_exists($file) || !is_readable($file)) {
			throw new FileNotFoundException("JSON file for package '" . $this->package->getFilename() . "' was not found or is not readable.");
		}

		return Json::decode(file_get_contents($file));
	}

}