<?php

define('DDD_VERSION', '2.1');

define('PROJECTS_CACHE', 'projects.cache');
define('DEPS_FROM_CACHE', 'deps_from.cache');
define('DEPS_TO_CACHE', 'deps_to.cache');
define('DEPS_FAILS_CACHE', 'deps_fails.cache');

define('FETCH_PER_PAGE', 50);
define('FETCH_LOG', 'fetch.log');
define('MAX_RUNTIME', 20 * 60 * 60); // 20h

header('Content-type: text/plain;charset=utf-8');



$context = stream_context_create(array(
	'http' => array(
		'user_agent' => 'Dependency db ' . DDD_VERSION,
	),
));



require '../../inc/db/db_sqlite.php';
$db = db_sqlite::open(array('database' => 'db/drupaldeps.sqlite3'));

$schema = require __DIR__ . '/inc.schema.php';
$db->schema($schema);


require __DIR__ . '/inc.drupal.php';



function download($url) {
	global $context;
	return file_get_contents($url, false, $context);
}

function finish() {
	echo "\nDone!\n\n\n\n";
}
