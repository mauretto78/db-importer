<?php

namespace App\Tests;

use DbImporter\DataCollection;
use DbImporter\Importer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;

class ImporterOldTest extends TestCase
{
    /**
     * @var Importer
     */
    private $importer;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * setUp
     */
    public function setUp()
    {
        (new DotEnv())->load(__DIR__ . '/../.env');
        $dbUrl = getenv('DATABASE_TEST_URL');
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = array(
            'url' => $dbUrl,
        );
        $this->connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
        $this->createSchema();

        $this->importer = Importer::init($this->connection);
    }

    public function fdsfdsfds()
    {
        $data = new DataCollection();
        $data->addItem([
            'id_utente' => 1,
            'name_utente' => 'Mauro',
            'email_utente' => 'm.cassani@bestnetwork.it',
            'username_utente' => 'mauretto78',
        ]);
        $data->addItem([
            'id_utente' => 2,
            'name_utente' => 'Damian',
            'email_utente' => 'damian@bestnetwork.it',
            'username_utente' => 'bigfoot90',
        ]);
        $data->addItem([
            'id_utente' => 3,
            'name_utente' => 'Matteo',
            'email_utente' => 'm.adamo@bestnetwork.it',
            'username_utente' => 'maffeo',
        ]);

        $this->importer->setTable('example_table');
        $this->importer->setDebug(true);
        $this->importer->setTableMap([
            'id',
            'name',
            'username',
        ]);
        $this->importer->setSourceMap([
            'id_utente',
            'name_utente',
            'username_utente',
        ]);
        $this->importer->setData($data);

        $query = $this->importer->getQuery();
        $expectedQuery = 'INSERT IGNORE INTO `example_table` (`id`,`name`,`username`) VALUES (:id_utente_1, :name_utente_1, :username_utente_1), (:id_utente_2, :name_utente_2, :username_utente_2), (:id_utente_3, :name_utente_3, :username_utente_3) ON DUPLICATE KEY UPDATE `id`=VALUES(id), `name`=VALUES(name), `username`=VALUES(username)';

        $this->importer->executeQuery();

        $this->assertEquals($query, $expectedQuery);
    }

    /**
     * @test
     */
    public function it_should_returns_the_correct_query_and_execetues_it()
    {
        $data = new DataCollection();
        $data->addItem([
            'id' => 1,
            'name' => 'Mauro',
            'email' => 'm.cassani@bestnetwork.it',
            'username' => 'mauretto78',
        ]);
        $data->addItem([
            'id' => 2,
            'name' => 'Damian',
            'email' => 'damian@bestnetwork.it',
            'username' => 'bigfoot90',
        ]);
        $data->addItem([
            'id' => 3,
            'name' => 'Matteo',
            'email' => 'm.adamo@bestnetwork.it',
            'username' => 'maffeo',
        ]);

        $this->importer->setTable('example_table');
        $this->importer->setDebug(true);
        $this->importer->setMapping([
            'id' => 'id',
            'name' => 'name',
            'email' => 'email',
            'username' => 'username',
        ]);
        $this->importer->setData($data);

        $query = $this->importer->getQuery();
        $expectedQuery = 'INSERT IGNORE INTO `example_table` (`id`,`name`,`email`,`username`) VALUES (:id_1, :name_1, :email_1, :username_1), (:id_2, :name_2, :email_2, :username_2), (:id_3, :name_3, :email_3, :username_3) ON DUPLICATE KEY UPDATE `id`=VALUES(id), `name`=VALUES(name), `email`=VALUES(email), `username`=VALUES(username)';

        $this->importer->executeQuery();

        $this->assertEquals($query, $expectedQuery);
    }

    /**
     * @test
     */
    public function it_should_returns_the_correct_query_and_execetues_it_when_source_map_is_provided()
    {
        $data = new DataCollection();
        $data->addItem([
            'id_utente' => 1,
            'username_utente' => 'mauretto78',
            'email_utente' => 'm.cassani@bestnetwork.it',
            'name_utente' => 'Mauro',
        ]);
        $data->addItem([
            'email_utente' => 'damian@bestnetwork.it',
            'id_utente' => 2,
            'name_utente' => 'Damian',
            'username_utente' => 'bigfoot90',
        ]);
        $data->addItem([
            'id_utente' => 3,
            'name_utente' => 'Matteo',
            'username_utente' => 'maffeo',
            'email_utente' => 'm.adamo@bestnetwork.it',
        ]);

        $this->importer->setTable('example_table');
        $this->importer->setDebug(true);
        $this->importer->setMapping([
            'id' => 'id_utente',
            'name' => 'name_utente',
            'email' => 'email_utente',
            'username' => 'username_utente'
        ]);
        $this->importer->setData($data);

        $query = $this->importer->getQuery();
        $expectedQuery = 'INSERT IGNORE INTO `example_table` (`id`,`name`,`email`,`username`) VALUES (:id_utente_1, :name_utente_1, :email_utente_1, :username_utente_1), (:id_utente_2, :name_utente_2, :email_utente_2, :username_utente_2), (:id_utente_3, :name_utente_3, :email_utente_3, :username_utente_3) ON DUPLICATE KEY UPDATE `id`=VALUES(id), `name`=VALUES(name), `email`=VALUES(email), `username`=VALUES(username)';

        $this->importer->executeQuery();

        $this->assertEquals($query, $expectedQuery);
    }

    /**
     * createSchema
     */
    protected function createSchema()
    {
        $schema = new Schema();
        $tableName= 'example_table';

        if(false === $this->checkTable($tableName)){
            $table = $schema->createTable($tableName);
            $table->addColumn('id', 'integer');
            $table->addColumn('name', 'string');
            $table->addColumn('email', 'string');
            $table->addColumn('username', 'string', ['length' => 32]);
            $table->setPrimaryKey(['id']);

            $platform = $this->connection->getDatabasePlatform();
            $queries = $schema->toSql($platform);

            foreach ($queries as $query){
                $this->connection->executeQuery($query);
            }
        }
    }

    /**
     * @param $table
     * @return bool
     */
    public function checkTable($table)
    {
        try {
            $this->connection->executeQuery('DESC ' . $table);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
