<?php

namespace movi\Packages\Helpers;

use movi\Model\Mapper;

class SqlProcessor
{

	/** @var \movi\Model\Mapper */
	private $mapper;


	public function __construct(Mapper $mapper)
	{
		$this->mapper = $mapper;
	}


	/**
	 * @param $file
	 * @param string $delimiter
	 * @return bool
	 */
	public function execute($file, $delimiter = ';')
	{
		set_time_limit(0);

		if (file_exists($file) && is_file($file)) {
			$file = fopen($file, 'r');
			$query = array();

			while (feof($file) === false)
			{
				$query[] = fgets($file);

				if (preg_match('~' . preg_quote($delimiter, '~') . '\s*$~iS', end($query)) === 1) {
					$query = trim(implode('', $query));

					// Execute the query
					$this->mapper->query($query);

					while (ob_get_level() > 0)
					{
						ob_end_flush();
					}

					flush();
				}

				if (is_string($query)) {
					$query = array();
				}
			}

			return fclose($file);
		}

		return false;
	}

}