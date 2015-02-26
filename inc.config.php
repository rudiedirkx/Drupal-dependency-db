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

// Screw ACID, go SPEED!
$db->execute('PRAGMA synchronous=OFF');
$db->execute('PRAGMA journal_mode=OFF');



function download($url) {
	global $context;
	return file_get_contents($url, false, $context);
}

function finish() {
	exit("\nDone!\n");
}

function drupal_parse_info_format($data) {
	$info = array();
	$constants = get_defined_constants();

	if (preg_match_all('
		@^\s*								# Start at the beginning of a line, ignoring leading whitespace
		((?:
			[^=;\[\]]|						# Key names cannot contain equal signs, semi-colons or square brackets,
			\[[^\[\]]*\]					# unless they are balanced and not nested
		)+?)
		\s*=\s*								# Key/value pairs are separated by equal signs (ignoring white-space)
		(?:
			("(?:[^"]|(?<=\\\\)")*")|		# Double-quoted string, which may contain slash-escaped quotes/slashes
			(\'(?:[^\']|(?<=\\\\)\')*\')|	# Single-quoted string, which may contain slash-escaped quotes/slashes
			([^\r\n]*?)						# Non-quoted string
		)\s*$								# Stop at the next end of a line, ignoring trailing whitespace
		@msx', $data, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			// Fetch the key and value string.
			$i = 0;
			foreach (array('key', 'value1', 'value2', 'value3') as $var) {
				$$var = isset($match[++$i]) ? $match[$i] : '';
			}
			$value = stripslashes(substr($value1, 1, -1)) . stripslashes(substr($value2, 1, -1)) . $value3;

			// Parse array syntax.
			$keys = preg_split('/\]?\[/', rtrim($key, ']'));
			$last = array_pop($keys);
			$parent = &$info;

			// Create nested arrays.
			foreach ($keys as $key) {
				if ($key == '') {
					$key = count($parent);
				}
				if (!isset($parent[$key]) || !is_array($parent[$key])) {
					$parent[$key] = array();
				}
				$parent = &$parent[$key];
			}

			// Handle PHP constants.
			if (isset($constants[$value])) {
				$value = $constants[$value];
			}

			// Insert actual value.
			if ($last == '') {
				$last = count($parent);
			}
			$parent[$last] = $value;
		}
	}

	return $info;
}
