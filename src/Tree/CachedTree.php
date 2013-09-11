<?php

namespace movi\Tree;

use movi\Caching\CacheProvider;
use movi\Localization\Language;
use movi\Model\TreeRepository;
use Nette\Caching\Cache;

class CachedTree extends Tree
{

	/** @var \movi\Localization\Language */
	private $language;

	/** @var \Nette\Caching\Cache */
	private $cache;


	public function __construct(Language $language, CacheProvider $cacheProvider)
	{
		$this->language = $language;

		$name = explode('\\', get_called_class());
		$name = end($name);

		$this->cache = $cacheProvider->create('movi.tree.' . $name);
	}


	/**
	 * @param TreeRepository $repository
	 * @return $this|void
	 */
	public function setRepository(TreeRepository $repository)
	{
		parent::setRepository($repository);

		$repository->onAfterPersist[] = function() {
			$this->cleanCache();
		};

		$repository->onAfterPersist[] = function() {
			$this->cleanCache();
		};

		$this->onBuild[] = function() {
			if ($this->cache->load($this->language->getCurrent()->id) === NULL) {
				$this->buildUrls();
			}
		};

		return $this;
	}


	private function buildUrls()
	{
		foreach ($this->nodes as $node)
		{
			$url = parent::getPath($node);

			$node->url = $url;
			$node->language = $this->language->getCurrent();

			$this->repository->persist($node);
		}

		$this->cache->save($this->language->getCurrent()->id, true, [
			Cache::TAGS => [get_called_class()]
		]);
	}


	/**
	 * @param $url
	 * @return mixed|TreeEntity
	 */
	public function getNode($url)
	{
		foreach ($this->nodes as $node)
		{
			if ($node->url === $url) {
				return $node;
			}
		}

		return parent::getNode($url);
	}


	/**
	 * @param $node
	 * @return array
	 */
	public function getPath($node)
	{
		return $node->url;
	}


	private function cleanCache()
	{
		$this->cache->clean([
			Cache::TAGS => get_called_class()
		]);
	}

}