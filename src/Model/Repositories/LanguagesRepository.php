<?php

namespace movi\Model\Repositories;

use Nette\Utils\Strings;
use movi\InvalidArgumentException;
use movi\Model\Entities\Language;
use movi\Model\Repository;

/**
 * Class LanguagesRepository
 * @package movi\Model\Repositories
 * @entity \movi\Model\Entities\Language
 * @table languages
 */
final class LanguagesRepository extends Repository
{

	/** @var string */
	private $localDir;


	protected function initEvents()
	{
		$this->onBeforePersist[] = function(Language $language) {
			if ($language->default === true && $language->active === false) {
				throw new InvalidArgumentException('Jazyk nemôže byť predvolený, ak nie je aktívny.');
			}

			if ($language->default === true) {
				$this->connection->update('languages', array('default' => false))->execute();
			}
		};

		$this->onBeforeCreate[] = function(Language $language) {
			$fp = fopen(sprintf('%s/%s.neon', $this->localDir, $language->code), 'w');
			fclose($fp);
		};

		$this->onAfterDelete[] = function(Language $language) {
			@unlink(sprintf('%s/%s.neon', $this->localDir, $language->code));
		};
	}


	/**
	 * @param $dir
	 * @return $this
	 */
	public function setLocalDir($dir)
	{
		$this->localDir = $dir;

		return $this;
	}


	/**
	 * @return array
	 */
	public function findActive()
	{
		return $this->findAll(array('[active] = %i' => true));
	}

}