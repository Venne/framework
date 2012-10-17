<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Tests\Utils;

use Venne;
use Venne\Utils\File;
use Venne\Testing\TestCase;

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
		$this->assertEquals($target, File::getRelativePath($data[0], $data[1]), '/');
	}


	/**
	 * @dataProvider dataGetRelativePath
	 *
	 * @param string $presenterName
	 * @param string $serviceName
	 */
	public function testGetRelativePathWindows($data, $target)
	{
		$this->assertEquals($target, File::getRelativePath($data[0], $data[1]), '\\');
	}


	public function testRmdir()
	{
		$tmpDir = $this->getContext()->parameters['tempDir'];
		umask(0000);

		mkdir($tmpDir . '/foo/bar', 0777, true);
		touch($tmpDir . '/foo/bar/a');
		touch($tmpDir . '/foo/b');

		$this->assertTrue(File::rmdir($tmpDir . '/foo', true));
	}


	public function testCopy()
	{
		$tmpDir = $this->getContext()->parameters['tempDir'];
		umask(0000);

		mkdir($tmpDir . '/foo/bar', 0777, true);
		touch($tmpDir . '/foo/bar/file');

		$this->assertTrue(File::copy($tmpDir . '/foo', $tmpDir . '/foo2'));
		$this->assertTrue(file_exists($tmpDir . '/foo2/bar/file'));

		File::rmdir($tmpDir . '/foo', true);
		File::rmdir($tmpDir . '/foo2', true);
	}
}
