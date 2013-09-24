<?php

namespace movi\Model;

class Connection extends \LeanMapper\Connection
{

	/**
	 * @return Fluent
	 */
	public function command()
	{
		return new Fluent($this);
	}

}