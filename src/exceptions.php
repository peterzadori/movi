<?php

namespace movi;


class LogicException extends \LogicException
{

}


class InvalidArgumentException extends LogicException
{

}


class InvalidStateException extends LogicException
{

}


class NotImplementedException extends LogicException
{

}


class FileNotFoundException extends \Exception
{

}


class PackageNotFoundException extends InvalidArgumentException
{

}


class PackageClassNotFoundException extends PackageNotFoundException
{

}


class PackageRegisteredException extends InvalidArgumentException
{

}

class EntityNotFound extends InvalidArgumentException
{

}