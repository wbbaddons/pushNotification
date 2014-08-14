#!/usr/bin/env php
<?php
namespace de\bisaboard\bisaboard;
/**
 * Builds be.bastelstu.max.pushNotification
 *
 * @author Tim Düsterhus, edited by Maximilian Mader
 * @copyright 2012-2013 Tim Düsterhus
 * @license BSD 3-Clause License <http://opensource.org/licenses/BSD-3-Clause>
 * @package be.bastelstu.wcf.nodePush
 */
$packageXML = file_get_contents('package.xml');
preg_match('/<version>(.*?)<\/version>/', $packageXML, $matches);
echo "Building be.bastelstu.max.pushNotification $matches[1]\n";
echo str_repeat("=", strlen("Building be.bastelstu.max.pushNotification $matches[1]"))."\n";

echo <<<EOT
Cleaning up
-----------

EOT;
	if (file_exists('package.xml.old')) {
		file_put_contents('package.xml', file_get_contents('package.xml.old'));
		unlink('package.xml.old');
	}
	if (file_exists('file.tar')) unlink('file.tar');
	if (file_exists('acptemplates.tar')) unlink('acptemplates.tars');
	if (file_exists('be.bastelstu.max.pushNotification')) unlink('be.bastelstu.max.pushNotification');
echo <<<EOT

Checking PHP for Syntax Errors
------------------------------

EOT;
	chdir('file');
	$check = null;
	$check = function ($folder) use (&$check) {
		if (is_file($folder)) {
			if (substr($folder, -4) === '.php') {
				passthru('php -l '.escapeshellarg($folder), $code);
				if ($code != 0) exit($code);
			}
			
			return;
		}
		$file = glob($folder.'/*');
		foreach ($file as $file) {
			$check($file);
		}
	};
	$check('.');
echo <<<EOT

Building file.tar
------------------

EOT;
	passthru('tar cvf ../file.tar --exclude=.git -- *', $code);
	if ($code != 0) exit($code);
echo <<<EOT

Building acptemplates.tar
-------------------------

EOT;
	if (is_dir('../acptemplates')) {
		chdir('../acptemplates');
		passthru('tar cvf ../acptemplates.tar *', $code);
		if ($code != 0) exit($code);
	}
	else {
		echo 'No ACP templates found.';
	}

echo <<<EOT

Building be.bastelstu.max.pushNotification
------------------------------------------

EOT;
	chdir('..');
	file_put_contents('package.xml.old', file_get_contents('package.xml'));
	file_put_contents('package.xml', preg_replace('~<date>\d{4}-\d{2}-\d{2}</date>~', '<date>'.date('Y-m-d').'</date>', file_get_contents('package.xml')));
	passthru('tar cvf be.bastelstu.max.pushNotification.tar --exclude=*.old --exclude=file --exclude=contrib -- *', $code);
	if (file_exists('package.xml.old')) {
		file_put_contents('package.xml', file_get_contents('package.xml.old'));
		unlink('package.xml.old');
	}
	if ($code != 0) exit($code);

if (file_exists('file.tar')) unlink('file.tar');
if (file_exists('acptemplates.tar')) unlink('acptemplates.tar');
