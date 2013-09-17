<?php

namespace movi\Schemas;

use Doctrine\DBAL\Schema\Schema;

abstract class SchemaFactory implements ISchemaFactory
{

	/**
	 * @return Schema
	 */
	final public function createSchema()
	{
		$schema = new Schema();

		$this->configure($schema);

		return $schema;
	}

}