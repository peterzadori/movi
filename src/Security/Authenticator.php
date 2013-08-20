<?php

namespace movi\Security;

use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;

class Authenticator implements IAuthenticator
{

	/** @var \movi\Security\IUsersRepository */
	private $usersRepository;


	public function __construct(IUsersRepository $usersRepository)
	{
		$this->usersRepository = $usersRepository;
	}


	/**
	 * @param array $credentials
	 * @return Identity|\Nette\Security\IIdentity
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		// Find the user
		$user = $this->usersRepository->login($credentials);

		if (!$user) {
			throw new AuthenticationException('Zle zadané údaje.', self::INVALID_CREDENTIAL);
		}

		$token = $this->usersRepository->token($user->id);

		return new Identity($user->id, $user->role, $token);
	}

}
