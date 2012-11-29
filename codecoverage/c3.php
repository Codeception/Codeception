<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart

/**
 * C3 - Codeception Code Coverage
 *
 * @author tiger
 */

define('C3_CODECOVERAGE_DEBUG', PHP_SAPI === 'cli');

if (C3_CODECOVERAGE_DEBUG)
{
	$_SERVER['REQUEST_URI'] = 'c3/report/html';
	$_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE'] = 'test';
}

if (! array_key_exists('HTTP_X_CODECEPTION_CODECOVERAGE', $_SERVER))
{
	return;
}

// workaround for 'zend_mm_heap corrupted' problem
gc_disable();

if ((integer)ini_get('memory_limit') < 384)
{
	ini_set('memory_limit', '384M');
}

define('C3_CODECOVERAGE_MEDIATE_STORAGE', __DIR__ . '/../c3tmp');
define('C3_CODECOVERAGE_PROJECT_ROOT', __DIR__ . '/..');
define('C3_CODECOVERAGE_TESTNAME', $_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE']);

if (stream_resolve_include_path('PHPUnit/Autoload.php') !== false)
{
	include_once 'PHPUnit/Autoload.php';
}

if (! class_exists('PHP_CodeCoverage', true))
{
	require __DIR__ . '/../Vendor/Codeception/autoload.php';

	if (! class_exists('PHP_CodeCoverage', true))
	{
		throw new Exception('PHPUnit CodeCoverage not found');
	}
}

if (! is_dir(C3_CODECOVERAGE_MEDIATE_STORAGE))
{
	mkdir(C3_CODECOVERAGE_MEDIATE_STORAGE, 0777, true);
}

// evaluate base path for c3-related files
$path = realpath(C3_CODECOVERAGE_MEDIATE_STORAGE) . DIRECTORY_SEPARATOR . 'codecoverage';

if (strpos($_SERVER['REQUEST_URI'], 'c3/report') !== false)
{
	set_time_limit(0);

	if (file_exists($path))
	{
		if (file_exists($path . '.serialied'))
		{
			unlink($path . '.serialized');
		}

		rename($path, $path . '.serialized');
	}

	$codeCoverage = codeCoverageFactory($path . '.serialized');

	switch (ltrim(strrchr($_SERVER['REQUEST_URI'], '/'), '/'))
	{
		case 'html':
			sendFile(buildHTMLReport($codeCoverage, $path));
			break;

		case 'clover':
			sendFile(buildCloverReport($codeCoverage, $path));
			break;

		case 'serialized':
			sendFile($path . '.serialized');
			break;
	}
}
else
{
	if (file_exists($path . '.serialied'))
	{
		unlink($path . '.serialized');
	}

	$codeCoverage = codeCoverageFactory($path);
	$codeCoverage->start(C3_CODECOVERAGE_TESTNAME);

	register_shutdown_function(function () use ($codeCoverage, $path)
	{
		$codeCoverage->stop();
		file_put_contents($path, serialize($codeCoverage));
	});
}

function codeCoverageFactory($filename)
{
	if (is_readable($filename))
	{
		$codeCoverage = unserialize(file_get_contents($filename));
	}
	else
	{
		$codeCoverage = new PHP_CodeCoverage;
	}

	loadPhpunitConfiguration($codeCoverage);

	return $codeCoverage;
}

function loadPhpunitConfiguration(PHP_CodeCoverage $codeCoverage)
{
	$base = C3_CODECOVERAGE_PROJECT_ROOT . DIRECTORY_SEPARATOR;

	if (file_exists($base . 'phpunit.xml'))
	{
		$pathToConfigFile = realpath($base . 'phpunit.xml');
	}
	elseif (file_exists($base . 'phpunit.xml.dist'))
	{
		$pathToConfigFile = realpath($base . 'phpunit.xml.dist');
	}
	else
	{
		return false;
	}

	$config = PHPUnit_Util_Configuration::getInstance($pathToConfigFile);
	$filterConfiguration = $config->getFilterConfiguration();

	$addUncoveredFilesFromWhitelist = $filterConfiguration['whitelist']['addUncoveredFilesFromWhitelist'];
	$processUncoveredFilesFromWhitelist = $filterConfiguration['whitelist']['processUncoveredFilesFromWhitelist'];

	$codeCoverage->setAddUncoveredFilesFromWhitelist($addUncoveredFilesFromWhitelist);
	$codeCoverage->setProcessUncoveredFilesFromWhitelist($processUncoveredFilesFromWhitelist);

	$codeCoverageFilter = $codeCoverage->filter();

	foreach ($filterConfiguration['blacklist']['include']['directory'] as $dir)
	{
		$codeCoverageFilter->addDirectoryToBlacklist(
			$dir['path'], $dir['suffix'], $dir['prefix'], $dir['group']
		);
	}

	foreach ($filterConfiguration['blacklist']['include']['file'] as $file)
	{
		$codeCoverageFilter->addFileToBlacklist($file);
	}

	foreach ($filterConfiguration['blacklist']['exclude']['directory'] as $dir)
	{
		$codeCoverageFilter->removeDirectoryFromBlacklist(
			$dir['path'], $dir['suffix'], $dir['prefix'], $dir['group']
		);
	}

	foreach ($filterConfiguration['blacklist']['exclude']['file'] as $file)
	{
		$codeCoverageFilter->removeFileFromBlacklist($file);
	}

	foreach ($filterConfiguration['whitelist']['include']['directory'] as $dir)
	{
		$codeCoverageFilter->addDirectoryToWhitelist(
			$dir['path'], $dir['suffix'], $dir['prefix']
		);
	}

	foreach ($filterConfiguration['whitelist']['include']['file'] as $file)
	{
		$codeCoverageFilter->addFileToWhitelist($file);
	}

	foreach ($filterConfiguration['whitelist']['exclude']['directory'] as $dir)
	{
		$codeCoverageFilter->removeDirectoryFromWhitelist(
			$dir['path'], $dir['suffix'], $dir['prefix']
		);
	}

	foreach ($filterConfiguration['whitelist']['exclude']['file'] as $file)
	{
		$codeCoverageFilter->removeFileFromWhitelist($file);
	}
}

function buildHTMLReport(PHP_CodeCoverage $codeCoverage, $path)
{
	$writer = new PHP_CodeCoverage_Report_HTML();
	$writer->process($codeCoverage, $path . 'html');

	if (file_exists($path . '.tar'))
	{
		unlink($path . '.tar');
	}

	$phar = new PharData($path . '.tar');
	$phar->setSignatureAlgorithm(Phar::SHA1);
	$files = $phar->buildFromDirectory($path . 'html');
	array_map('unlink', $files);

	if (in_array('GZ', Phar::getSupportedCompression()))
	{
		if (file_exists($path . '.tar.gz'))
		{
			unlink($path . '.tar.gz');
		}

		$phar->compress(\Phar::GZ);

		// close the file so that we can rename it
		unset($phar);

		unlink($path . '.tar');
		rename($path . '.tar.gz', $path . '.tar');
	}

	return $path . '.tar';
}

function buildCloverReport(PHP_CodeCoverage $codeCoverage, $path)
{
	$writer = new PHP_CodeCoverage_Report_Clover();
	$writer->process($codeCoverage, $path . '.clover.xml');

	return $path . '.clover.xml';
}

function sendFile($filename)
{
	if (! headers_sent())
	{
		readfile($filename);
	}

	exit;
}

// @codeCoverageIgnoreEnd
