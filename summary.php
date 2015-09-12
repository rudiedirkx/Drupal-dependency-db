<?php

require 'inc.config.php';

header('Content-type: text/plain;charset=utf-8');

$lastRelease = $db->max('releases', 'downloaded_when', '1');
$projects = $db->count('projects');
$allReleases = $db->select_one('releases', 'COUNT(DISTINCT project_nid)', '1');
$nonDevReleases = $db->select_one('releases', 'COUNT(DISTINCT project_nid)', 'dev = 0');
$modules = $db->count('modules');
$noDeps = $db->count_rows('select * from modules where module_name not in (select module_name from dependencies)');
$manyDeps = $db->count_rows('SELECT module_name, count(1) AS deps FROM dependencies group by module_name having deps > 5');
$depsFails = $db->select_fields('releases', 'title, created', 'dev = 0 AND download_fail IS NOT NULL');
$mostDeps = $db->fetch_fields('SELECT module_name, count(1) AS deps FROM dependencies group by module_name order by deps desc limit 10');
$mostDepees = $db->fetch_fields('SELECT dependency_module_name, count(1) AS deps FROM "dependencies" group by dependency_module_name order by deps desc limit 10');

echo "Last release download @ " . ($lastRelease ? date('Y-m-d H:i', $lastRelease) : '?') . "\n";
echo "\n";

echo "Downloaded  " . number_format($projects, 0, '.', ' ') . "\t PROJECTS' meta data.\n";
echo "            " . number_format($allReleases, 0, '.', ' ') . "\t have a 7.x release.\n";
echo "            " . number_format($nonDevReleases, 0, '.', ' ') . "\t are stable (non-dev) releases.\n";
echo "\n";

echo "Parsed      " . number_format($modules, 0, '.', ' ') . "\t MODULES' info files.\n";
echo "            " . number_format($noDeps, 0, '.', ' ') . "\t have NO dependencies.\n";
echo "            " . number_format($manyDeps, 0, '.', ' ') . "\t have > 5 dependencies.\n";
echo "\n";

echo "            " . number_format(count($depsFails), 0, '.', ' ') . "  \t stable releases FAILED to download INFOS.\n";
echo "\n";

echo "The modules with the most dependencies:\n";
print_r($mostDeps);
echo "\n";

echo "The most depended on modules:\n";
print_r($mostDepees);
echo "\n";

echo "Download fails:\n";
print_r(array_map(function($created) {
	return date('Y-m-d', $created);
}, $depsFails));
echo "\n";
