<?php

namespace movi\Security;

use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;

class Authenticator implements IAuthenticator
{

	/** @var IUsers */
	private $users;


	public function __construct(IUsers $users)
	{
		$this->users = $users;
	}


	/**
	 * @param array $credentials
	 * @return Identity
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		$user = $this->users->login($credentials);

		if (!$user) {
			throw new AuthenticationException(self::INVALID_CREDENTIAL);
		}

		$token = $this->users->generateToken($user);

		return new Identity($user->id, $user->role, $token);
	}

}
