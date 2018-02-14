<?php
/**
 * This file is part of the DbImporter package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DbImporter\Tests;

use DbImporter\QueryBuilder\Contracts\QueryBuilderInterface;
use DbImporter\QueryBuilder\SqliteQueryBuilder;

class SqliteQueryBuilderTest extends BaseTestCase
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $mapping;

    public function setUp()
    {
        $this->data = [
            [
                'id_utente' => 1,
                'name_utente' => 'Mauro',
                'email_utente' => 'assistenza@easy-grafica.com',
                'username_utente' => 'mauretto78',
            ],
            [
                'id_utente' => 2,
                'name_utente' => 'John',
                'email_utente' => 'john@doe.com',
                'username_utente' => 'johndoe',
            ],
            [
                'id_utente' => 3,
                'name_utente' => 'Maria',
                'email_utente' => 'maria@key.com',
                'username_utente' => 'maria',
            ]
        ];

        $this->mapping = [
            'id' => 'id_utente',
            'name' => 'name_utente',
            'username' => 'username_utente',
        ];
    }

    /**
     * @test
     */
    public function it_should_returns_the_correct_multiple_insert_query()
    {
        $qb = new SqliteQueryBuilder(
            'example_table',
            $this->mapping,
            $this->data,
            true
        );

        $queries = $qb->getQueries();
        foreach ($queries as $query) {
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
        $qb = new SqliteQueryBuilder(
            'example_table',
            $this->mapping,
            $this->data,
            true
        );

        $queries = $qb->getQueries('single');
        foreach ($queries as $query) {
            $expectedQuery = 'INSERT OR IGNORE INTO `example_table` (`id`, `name`, `username`) VALUES (:id_utente, :name_utente, :username_utente)';

            $this->assertInstanceOf(QueryBuilderInterface::class, $qb);
            $this->assertEquals($query, $expectedQuery);
        }

        $this->assertCount(3, $queries);
    }
}
