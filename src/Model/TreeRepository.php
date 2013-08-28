<?php

namespace movi\Model;


/**
 * Class TreeRepository
 * @package movi\Model
 */
abstract class TreeRepository extends TranslationsRepository
{

	public function getStatement()
	{
		$statament = parent::getStatement();
		$statament->orderBy('[order]', 'ASC');

		return $statament;
	}

}