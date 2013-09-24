<?php

namespace movi\Model;

class Fluent extends \LeanMapper\Fluent
{

	/**
	 * @return int
	 */
	public function count()
	{
		$this->removeClause('SELECT');
		$this->select('COUNT(*)');

		return $this->fetchSingle();
	}

}