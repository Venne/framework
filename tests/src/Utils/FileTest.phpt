<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace VenneTests\Utils;

use Doctrine\Tests\Common\Persistence\Mapping\TestEntity;
use Tester\Assert;
use Tester\TestCase;
use Venne\Utils\File;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FileTest extends TestCase
{


	/**
	 * @return array
	 */
	public function dataGetRelativePath()
	{
		return array(
			array(array('/foo/a/', '/foo/b'), '../b'),
			array(array('/foo/a', '/foo/b/'), '../b'),
			array(array('/foo/a/', '/foo/b/'), '../b'),
			array(array('/foo/a', '/foo/b'), '../b'),
		);
	}


	/**
	 * @return array
	 */
	public function dataGetRelativePathWindows()
	{
		return array(
			array(array('C:\\foo\\a\\', 'C:\\foo\\b'), '..\\b'),
			array(array('C:\\foo\\a', 'C:\\foo\\b\\'), '..\\b'),
			array(array('C:\\foo\\a\\', 'C:\\foo\\b\\'), '..\\b'),
			array(array('C:\\foo\\a', 'C:\\foo\\b'), '..\\b'),
		);
	}


	/**
	 * @dataProvider dataGetRelativePath
	 *
	 * @param string $presenterName
	 * @param string $serviceName
	 */
	public function testGetRelativePathLinux($data, $target)
	{
		Assert::equal($target, File::getRelativePath($data[0], $data[1]), '/');
	}


	/**
	 * @dataProvider dataGetRelativePath
	 *
	 * @param string $presenterName
	 * @param string $serviceName
	 */
	public function testGetRelativePathWindows($data, $target)
	{
		Assert::equal($target, File::getRelativePath($data[0], $data[1]), '\\');
	}


	public function testRmdir()
	{
		umask(0000);

		mkdir(TEMP_DIR . '/foo/bar', 0777, true);
		touch(TEMP_DIR . '/foo/bar/a');
		touch(TEMP_DIR . '/foo/b');

		Assert::true(File::rmdir(TEMP_DIR . '/foo', true));
	}


	public function testCopy()
	{
		umask(0000);

		mkdir(TEMP_DIR . '/foo/bar', 0777, true);
		touch(TEMP_DIR . '/foo/bar/file');

		Assert::true(File::copy(TEMP_DIR . '/foo', TEMP_DIR . '/foo2'));
		Assert::true(file_exists(TEMP_DIR . '/foo2/bar/file'));

		File::rmdir(TEMP_DIR . '/foo', true);
		File::rmdir(TEMP_DIR . '/foo2', true);
	}
}

$testCache = new FileTest;
$testCache->run();
