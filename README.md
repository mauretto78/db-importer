# Db-Importer

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mauretto78/db-importer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mauretto78/db-importer/?branch=master)
[![Build Status](https://travis-ci.org/mauretto78/db-importer.svg?branch=master)](https://travis-ci.org/mauretto78/db-importer)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/61444b8259e642f990965fc843283ad7)](https://www.codacy.com/app/mauretto78/db-importer?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=mauretto78/db-importer&amp;utm_campaign=Badge_Grade)
[![license](https://img.shields.io/github/license/mauretto78/db-importer.svg)]()
[![Packagist](https://img.shields.io/packagist/v/mauretto78/db-importer.svg)]()

This library allows you to import data in your database with very low effort.

## Basic Usage

To use Importer simply do this:

```php
use DbImporter\Importer;

// init Importer
$importer = Importer::init(
    $connection, // your DBAL connection
    $table,      // table to import data
    $mapping,    // mapping array
    $data,       // input data
    $ignoreErr,  // ignore errors (boolean). True is default value
    $mode        // insert mode. 'single' or 'multiple' are the only values allowed. 'multiple' is default value
);

// execute import query
$importer->execute()

```

Please note that you must pass a [DBAL Connection](http://www.doctrine-project.org/projects/dbal.html) instance to Importer class.

### Avaliable drivers
 
Currently the supported drivers are:

* `pdo_mysql` (MySQL)
* `pdo_pgsql` (PostgreSQL)
* `pdo_sqlite` (Sqlite)

### Mapping array

The mapping array is a simple key value array in which you specify the column name on your database's table and the corresponding key in the input data. Look at the following example:

```php
$mapping = [
    'id' => 'id_utente',             // 'id' is the column name on your database's table. 'id_utente' is the key in input data
    'name' => 'name_utente',         // 'name' is the column name on your database's table. 'name_utente' is the key in input data
    'username' => 'username_utente', // 'username' is the column name on your database's table. 'username_utente' is the key in input data
    'email' => 'email_utente',       // 'email' is the column name on your database's table. 'email_utente' is the key in input data
];

```

### Data

The only requirement is the input data must be iterable (array or object). Here's the most simple example:

```php
// as simple associative array
$data = [
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

//..
```

#### Working with Entities

You can use as your feed data an iterable object of entities. **Getters are required**. Look at the following example:

```php
// User entity
final class User
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $username;

    /**
     * User constructor.
     * @param $id
     * @param $name
     * @param $email
     * @param $username
     */
    public function __construct(
        $id,
        $name,
        $email,
        $username
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->username = $username;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
}

// use Doctrine\ArrayCollection as feed of Importer
$data = new ArrayCollection([
    new User(
        1,
        'Mauro',
        'assistenza@easy-grafica.com',
        'mauretto78'
    ),
    new User(
        2,
        'John',
        'john@doe.com',
        'johndoe'
    ), 
    new User(
        3,
        'Maria',
        'maria@key.com',
        'maria'
    )
]);

//..
```

### Insert Mode (multiple or single)

You can decide how to build insert query:
 
* 'multiple' (default) - insert data in a unique multiple insert query
* 'single' - insert data in a loop of insert queries
 
### Limit of records in multiple insert queries
 
Please note that there is a limit to the maximum number of records that can be inserted in a single query. In case this limit is exceeded, a loop of multiple insertion queries will be executed. 

This limit is:

* 4000 records for `pdo_mysql` driver
* 4000 records for `pdo_pgsql` driver
* 200 records for `pdo_sqlite` driver
 
### Create Schema

If you need to create table scheme, use `createSchema()` method. Do the following:

```php
$keys = [
    'id' => 'integer',
    'album_id' => 'integer',
    'titolo' => 'string',
    'url' => 'string',
    'thumbnail_url' => 'string',
];

$uniqueKeys = ['id'];
$indexKeys = ['album_id', 'titolo'];

$importer->createSchema($keys, $uniqueKeys, $indexKeys);
```

### Destroy Schema
 
To destroy table scheme, use `destroySchema()` method:

```php
// ..

$importer->destroySchema();
```

### Clear data table

If you want to clear table data (maybe before importing data), use `clearData()` method instead:

```php
// ..

$importer->clearData();
```

## Built With

* [DBAL](http://www.doctrine-project.org/projects/dbal.html) - Database Abstraction Layer

## Requirements

* PHP 5.6+
* MySQL 5.7+
* PostgreSQL 9.5+

## Support

If you found an issue or had an idea please refer [to this section](https://github.com/mauretto78/db-importer/issues).

## Authors

* **Mauro Cassani** - [github](https://github.com/mauretto78)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
