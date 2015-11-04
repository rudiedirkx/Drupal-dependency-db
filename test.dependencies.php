<?php

require_once 'inc.config.php';

$info = <<<INFO
name = Forum
description = Provides discussion forums.

dependencies[] = taxonomy
dependencies[] = comment
dependencies[] = bar:foo (>=2.x, >=7.x-1.4)
dependencies[] = bar:foo
dependencies[] = foo (>=7.39)

package = Core
version = VERSION
core = 7.x

files[] = forum.test

configure = admin/structure/forum

stylesheets[all][] = forum.css

; Information added by Drupal.org packaging script on 2015-08-19
version = "7.39"
project = "drupal"
datestamp = "1440020197"
INFO;

echo trim($info) . "\n\n\n\n";



$parsed = drupal_parse_info_format($info);
print_r($parsed);
echo "\n\n\n";



$dependencies = array_map(function($dependency) {
	return drupal_parse_dependency($dependency);
}, $parsed['dependencies']);
print_r($dependencies);
echo "\n\n\n";
