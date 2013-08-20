<?php

namespace movi\Security;

use Nette;

class Identity extends Nette\Security\Identity implements \Serializable
{

	/** @var integer */
	private $id;

	/** @var array */
	private $roles;

	/** @var string */
	private $token;

	/** @var array */
	private $data;

	/** @var bool */
	private $loaded = false;


	public function __construct($id, $roles = NULL, $token)
	{
		$this->id = $id;
		$this->roles = $roles;
		$this->token = $token;
	}


	public function serialize()
	{
		return serialize(array($this->id, $this->roles, $this->token));
	}


	public function unserialize($serialized)
	{
		$this->loaded = false;

		list($this->id, $this->roles, $this->token) = unserialize($serialized);
	}


	/**
	 * @return null|string
	 */
	public function getToken()
	{
		return $this->token;
	}


	public function getId()
	{
		return $this->id;
	}


	public function getRoles()
	{
		return (array) $this->roles;
	}


	/**
	 * @param $key
	 * @return mixed
	 */
	public function &__get($key)
	{
		$value = $this->data->__get($key);

		return $value;
	}


	/**
	 * @param $data
	 */
	public function setData($data)
	{
		$this->data = $data;
	}


	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}


	public function setLoaded()
	{
		$this->loaded = true;
	}


	/**
	 * @return bool
	 */
	public function isLoaded()
	{
		return $this->loaded;
	}

}