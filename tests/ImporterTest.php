<?php

namespace DbImporter\Tests;

use DbImporter\Collections\DataCollection;
use DbImporter\Importer;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

class ImporterTest extends BaseTestCase
{
    const TABLE_NAME = 'example_table';

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $mapping;

    /**
     * @var array
     */
    private $keys;

    /**
     * @var array
     */
    private $uniqueKeys;

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

        $this->keys = [
            'id' => 'integer',
            'name' => 'string',
            'email' => 'string',
            'username' => 'string',
        ];

        $this->uniqueKeys = ['id'];
    }

    /**
     * @test
     * @expectedException \DbImporter\Exceptions\NotAllowedDriverException
     * @expectedExceptionMessage The driver sqlsrv is not allowed. Drivers allowed are: [pdo_mysql,pdo_sqlite]
     */
    public function it_throws_NotAllowedDriverException_if_a_not_yet_supported_driver_is_provided()
    {
        $connection = DriverManager::getConnection(
            [
                'url' => $this->config['sqlsrv_url'],
            ],
            new Configuration()
        );

        Importer::init(
            $connection,
            self::TABLE_NAME,
            $this->mapping,
            $this->data,
            true
        );
    }

    /**
     * @test
     * @expectedException \DbImporter\Exceptions\NotAllowedModeException
     * @expectedExceptionMessage The mode not-allowed-insert-mode is not allowed. Drivers allowed are: [single,multiple]
     */
    public function it_throws_NotAllowedModeException_if_a_not_yet_supported_driver_is_provided()
    {
        $connection = DriverManager::getConnection(
            [
                'url' => $this->config['mysql_url'],
            ],
            new Configuration()
        );

        Importer::init(
            $connection,
            self::TABLE_NAME,
            $this->mapping,
            $this->data,
            true,
            'not-allowed-insert-mode'
        );
    }

    /**
     * @test
     */
    public function execute_the_multiple_import_query_with_mysql_driver()
    {
        $connection = DriverManager::getConnection(
            [
                'url' => $this->config['mysql_url'],
            ],
            new Configuration()
        );

        $importer = Importer::init(
            $connection,
            self::TABLE_NAME,
            $this->mapping,
            $this->data,
            true
        );

        $this->executeQueryAndPerformTests(
            $importer,
            $connection,
            self::TABLE_NAME,
            $this->keys,
            $this->uniqueKeys
        );
    }

    /**
     * @test
     */
    public function execute_the_multiple_import_query_with_sqlite_driver()
    {
        $connection = DriverManager::getConnection(
            [
                'url' => $this->config['sqlite_url'],
            ],
            new Configuration()
        );

        $importer = Importer::init(
            $connection,
            self::TABLE_NAME,
            $this->mapping,
            $this->data,
            true
        );

        $this->executeQueryAndPerformTests(
            $importer,
            $connection,
            self::TABLE_NAME,
            $this->keys,
            $this->uniqueKeys
        );
    }

    /**
     * @test
     */
    public function execute_the_single_import_query_with_mysql_driver()
    {
        $connection = DriverManager::getConnection(
            [
                'url' => $this->config['mysql_url'],
            ],
            new Configuration()
        );

        $importer = Importer::init(
            $connection,
            self::TABLE_NAME,
            $this->mapping,
            $this->data,
            true,
            'single'
        );

        $this->executeQueryAndPerformTests(
            $importer,
            $connection,
            self::TABLE_NAME,
            $this->keys,
            $this->uniqueKeys
        );
    }

    /**
     * @test
     */
    public function execute_the_single_import_query_with_sqlite_driver()
    {
        $connection = DriverManager::getConnection(
            [
                'url' => $this->config['sqlite_url'],
            ],
            new Configuration()
        );

        $importer = Importer::init(
            $connection,
            self::TABLE_NAME,
            $this->mapping,
            $this->data,
            true,
            'single'
        );

        $this->executeQueryAndPerformTests(
            $importer,
            $connection,
            self::TABLE_NAME,
            $this->keys,
            $this->uniqueKeys
        );
    }
}
