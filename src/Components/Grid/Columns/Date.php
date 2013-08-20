<?php

namespace movi\Components\Grid\Columns;

class Date extends Column
{

	/** @var string */
	private $format = 'dd.mm.yyyy';


	public function setFormat($format = 'dd.mm.yyyy')
	{
		if ($format !== NULL) {
			$this->format = $format;
		}

		return $this;
	}


	public function getFormat()
	{
		return $this->format;
	}

}