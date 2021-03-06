<?php

namespace movi\Packages\Installers;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use movi\Model\Connection;
use movi\Packages\IInstaller;
use movi\Packages\Package;

final class SchemaInstaller implements IInstaller
{

	/** @var \movi\Model\Connection */
	private $connection;

	/** @var \Doctrine\DBAL\Platforms\MySqlPlatform */
	private $platform;


	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
		$this->platform = new MySqlPlatform();
	}


	/**
	 * @param Package $package
	 * @return bool
	 */
	public function install(Package $package)
	{
		if (count($package->schemas) > 0) {
			foreach ($package->schemas as $class)
			{
				if (class_exists($class)) {
					/** @var \movi\Schemas\SchemaFactory $factory */
					$factory = new $class;
					$schema = $factory->createSchema();

					$queries = $schema->toSql($this->platform);

					foreach ($queries as $query)
					{
						try {
							$this->connection->query($query);
						} catch (\DibiDriverException $e) {

						}
					}
				}
			}
		}

		return true;
	}

}