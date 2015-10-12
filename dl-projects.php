<?php

require_once 'inc.config.php';

// EXAMPLE
// https://www.drupal.org/api-d7/node.json?type=project_module&sort=nid&direction=desc&field_project_type=full&limit=10

$limit = 100;
$_url = "https://www.drupal.org/api-d7/node.json?type=project_module&sort=nid&direction=desc&limit=$limit&field_project_type=full&page=";

$projects = $db->select_fields('projects', 'project_nid, project_name', '1');

$page = 0;
$pagesOfNothing = 0;
while ( true ) {

	$url = $_url . $page;

	echo "Downloading page $page\n";
	echo "  $url\n";

	$_time = microtime(1);
	$json = download($url);
	$data = json_decode($json, true);

	$time = number_format(microtime(1) - $_time, 3);
	echo "Downloaded page  $page - $time sec\n";

	// Find last page
	preg_match('#page=(\d+)#', $data['last'], $match);
	$lastPage = $match[1];
	$isLastPage = $lastPage == $page;

	// Relevant data
	$list = $data['list'];

	$new = 0;
	foreach ( $list as $project ) {
		$name = $project['field_project_machine_name'];
		$nid = $project['nid'];
		$uid = @$project['author']['id'] ?: 0;
		$created = $project['created'];
		$downloads = @$project['field_download_count'] ?: 0;

		if ( !isset($projects[$nid]) ) {
			$new++;

			echo "  - $name ($nid) - https://www.drupal.org/project/$name\n";

			$db->insert('projects', array(
				'project_name' => $name,
				'project_nid' => $nid,
				'project_uid' => $uid,
				'project_downloads' => $downloads,
				'created' => $created,
				'downloaded' => time(),
			));
		}
	}

	if ( $new ) {
		$pagesOfNothing = 0;
	}
	else {
		$pagesOfNothing++;
	}

	echo "Did page         $page - $new new projects\n";


	// Next cycle, or finish
	if ( $isLastPage || $pagesOfNothing >= 3 ) {
		finish();
		break;
	}

	echo "\n";
	$page++;

	sleep(1);

}
