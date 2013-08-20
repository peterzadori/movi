<?php

namespace movi\Packages\Installers;

use movi\Packages\IInstaller;
use movi\Packages\Package;
use Nette\Utils\Finder;

class ResourceInstaller implements IInstaller
{

	/** @var string */
	private $resourcesDir;


	public function __construct($resourcesDir)
	{
		$this->resourcesDir = $resourcesDir;
	}


	public function install(Package $package)
	{
		if (!empty($package->resources)) {
			foreach ($package->resources as $dir)
			{
				$dir = ltrim($dir, '/');
				$dir = $package->dir . '/' . $dir;

				if (!file_exists($dir)) {
					continue;
				}

				$targetDir = $this->resourcesDir . '/' . $package->name;

				$this->copy($dir, $targetDir);
			}
		}
	}


	private function copy($dir, $targetDir)
	{
		$files = array();

		/** @var $file \SplFileInfo */
		foreach (Finder::find('*')->from($dir) as $file)
		{
			if ($file->isFile()) {
				$filename = $this->getRelativePath($file->getPathname(), $dir);

				$files[$filename] = $file;
			}
		}

		foreach ($files as $filename => $file)
		{
			$target = $targetDir . '/' . $filename;
			$dir = (new \SplFileInfo($target))->getPath();

			if (!file_exists($dir)) {
				umask(0000);
				mkdir($dir, 0777, true);
			}

			@copy($file->getPathname(), $target);
		}
	}


	/**
	 * @param $file
	 * @param $resourceDir
	 * @return string
	 */
	private function getRelativePath($file, $resourceDir)
	{
		return substr($file, strlen($resourceDir) + 1);
	}

}