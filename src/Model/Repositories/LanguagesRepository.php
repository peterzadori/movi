<?php

namespace movi\Model\Repositories;

use movi\InvalidArgumentException;
use Nette\Http\Request;
use Nette\Utils\Strings;
use movi\Model\Entities\Language;

/**
 * Class LanguagesRepository
 * @package movi\Model\Repositories
 * @entity \movi\Model\Entities\Language
 * @table languages
 */
final class LanguagesRepository extends Repository
{

	/** @var string  */
	private $suffix = 'translations';

	/** @var \Nette\Http\Request  */
	private $request;

	public $detect;


	/**
	 * @param Request $request
	 */
	public function setRequest(Request $request)
	{
		$this->request = $request;
	}


	public function beforePersist(array $values)
	{
		$values['code'] = Strings::lower($values['code']);

		if ($values['default'] == true && $values['active'] == false) {
			throw new InvalidArgumentException('Jazyk nemôže byť predvolený, ak nie je aktívny.');
		}

		if ($values['default'] == true) {
			$this->connection->update('languages', array('default' => false))->execute();
		}

		return $values;
	}


	/**
	 * @param bool
	 * @return Language
	 */
	public function getDefaultLanguage()
	{
		if ($this->detect === true) {
			$language = $this->detectLanguage();

			if ($language) return $this->findBy(array('[code] = %s' => $language))->fetch();
		}

		return $this->findBy(array('[default] = %i' => true));
	}
	

	/**
	 * @return string
	 */

	private function detectLanguage()
	{
		$languages = $this->getStatement()->where('[active] = %i', true)->fetchPairs('id', 'code');
		$language = $this->request->detectLanguage(explode(',', implode(',', $languages)));

		if ($language == NULL) {
			return array_slice($languages, 0, 1);
		} else {
			return $language;
		}
	}

}