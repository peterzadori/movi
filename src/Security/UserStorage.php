<?php

namespace movi\Security;

use Nette;

class UserStorage extends Nette\Http\UserStorage
{

	/** @var IUsers */
	private $users;

	/** @var bool */
	private $authenticated;


	public function __construct(Nette\Http\Session $session, IUsers $users)
	{
		parent::__construct($session);

		$this->users = $users;
	}


	/**
	 * @return Identity|Nette\Security\IIdentity|NULL
	 */
	public function getIdentity()
	{
		$identity = parent::getIdentity();

		if ($identity instanceof Identity && !$identity->isLoaded()) {
			$this->users->loadIdentity($identity);
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
				$identity = $this->getIdentity();

				if (!$this->users->validateToken($identity->getToken(), $identity->getUser())) {
					$this->getSessionSection(true)->remove(); // Logout

					$authenticated = false;
				}
			}

			$this->authenticated = $authenticated;
		}

		return $this->authenticated;
	}

}