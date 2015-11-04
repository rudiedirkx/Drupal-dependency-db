<?php

require 'inc.config.php';

$releases = $db->select('releases', '1 ORDER BY changed');

$db->begin();

$db->update('releases', array('downloaded' => 0, 'deleted' => 0), '1');

$done = array();
$deleted = 0;
foreach ($releases as $release) {
	// echo $release->title . "\n";

	// New
	if ( !isset($done[$release->project_nid]) ) {
		$done[$release->project_nid] = $release->release_nid;
	}
	// Done
	else {
		// Delete non-dev
		if ( !$release->dev ) {
			$deleted++;

			$db->update('releases', array('deleted' => 1), array('release_nid' => $release->release_nid));
		}
		// Mark as downloaded
		else {
			$db->update('releases', array('downloaded' => 1), array('release_nid' => $release->release_nid));
		}
	}
}

$db->commit();

echo "\n";

echo count($done) . " releases ready for download\n";
echo "Deleted $deleted irrelevant releases\n";
