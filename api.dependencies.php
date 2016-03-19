<?php

require_once 'inc.config.php';

if ( empty($_GET['project']) ) {
	exit('Need `project` param to query.');
}

$dependencies = $db->fetch("
	SELECT
		d.dependency_module_name,
		d.dependency_project_name,
		(SELECT COUNT(1) FROM modules WHERE module_name = d.dependency_module_name) AS found_projects,
		(SELECT project_name FROM modules WHERE module_name = d.dependency_module_name) AS found_project,
		(SELECT COUNT(1) FROM projects WHERE project_name = d.dependency_module_name) AS project_literal
	FROM dependencies d
	JOIN modules m ON m.module_name = d.module_name AND m.project_name = ?
	GROUP BY d.dependency_module_name
	ORDER BY d.dependency_module_name
", array($_GET['project']))->all();

$modules = array_map(function($dep) {
	return array(
		'module' => $dep->dependency_module_name,
		'projects' => (int) $dep->found_projects,
		'project' => $dep->dependency_project_name ?: ($dep->found_projects == 1 ? $dep->found_project : null),
		'project_literal' => (bool) $dep->project_literal,
	);
}, $dependencies);

$json = json_encode($modules);

if ( !empty($_GET['jsonp']) ) {
	header('Content-type: text/javascript; charset=utf-8');
	echo $_GET['jsonp'] . '(' . $json . ");\n";
	exit;
}

header('Content-type: text/json; charset=utf-8');
echo $json . "\n";
