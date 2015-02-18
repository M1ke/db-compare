<?php
require __DIR__.'/vendor/autoload.php';

require __DIR__.'/DbCompare.php';

define('SQL_SERVER','localhost');
define('SQL_USER','root');
define('SQL_PASS','');

$db_compare=new DbCompare(SQL_SERVER,SQL_USER,SQL_PASS);
$db_compare->addDb('db_1')->addDb('db_2');

$output=$db_compare->compare('table',['key1','key2'],'primary_key');

/* results in array [
	'table' => array with one "row" per "row with differences".
		Each row contains 1 column per database per key
	'arr' => 3d array with one "row" per "row with differences".
		Each row contains one array per key, with each array containing a value for each database name
	'new' => array where the keys are primary keys of new rows, contains no data
]