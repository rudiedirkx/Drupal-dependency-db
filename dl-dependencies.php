<?php

require_once 'inc.config.php';

$sql = "
	SELECT p.project_name, r.*
	FROM releases r
	JOIN projects p ON (p.project_nid = r.project_nid)
	WHERE r.downloaded = 0 AND r.deleted = 0
	ORDER BY r.changed DESC
";
$releases = $db->fetch($sql);

$total = $db->count_rows($sql);

$done = 0;
foreach ($releases as $release) {

	$release_nid = $release->release_nid;
	$dev = $release->dev;

	$update = function($data) use ($db, $release_nid) {
		return $db->update('releases', $data, compact('release_nid'));
	};

	$filename = str_replace(' ', '-', trim($release->title)) . '.zip';
	$url = 'http://ftp.drupal.org/files/projects/' . $filename;
	$local = '/tmp/' . $filename;

	// Download ZIP
	if ( !file_exists($local) || filemtime($local) <= $release->changed ) {
		sleep(1);
		echo "Downloading  $filename\n";
		$content = download($url);
		if ( $content ) {
			file_put_contents($local, $content);
		}
	}
	else {
		echo "(cached)     $filename\n";
	}

	if ( !file_exists($local) ) {
		echo "Download fail - $filename\n";
		$update(array('download_fail' => $url, 'downloaded' => 1));
		continue;
	}

	$zip = new ZipArchive;
	$zip->open($local);

	$db->begin();

	for ( $i = 0; $i < $zip->numFiles; $i++ ) {
		$filename = $zip->getNameIndex($i);
		if ( preg_match('#([^./]+)\.info$#', $filename, $match) ) {
			echo " - $filename\n";
			$module = $match[1];

			$db->insert('modules', array(
				'project_name' => $release->project_name,
				'module_name' => $module,
			));

			$info = $zip->getFromIndex($i);
			$data = drupal_parse_info_format($info);

			if ( isset($data['dependencies']) ) {
				foreach ($data['dependencies'] as $dependency) {
					$dependency = drupal_parse_dependency($dependency);

					if (isset($dependency['name'])) {
						$db->insert('dependencies', array(
							'project_name' => $release->project_name,
							'module_name' => $module,
							'dependency_project_name' => @$dependency['project'] ?: '',
							'dependency_module_name' => $dependency['name'],
							'dependency_versions' => trim(@$dependency['original_version'], "\t )("),
						));
					}
				}
			}
		}
	}

	$update(array('downloaded' => 1));

	$done++;
	echo "Did $done / $total\n";

	$db->commit();
	echo "\n";

}

finish();
