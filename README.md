# Db-Importer

[![Build Status](https://travis-ci.org/mauretto78/db-importer.svg?branch=master)](https://travis-ci.org/mauretto78/db-importer)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/61444b8259e642f990965fc843283ad7)](https://www.codacy.com/app/mauretto78/db-importer?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=mauretto78/db-importer&amp;utm_campaign=Badge_Grade)
[![Coverage Status](https://coveralls.io/repos/github/mauretto78/db-importer/badge.svg?branch=master)](https://coveralls.io/github/mauretto78/db-importer?branch=master)
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
    $ignore,     // ignore duplicates (boolean). True is default value
    $mode        // insert mode. 'single' or 'multiple' are the only values allowed. 'multiple' is default value
);

// execute import query
$importer->execute()

```

Please note that you must pass a [DBAL Connection](http://www.doctrine-project.org/projects/dbal.html) instance to Importer class.

### Avaliable drivers
 
Currently the supported drivers are:

* `pdo_mysql`
* `pdo_sqlite`

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

The only requirement is the input data must be iterable (array or object). Example:

```php
// as array
$data = [
    [
        'id_utente' => 1,
        'name_utente' => 'Mauro',
        'email_utente' => 'm.cassani@bestnetwork.it',
        'username_utente' => 'mauretto78',
    ],
    [
        'id_utente' => 2,
        'name_utente' => 'Damian',
        'username_utente' => 'bigfoot90',
        'email_utente' => 'damian@bestnetwork.it',
    ],
    [
        'id_utente' => 3,
        'username_utente' => 'maffeo',
        'name_utente' => 'Matteo',
        'email_utente' => 'm.adamo@bestnetwork.it',
    ]
];

// as an instance of Doctrine ArrayCollection
$data = new ArrayCollection([
    new User(
        1,
        'Mauro',
        'm.cassani@bestnetwork.it',
        'mauretto78'
    ),
    new User(
        2,
        'Damian',
        'damian@bestnetwork.it',
        'bigfoot90'
    ), 
    new User(
        3,
        'Matteo',
        'm.adamo@bestnetwork.it',
        'maffeo'
    )
]);

```

### Insert Mode (multiple or single)

You can decide how to build insert query:
 
* 'multiple' (default) - insert data in a unique multiple insert query
* 'single' - insert data in a loop of insert queries
 
### Limit of records in multiple insert queries
 
Please note that there is a limit to the maximum number of records that can be inserted in a single query. In case this limit is exceeded, a loop of multiple insertion queries will be executed. 

This limit is:

* 4000 records for `pdo_mysql` driver
* 200 records for `pdo_sqlite` driver
 
## Built With

* [DBAL](http://www.doctrine-project.org/projects/dbal.html) - Database Abstraction Layer

## Support

If you found an issue or had an idea please refer [to this section](https://github.com/mauretto78/db-importer/issues).

## Authors

* **Mauro Cassani** - [github](https://github.com/mauretto78)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
