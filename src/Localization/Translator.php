<?php

namespace movi\Localization;

use Kdyby\Events\Subscriber;
use Nette\Caching\Cache;
use Nette\Localization\ITranslator;
use Nette\Utils\Neon;
use movi\Caching\CacheProvider;
use movi\Model\Entities\Language as LanguageEntity;

final class Translator implements ITranslator, Subscriber
{

	const IN = 'in',
		OUT = 'out',
		PRESENTERS = 'presenters';

	/** @var string */
	private $localDir;

	/** @var \movi\Localization\Language */
	private $language;

	/** @var array */
	private $translations;

	/** @var \Nette\Caching\Cache */
	private $cache;


	public function __construct($localDir, Language $language, CacheProvider $cacheProvider)
	{
		$this->localDir = $localDir;
		$this->language = $language;
		$this->cache = $cacheProvider->create('movi.translations');
	}


	public function getSubscribedEvents()
	{
		return ['movi\Localization\Language::onSet'];
	}


	/**
	 * @param $language
	 */
	public function onSet(LanguageEntity $language)
	{
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
			if (array_key_exists($message, $this->translations)) {
				return $this->translations[$message];
			}
		}

		return $message;
	}


	/**
	 * @param $presenter
	 * @param string $way
	 * @return string
	 */
	public function translatePresenter($presenter, $way = 'out')
	{
		if ($this->language->isLanguageSet() && isset($this->translations[self::PRESENTERS])) {
			$presenters = $this->translations[self::PRESENTERS];
			$presenter = strtolower($presenter);

			switch ($way)
			{
				case self::IN:
					$presenters = array_flip($presenters);

					if (isset($presenters[$presenter])) {
						$presenter = $presenters[$presenter];
					}
					break;

				case self::OUT:
					if (isset($presenters[$presenter])) {
						$presenter = $presenters[$presenter];
					}
					break;
			}
		} else {
			$presenter = strtolower($presenter);
		}

		return $presenter;
	}


	/**
	 * @param array $translations
	 */
	private function process(array &$translations = NULL)
	{
		if ($translations !== NULL) {
			$presenters = [];

			if (isset($translations[self::PRESENTERS])) {
				$presenters = $translations[self::PRESENTERS];
				unset($translations[self::PRESENTERS]);
			}

			$translations = $this->flatten($translations);
			$translations[self::PRESENTERS] = $presenters;
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