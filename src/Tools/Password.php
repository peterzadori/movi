<?php

namespace movi\Tools;

use Nette\Object;

class Password extends Object
{

	/** @var string */
	private $algorithm;

	/** @var string */
	private $salt;


	public function __construct($salt = '$2a$07$', $algorithm = 'sha512')
	{
		$this->salt = $salt;
		$this->algorithm = $algorithm;
	}


	/**
	 * @param $password
	 * @return string
	 */
	public function calculateHash($password)
	{
		return hash($this->algorithm, $password . $this->salt);
	}

}