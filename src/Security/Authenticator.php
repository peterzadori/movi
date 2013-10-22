<?php

namespace movi\Security;

use movi\InvalidStateException;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;

class Authenticator implements IAuthenticator
{

	/** @var IUsers */
	private $users;


	public function __construct(IUsers $users = NULL)
	{
		$this->users = $users;
	}


	/**
	 * @param array $credentials
	 * @return Identity|\Nette\Security\IIdentity
	 * @throws \movi\InvalidStateException
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		if ($this->users === NULL) {
			throw new InvalidStateException('Service IUsers is not registered.');
		}

		$user = $this->users->login($credentials);

		if (!$user) {
			throw new AuthenticationException(self::INVALID_CREDENTIAL);
		}

		$token = $this->users->generateToken($user);

		return new Identity($user->id, $user->role, $token);
	}

}
