<?php

namespace movi\Localization;

use Kdyby\Events\Subscriber;
use Nette\Caching\Cache;
use Nette\Localization\ITranslator;
use Nette\Utils\Neon;
use movi\Caching\CacheProvider;
use movi\Model\Entities\Language as LanguageEntity;

final class Translator implements ITranslator
{

	/** @var string */
	private $localDir;

	/** @var \movi\Localization\Language */
	private $language;

	/** @var array */
	private $translations;

	/** @var \Nette\Caching\Cache */
	private $cache;

	/** @var bool */
	private $loaded = false;


	public function __construct($localDir, Language $language, CacheProvider $cacheProvider)
	{
		$this->localDir = $localDir;
		$this->language = $language;

		$this->cache = $cacheProvider->create('movi.translations');
	}


	private function loadTranslations()
	{
		$language = $this->language->getLanguage();

		if ($this->cache->load($language->id) === NULL) {
			$file = sprintf('%s/%s.neon', $this->localDir, $language->code);
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
	 * @param null $count
	 * @return string
	 */
	public function translate($message, $count = NULL)
	{
		// Language must be set
		if ($this->language->isLanguageSet()) {
			if (!$this->loaded) {
				$this->loadTranslations();

				$this->loaded = true;
			}

			if (array_key_exists($message, $this->translations)) {
				return $this->translations[$message];
			}
		}

		return $message;
	}


	/**
	 * @param array $translations
	 */
	private function process(array &$translations = NULL)
	{
		if ($translations !== NULL) {
			$translations = $this->flatten($translations);
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