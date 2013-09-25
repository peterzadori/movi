<?php

namespace movi\Model;

abstract class Entity extends \LeanMapper\Entity implements \JsonSerializable
{

	/**
	 * @return array|mixed
	 */
	public function jsonSerialize()
	{
		return $this->getRowData();
	}

}