<?php

namespace DbImporter\Tests;

use DbImporter\Collections\DataCollection;
use DbImporter\Importer;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;

class ImporterTest extends TestCase
{
    const TABLE_NAME = 'example_table';

    /**
     * @var array
     */
    private $config;
    private $data;
    private $mapping;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->config = require __DIR__.'/../app/bootstrap.php';

        $this->data = new DataCollection();
        $this->data->addItem([
            'id_utente' => 1,
            'name_utente' => 'Mauro',
            'email_utente' => 'm.cassani@bestnetwork.it',
            'username_utente' => 'mauretto78',
        ]);
        $this->data->addItem([
            'id_utente' => 2,
            'name_utente' => 'Damian',
            'username_utente' => 'bigfoot90',
            'email_utente' => 'damian@bestnetwork.it',
        ]);
        $this->data->addItem([
            'id_utente' => 3,
            'username_utente' => 'maffeo',
            'name_utente' => 'Matteo',
            'email_utente' => 'm.adamo@bestnetwork.it',
        ]);

        $this->mapping = [
            'id' => 'id_utente',
            'name' => 'name_utente',
            'username' => 'username_utente',
            'email' => 'email_utente',
        ];
    }

    /**
     * @test
     */
    public function mysqldfsfdsfsd()
    {
        $connection = DriverManager::getConnection(
            [
                'url' => $this->config['mysql_url'],
            ],
            new Configuration()
        );

        $importer = Importer::init(
            $connection,
            'example_table',
            true,
            $this->mapping,
            $this->data
        );

        $this->executeQueryAndPerformTests($importer, $connection, 'mysql');
    }

    /**
     * @test
     */
    public function sqlite()
    {
        $connection = DriverManager::getConnection(
            [
                'url' => $this->config['sqlite_url'],
            ],
            new Configuration()
        );

        $importer = Importer::init(
            $connection,
            'example_table',
            true,
            $this->mapping,
            $this->data
        );

        $this->executeQueryAndPerformTests($importer, $connection, 'sqlite');
    }

    /**
     * @param Importer $importer
     * @param Connection $connection
     * @param $driver
     */
    public function executeQueryAndPerformTests(Importer $importer, Connection $connection, $driver)
    {
        $this->createSchema($connection, $driver);
        $this->assertTrue($importer->executeQuery());
    }

    /**
     * @param Connection $connection
     */
    protected function createSchema(Connection $connection, $driver)
    {
        $schema = new Schema();

        if(false === $this->checkIfTableExists($connection, self::TABLE_NAME, $driver)){
            $table = $schema->createTable(self::TABLE_NAME);
            $table->addColumn('id', 'integer');
            $table->addColumn('name', 'string');
            $table->addColumn('email', 'string');
            $table->addColumn('username', 'string', ['length' => 32]);
            $table->setPrimaryKey(['id']);

            $platform = $connection->getDatabasePlatform();
            $queries = $schema->toSql($platform);

            foreach ($queries as $query){
                $connection->executeQuery($query);
            }
        }
    }

    /**
     * @param $table
     * @return bool
     */
    public function checkIfTableExists(Connection $connection, $table, $driver)
    {
        switch ($driver){
            case 'mysql':
                $query = 'DESC ' . $table;
                break;

            case 'sqlite':
                $query = 'PRAGMA table_info( ' . $table .')';
                break;
        }

        try {
            $connection->executeQuery($query);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
