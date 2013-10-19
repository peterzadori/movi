<?php

namespace movi\Tree;

use movi\Caching\CacheProvider;
use movi\Localization\Language;
use movi\Model\Repository;
use movi\Model\TreeRepository;
use Nette\Caching\Cache;

class CachedTree extends Tree
{

	/** @var \movi\Localization\Language */
	private $language;

	/** @var \Nette\Caching\Cache */
	private $cache;

	private $urls = [];


	public function __construct(Language $language, CacheProvider $cacheProvider)
	{
		$this->language = $language;

		$name = explode('\\', get_called_class());
		$name = end($name);

		$this->cache = $cacheProvider->create('movi.tree.' . $name);
	}


	/**
	 * @param Repository $repository
	 * @return $this
	 */
	public function setRepository(Repository $repository)
	{
		parent::setRepository($repository);

		$repository->onAfterPersist[] = function() {
			$this->cleanCache();
		};

		$repository->onAfterDelete[] = function() {
			$this->cleanCache();
		};

		$this->onBuild[] = function() {
			if ($this->cache->load($this->language->getCurrent()->id) === NULL) {
				$this->buildUrls();
			}
		};

		return $this;
	}


	public function build(array $nodes = NULL)
	{
		if (!$this->built) {
			$key = sprintf('nodes-%d', $this->language->getCurrent()->id);

			if ($this->cache->load($key) === NULL) {
				$rows = $this->repository->findAll();
				$nodes = [];
				$parents = [];
				$children = [];

				foreach ($rows as $node)
				{
					$this->onFetch($node);

					$nodes[$node->id] = json_encode($node);

					if ($node->parent === NULL) {
						$parents[$node->order] = $node->id;
					} else {
						$children[$node->parent->id][$node->order] = $node->id;
					}
				}

				$this->cache->save($key, [$nodes, $parents, $children], [
					Cache::TAGS => [get_called_class()]
				]);
			}

			list($nodes, $this->parents, $this->children) = $this->cache->load($key);

			foreach($nodes as $node)
			{
				$node = $this->repository->createUnserializedEntity($node);
				$this->nodes[$node->id] = $node;
				$this->urls[$node->url] = $node->id;

				if ($node->root) {
					$this->root = $node;
				}
			}

			$this->onBuild();

			$this->built = true;
		}
	}


	private function buildUrls()
	{
		foreach ($this->nodes as $node)
		{
			$url = parent::getPath($node);

			if ($node->url != $url) {
				$node->url = $url;
				$node->language = $this->language->getCurrent();

				$this->repository->persist($node);
			}
		}

		$this->cache->save($this->language->getCurrent()->id, true, [
			Cache::TAGS => [get_called_class()]
		]);
	}


	/**
	 * @param $url
	 * @return mixed
	 */
	public function getNode($url)
	{
		if (array_key_exists($url, $this->urls)) {
			$url = $this->urls[$url];
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