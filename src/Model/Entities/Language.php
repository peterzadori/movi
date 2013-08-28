<?php

namespace movi\Model\Entities;

use Nette\Utils\Strings;
use movi\Model\IdentifiedEntity;

/**
 * Class Language
 * @package movi\Model\Entities
 * @property string $name
 * @property string $code
 * @property bool $default
 * @property bool $active
 * @property int $order
 */
final class Language extends IdentifiedEntity
{

	public function setCode($code)
	{
		$this->row->code = Strings::lower($code);
	}

}