<?php

require_once 'inc.config.php';

if ( empty($_GET['project']) ) {
	exit('Need `project` param to query.');
}

$dependencies = $db->fetch("
	SELECT DISTINCT dependency_module_name
	FROM dependencies d
	WHERE module_name = ? AND dependency_project_name <> module_name
	ORDER BY dependency_module_name
", array($_GET['project']))->fields('dependency_module_name');

$dependees = $db->fetch("
	SELECT DISTINCT module_name
	FROM dependencies d
	WHERE dependency_module_name = ? AND project_name <> dependency_module_name
	ORDER BY module_name
", array($_GET['project']))->fields('module_name');

$json = json_encode(compact('dependencies', 'dependees'));

if ( !empty($_GET['jsonp']) ) {
	header('Content-type: text/javascript; charset=utf-8');
	echo $_GET['jsonp'] . '(' . $json . ");\n";
	exit;
}

header('Content-type: text/json; charset=utf-8');
echo $json . "\n";
