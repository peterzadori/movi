<?php

namespace movi\Security;

interface IUsersRepository
{

	public function login(array $credentials);

	public function token($user);

	public function isTokenValid($token, $user);

	public function load(Identity $identity);

}
