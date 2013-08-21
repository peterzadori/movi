<?php

namespace movi\Model\Repositories;

use Nette\Utils\Strings;

/**
 * Class LanguagesRepository
 * @package movi\Model\Repositories
 * @entity \movi\Model\Entities\Language
 * @table languages
 */
final class LanguagesRepository extends Repository
{

	/**
	 * @return array
	 */
	public function findActive()
	{
		return $this->findAll(array('[active] = %i' => true));
	}


	/*
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
	*/

}