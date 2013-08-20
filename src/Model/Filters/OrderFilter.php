<?php

namespace movi\Model\Filters;

use LeanMapper\Fluent;

class OrderFilter
{

	public function modify(Fluent $statement)
	{
		$statement->orderBy('[order]', 'ASC');
	}

}