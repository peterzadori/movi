<?php

namespace movi\Security;

interface IUsers
{

	public function login(array $credentials);

	public function generateToken($user);

	public function validateToken($token, $user);

	public function loadIdentity(Identity $identity);

}
