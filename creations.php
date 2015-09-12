<?php

require 'inc.config.php';

header('Content-type: text/plain;charset=utf-8');

$byY = $db->fetch("
	SELECT strftime('%Y', datetime(created, 'unixepoch')) AS y, COUNT(1) AS num
	FROM projects
	GROUP BY y
	ORDER BY y
");

$byYM = $db->fetch("
	SELECT strftime('%Y-%m', datetime(created, 'unixepoch')) AS ym, COUNT(1) AS num
	FROM projects
	GROUP BY ym
	ORDER BY ym
");

echo "Project creations by year:\n\n";
foreach ($byY as $project) {
	echo $project->y . "\t " . $project->num . "\n";
}

echo "\n\n";

echo "Project creations by month, over the years:\n\n";
foreach ($byYM as $project) {
	echo $project->ym . "\t " . $project->num . "\n";
}
