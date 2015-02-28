<?php

require_once 'inc.config.php';

// EXAMPLE
// https://www.drupal.org/api-d7/node.json?type=project_release&sort=nid&direction=desc&field_release_build_type=static&limit=10

$limit = 100;
$_url = "https://www.drupal.org/api-d7/node.json?type=project_release&sort=nid&direction=desc&field_release_build_type=static&limit=$limit&page=";

// Track `nid`
// Check `field_release_version` for 7.x
// Save by `field_release_project`

$projects = $db->select_fields('projects', 'project_nid, project_name', '1');
$releases = $db->select_fields('releases', 'release_nid, release_nid', '1');

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

	$new = $updated = 0;
	foreach ( $list as $release ) {
		$release_version = $release['field_release_version'];
		$dev = (int)($release['field_release_build_type'] == 'dynamic');
		$release_nid = $release['nid'];
		$project_nid = @$release['field_release_project']['id'];
		$title = $release['title'];
		$created = $release['created'];
		$changed = $release['changed'];

		// Invalid project
		if ( !$project_nid || !isset($projects[$project_nid]) ) continue;

		// Invalid core version
		if ( !preg_match('#^7\.x\-#', $release_version) ) continue;

		// Already have this one
		$have = isset($releases[$release_nid]);
		if ( $have && !$dev ) continue;

		// Update dev version
		if ( $have ) {
			$updated++;
			echo "  - $title\n";

			$downloaded = 0;
			$db->update('releases', compact('title', 'changed', 'downloaded'), compact('release_nid'));
		}

		// Create new static release
		else {
			// Find existing release for this project
			$have = $db->select('releases', array(
				'project_nid' => $project_nid,
				'dev' => 0,
			))->first();

			// If the existing release is actually older (created, not changed), we want this one.
			if ( !$have || $have->created < $created ) {
				$new++;
				echo "  - $title\n";

				$db->insert('releases', compact('release_nid', 'project_nid', 'release_version', 'title', 'created', 'changed', 'dev'));
				$releases[$release_nid] = $release_nid;

				// And remove the older release, because we now have a more recent version
				if ( $have ) {
					$db->delete('releases', array('release_nid' => $have->release_nid));
				}
			}
		}
	}

	if ( $new ) {
		$pagesOfNothing = 0;
	}
	else {
		$pagesOfNothing++;
	}

	echo "Did page         $page - $new new / $updated updated\n";


	// Next cycle, or finish
	if ( $isLastPage || $pagesOfNothing >= 3 ) {
		finish();
		break;
	}

	echo "\n";
	$page++;

	sleep(1);

}
