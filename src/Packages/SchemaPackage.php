<?php

namespace movi\Packages;

use Doctrine\DBAL\Schema\Schema;

class SchemaPackage extends Package
{

	/**
	 * @param Schema $schema
	 * @return Schema
	 */
	public function createSchema(Schema $schema)
	{

	}

}