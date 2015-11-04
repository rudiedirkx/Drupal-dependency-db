<?php

require 'inc.config.php';

$db->begin();

$dependencies = $db->fetch("
	SELECT *
	FROM dependencies
	WHERE dependency_module_name LIKE '%(%'
");

foreach ( $dependencies as $dependency ) {
	$conditions = array(
		'project_name' => $dependency['project_name'],
		'module_name' => $dependency['module_name'],
		'dependency_module_name' => $dependency['dependency_module_name'],
	);

	var_dump($dependency['dependency_module_name']);
	$dependency = drupal_parse_dependency($dependency['dependency_module_name']);
	$update = array(
		'dependency_project_name' => @$dependency['project'] ?: '',
		'dependency_module_name' => $dependency['name'],
		'dependency_versions' => trim(@$dependency['original_version'], "\t )("),
	);
	print_r($update);
	$db->update('dependencies', $update, $conditions);

	echo "\n\n";
}

$db->commit();
