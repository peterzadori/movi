<?php

namespace movi\Model\SqlBuilder;

class Limit
{

	/** @var integer */
	public $offset;

	/** @var integer */
	public $limit;


	public function __construct($offset = NULL, $limit = NULL)
	{
		$this->offset = $offset;
		$this->limit = $limit;
	}

}