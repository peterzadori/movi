<?php

namespace movi\Schemas;

use Doctrine\DBAL\Schema\Schema;

interface ISchemaFactory
{

	public function createSchema();

	public function configure(Schema $schema);

}