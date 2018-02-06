<?php

namespace DbImporter\Tests;

use DbImporter\Collections\DataCollection;
use DbImporter\QueryBuilder\Contracts\QueryBuilderInterface;
use DbImporter\QueryBuilder\SqliteQueryBuilder;

class SqliteQueryBuilderTest extends BaseTestCase
{
    /**
     * @test
     */
    public function it_should_returns_the_correct_multiple_insert_query()
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

        $mapping = [
            'id' => 'id_utente',
            'name' => 'name_utente',
            'username' => 'username_utente',
        ];

        $qb = new SqliteQueryBuilder(
            'example_table',
            $mapping,
            $data,
            true
        );

        $queries = $qb->getQueries();
        foreach ($queries as $query){
            $expectedQuery = 'INSERT OR IGNORE INTO `example_table` (`id`, `name`, `username`) VALUES (:id_utente_1, :name_utente_1, :username_utente_1), (:id_utente_2, :name_utente_2, :username_utente_2), (:id_utente_3, :name_utente_3, :username_utente_3)';

            $this->assertInstanceOf(QueryBuilderInterface::class, $qb);
            $this->assertEquals($query, $expectedQuery);
        }
    }

    /**
     * @test
     */
    public function it_should_returns_the_correct_single_insert_query()
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

        $mapping = [
            'id' => 'id_utente',
            'name' => 'name_utente',
            'username' => 'username_utente',
        ];

        $qb = new SqliteQueryBuilder(
            'example_table',
            $mapping,
            $data,
            true
        );

        $queries = $qb->getQueries('single');
        foreach ($queries as $query){
            $expectedQuery = 'INSERT OR IGNORE INTO `example_table` (`id`, `name`, `username`) VALUES (:id_utente, :name_utente, :username_utente)';

            $this->assertInstanceOf(QueryBuilderInterface::class, $qb);
            $this->assertEquals($query, $expectedQuery);
        }

        $this->assertCount(3, $queries);
    }
}
