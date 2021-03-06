<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "interface"
 *
 * Auto generated by Extension Builder 2015-03-30
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'JSON API',
	'description' => 'Provides an Interface for data output in JSON format. Support for pages, files (FAL) and any extbase model.',
	'category' => 'services',
	'author' => 'Ben Walch',
	'author_email' => 'ben.walch@world-direct.at',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0-7.3.99',
			'routing' => '0.2.0-0.2.99'
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);