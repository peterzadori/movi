<?php

namespace movi\Model;

class Fluent extends \LeanMapper\Fluent
{

	/**
	 * @return int
	 */
	public function count()
	{
		$fluent = clone $this;

		$fluent->removeClause('SELECT');
		$fluent->select('COUNT(*)');

		return $fluent->fetchSingle();
	}

}