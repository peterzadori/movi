<?php

namespace movi\Components\Assets;

use Nette\Object;
use Nette\Utils\Finder;
use Nette\Utils\Strings;
use movi\Caching\CacheProvider;
use movi\Diagnostics\Stopwatch;

class AssetsManager extends Object
{

	/** @var string */
	private $resourcesDir;

	/** @var array */
	private $supported = array('css', 'js');

	/** @var array */
	private $dirs = array();

	/** @var array(\SplFileInfo => relative_path) */
	private $files = array();

	/** @var array */
	private $ignored = array();

	/** @var array */
	private $css = array();

	/** @var array */
	private $js = array();

	/** @var \Nette\Caching\Cache */
	private $cache;


	public function __construct($resourcesDir, CacheProvider $cacheProvider)
	{
		$this->resourcesDir = $resourcesDir;
		$this->cache = $cacheProvider->create('movi.assets');
	}


	/**
	 * @param $dir
	 */
	public function addDir($dir)
	{
		if (file_exists($dir)) {
			$this->dirs[] = $dir;
		}
	}


	/**
	 * @param $file
	 */
	public function addFile($file)
	{
		if (file_exists($file)) {
			$this->files[] = $file;
		}
	}


	/**
	 * @param $file
	 */
	public function ignoreFile($file)
	{
		$this->ignored[] = $file;
	}


	/**
	 * @param $dir
	 */
	public function ignoreDir($dir)
	{
		$this->ignored[] = $dir;
	}


	public function build()
	{
		$hash = md5(serialize($this->dirs)) . md5(serialize($this->files));

		if ($this->cache->load('hash') !== $hash) {
			$files = array();
			$css = array();
			$js = array();

			// Find files in dirs
			foreach ($this->dirs as $dir)
			{
				$dir = $this->fixPath($dir);

				if (in_array($dir, $this->ignored)) {
					continue;
				}

				foreach(Finder::find('*')->from($dir) as $file)
				{
					if ($file->isDir()) {
						continue;
					}

					$files[] = $file->getRealPath();
				}
			}

			// Merge custom added files and found files
			$files = array_merge($files, $this->files);

			foreach ($files as $file)
			{
				$file = $this->fixPath($file);

				if (in_array($file, $this->ignored)) {
					continue;
				}

				$file = new \SplFileInfo($file);

				if (!in_array($file->getExtension(), $this->supported)) {
					continue;
				}

				$path = Strings::substring($file->getRealPath(), strlen($this->resourcesDir));
				$path = Strings::replace($path, '#\\\#', '/');

				switch ($file->getExtension())
				{
					case 'css':
						$css[] = $path;
						break;

					case 'js':
						$js[] = $path;
						break;
				}
			}

			$this->cache->save('hash', $hash);
			$this->cache->save('files', array($css, $js));
		}

		list($this->css, $this->js) = $this->cache->load('files');
	}


	public function rebuild()
	{
		$this->cache->remove('hash');
		$this->cache->remove('files');

		$this->build();
	}


	/**
	 * @return array
	 */
	public function getCss()
	{
		return $this->css;
	}


	/**
	 * @return array
	 */
	public function getJs()
	{
		return $this->js;
	}


	/**
	 * @param $path
	 * @return string
	 */
	private function fixPath($path)
	{
		$path = Strings::replace($path, '#\\\#', '/');
		$path = Strings::replace($path, '#//#', '/');

		return $path;
	}

}