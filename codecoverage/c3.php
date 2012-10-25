<?php
// @codeCoverageIgnoreStart
// @codingStandardsIgnoreStart

/**
 * C3 - Codeception Code Coverage
 *
 * @author tiger
 */

define('C3_CODECOVERAGE_DEBUG', PHP_SAPI === 'cli');

if (C3_CODECOVERAGE_DEBUG)
{
	$_GET['report'] = 1;
	$_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE'] = 'test';
}

if (! array_key_exists('HTTP_X_CODECEPTION_CODECOVERAGE', $_SERVER))
{
	return;
}

// workaround for 'zend_mm_heap corrupted' problem
gc_disable();

define('C3_CODECOVERAGE_MEDIATE_STORAGE', __DIR__ . '/../c3tmp');
define('C3_CODECOVERAGE_PROJECT_ROOT', __DIR__ . '/..');
define('C3_CODECOVERAGE_TESTNAME', $_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE']);

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

if (is_readable($path))
{
	$codeCoverage = new PHP_CodeCoverage;
	$codeCoverage->merge(unserialize(file_get_contents($path)));
}
else
{
	$codeCoverage = new PHP_CodeCoverage;
}

$base = C3_CODECOVERAGE_PROJECT_ROOT . DIRECTORY_SEPARATOR;

if (file_exists($base . 'phpunit.xml'))
{
	$phpunitConfiguration = realpath($base . 'phpunit.xml');
}
elseif (file_exists($base . 'phpunit.xml.dist'))
{
	$phpunitConfiguration = realpath($base . 'phpunit.xml.dist');
}

if (isset($phpunitConfiguration))
{
	$config = PHPUnit_Util_Configuration::getInstance($phpunitConfiguration);
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

if (array_key_exists('report', $_GET))
{
	set_time_limit(0);

	$writer = new PHP_CodeCoverage_Report_Clover();
	$writer->process($codeCoverage, $path . '.clover.xml');

	buildReport($codeCoverage, $path);
}
else
{
	$codeCoverage->start(C3_CODECOVERAGE_TESTNAME);

	register_shutdown_function(function () use ($codeCoverage, $path)
	{
		$codeCoverage->stop();
		file_put_contents($path, serialize($codeCoverage));
	});
}

function buildReport(PHP_CodeCoverage $codeCoverage, $path)
{
	$writer = new PHP_CodeCoverage_Report_HTML();
	$writer->process($codeCoverage, $path . 'html');

	$phar = new PharData($path . '.tar');
	$phar->setSignatureAlgorithm(Phar::SHA1);
	$files = $phar->buildFromDirectory($path . 'html');

	if (in_array('GZ', Phar::getSupportedCompression()))
	{
		$phar = $phar->compress(\Phar::GZ);
		unlink($path . '.tar');
		rename($path . '.tar.gz', $path . '.tar');
	}

	readfile($path . '.tar');

	unlink($path . '.tar');
	array_map('unlink', $files);

	exit;
}

// @codeCoverageIgnoreEnd
// @codingStandardsIgnoreEnd
