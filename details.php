<?php

require 'inc.config.php';

header('Content-type: text/plain;charset=utf-8');

$module = @$_GET['module'] ?: @$_SERVER['argv'][1];
if ( !$module ) {
	exit("Need `module` in arg 1.\n");
}

$projects = $db->select_fields('modules', 'project_name', array('module_name' => $module));
echo "Module `$module` exists in these projects:\n\n";
ul($projects);

echo "\n\n";

$modules = $db->select_fields('modules', 'module_name', array('project_name' => $projects));
echo "Above " . count($projects) . " projects contain these " . count($modules) . " modules:\n\n";
ul($modules);

echo "\n\n";

$dependencies = array_unique($db->select_fields('dependencies', 'dependency_module_name', array('module_name' => $module)));
echo "It has these " . count($dependencies) . " dependencies:\n\n";
ul($dependencies);

echo "\n\n";

$dependees = array_unique($db->select_fields('dependencies', 'module_name', array('dependency_module_name' => $module)));
echo "It is depended on by these " . count($dependees) . " modules:\n\n";
ul($dependees);



function ul( $items ) {
	foreach ( $items as $item ) {
		echo "* $item\n";
	}
}
