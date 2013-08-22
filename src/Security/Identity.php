<?php

namespace movi\Security;

use Nette\Object;
use Nette\Security\IIdentity;
use movi\Model\Entities\User;

class Identity extends Object implements \Serializable, IIdentity
{

	/** @var integer */
	private $id;

	/** @var array */
	private $roles;

	/** @var string */
	private $token;

	/** @var User */
	private $user;

	/** @var bool */
	private $loaded = false;


	public function __construct($id, $roles = NULL, $token)
	{
		$this->id = $id;
		$this->roles = (array) $roles;
		$this->token = $token;
	}


	/**
	 * @param User $user
	 * @return $this
	 */
	public function setUser(User $user)
	{
		$this->user = $user;

		return $this;
	}


	/**
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}


	/**
	 * @return int|mixed
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * @return array
	 */
	public function getRoles()
	{
		return $this->roles;
	}


	/**
	 * @return string
	 */
	public function getToken()
	{
		return $this->token;
	}


	/**
	 * @return string
	 */
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
	 * @param $key
	 * @return mixed
	 */
	public function &__get($key)
	{
		$value = $this->user->{$key};

		return $value;
	}


	/**
	 * @return $this
	 */
	public function setLoaded()
	{
		$this->loaded = true;

		return $this;
	}


	/**
	 * @return bool
	 */
	public function isLoaded()
	{
		return $this->loaded;
	}

}