<?php

namespace movi\Packages;

use Nette\Utils\Arrays;
use Nette\Utils\Json;
use movi\FileNotFoundException;
use movi\InvalidArgumentException;

final class Loader
{

	const PACKAGE_FILE = 'package.json';


	private $defaults = [
		'config' => [],
		'resources' => [],
		'extensions' => [],
		'schemas' => []
	];


	public function getPackage(\SplFileInfo $package)
	{
		return $this->createPackage($package);
	}


	/**
	 * @param \SplFileInfo $package
	 * @return mixed
	 * @throws \movi\InvalidArgumentException
	 */
	private function createPackage(\SplFileInfo $package)
	{
		$data = $this->getData($package);

		if (!isset($data['name'])) {
			throw new InvalidArgumentException("Unknown package name.");
		}

		return $data;
	}


	/**
	 * @param \SplFileInfo $package
	 * @return array
	 * @throws \movi\FileNotFoundException
	 */
	private function getData(\SplFileInfo $package)
	{
		$file = $package->getPathname() . '/'. self::PACKAGE_FILE;

		if (!file_exists($file) || !is_readable($file)) {
			throw new FileNotFoundException("JSON file for package '" . $package->getFilename() . "' was not found or is not readable.");
		}

		$data = Json::decode(file_get_contents($file), Json::FORCE_ARRAY);
		$data['dir'] = $package->getPathname();

		return Arrays::mergeTree($data, $this->defaults);
	}

}