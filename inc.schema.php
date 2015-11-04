<?php

$int = array('type' => 'int',	'null' => false);
$text = array('type' => 'text',	'null' => false);

return array(
	'projects' => array(
		'project_nid'		=> $int,
		'project_name'		=> $text,
		'project_uid'		=> $int,
		'created'			=> $int,
		'project_downloads'	=> $int,
		'downloaded'		=> $int,
	),
	'releases' => array(
		'release_nid'		=> $int,
		'project_nid'		=> $int,
		'release_version'	=> $text,
		'title'				=> $text,
		'created'			=> $int,
		'changed'			=> $int,
		'dev'				=> $int,
		'downloaded'		=> $int,
		'deleted'			=> $int,
		'download_fail'		=> $text,
		'downloaded_when'	=> $int,
	),
	'modules' => array(
		'project_name'		=> $text,
		'module_name'		=> $text,
	),
	'dependencies' => array(
		'project_name'				=> $text,
		'module_name'				=> $text,
		'dependency_project_name'	=> $text,
		'dependency_module_name'	=> $text,
		'dependency_versions'		=> $text,
	),
);
