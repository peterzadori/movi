<?php

namespace movi\Model;

trait DateTimeEntity
{

	protected function initDefaults()
	{
		$this->date = new \DateTime();
	}

}