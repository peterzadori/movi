<?php

namespace movi\Security;

use Nette;
use movi\Security\IUsersRepository;

class UserStorage extends Nette\Http\UserStorage
{

	/** @var \movi\Security\IUsersRepository */
	private $usersRepository;

	/** @var bool */
	private $authenticated;


	public function __construct(Nette\Http\Session $session, IUsersRepository $usersRepository)
	{
		parent::__construct($session);

		$this->usersRepository = $usersRepository;
	}


	/**
	 * @return Identity|Nette\Security\IIdentity|NULL
	 */
	public function getIdentity()
	{
		$identity = parent::getIdentity();

		if ($identity instanceof Identity && !$identity->isLoaded()) {
			$this->usersRepository->load($identity);
		}

		return $identity;
	}


	/**
	 * @return bool
	 */
	public function isAuthenticated()
	{
		$authenticated = parent::isAuthenticated();

		if ($this->authenticated === NULL || $this->authenticated !== $authenticated) {
			if ($authenticated === true) {
				// Check token
				$identity = $this->getIdentity();

				if (!$this->usersRepository->isTokenValid($identity->getToken(), $identity->getId())) {
					$this->getSessionSection(true)->remove(); // Logout

					$authenticated = false;
				}
			}

			$this->authenticated = $authenticated;
		}

		return $this->authenticated;
	}

}