<?php

namespace movi\Localization;

use Kdyby\Events\Subscriber;
use movi\Caching\CacheProvider;
use movi\Model\Entities\Language;
use Nette\Caching\Cache;
use Nette\Object;
use Nette\Utils\Neon;

class Translations extends Object implements Subscriber
{

	/** @var string */
	private $localeDir;

	/** @var \movi\Model\Entities\Language */
	private $language;

	/** @var \Nette\Caching\Cache */
	private $cache;

	/** @var array */
	private $translations;


	public function __construct($localeDir, Language $language, CacheProvider $cacheProvider)
	{
		$this->localeDir = $localeDir;
		$this->language = $language;

		$this->cache = $cacheProvider->create('movi.translations');
	}


	public function getSubscribedEvents()
	{
		return ['movi\Localization\Language::onSet'];
	}


	/**
	 * @param Language $language
	 */
	public function onSet(Language $language)
	{
		if ($this->cache->load($language->id) === NULL) {
			$file = sprintf('%s/%s.neon', $this->localeDir, $language->code);
			$translations = [];

			if (file_exists($file) && is_readable($file)) {
				$translations = Neon::decode(file_get_contents($file));

				$this->process($translations);
			}

			$this->cache->save($language->id, $translations, [
				Cache::FILES => $file
			]);
		}

		$this->translations = $this->cache->load($language->id);
	}


	/**
	 * @param $message
	 * @return null|string
	 */
	public function getTranslation($message)
	{
		if (array_key_exists($message, $this->translations)) {
			return $this->translations[$message];
		} else {
			return $message;
		}
	}


	/**
	 * @param array $translations
	 */
	private function process(array &$translations = NULL)
	{
		if ($translations !== NULL) {
			$translations = $this->flatten($translations);
		} else {
			$translations = [];
		}
	}


	/**
	 * @param $array
	 * @param string $index
	 * @param array $return
	 * @return array
	 */
	private function flatten($array, $index = '', &$return = [])
	{
		foreach ($array as $key => $value)
		{
			if (is_array($value)) {
				$this->flatten($value, $index . $key . '-', $return);
			} else {
				$return[$index . $key] = $value;
			}
		}

		return $return;
	}

} 