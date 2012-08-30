<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Tests\Module;

use Venne;
use Venne\Testing\TestCase;
use Venne\Module\Helpers;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class HelpersTest extends TestCase
{


	/** @var array */
	protected $parameters = array(
		'foo' => array('path' => '/foo'),
		'bar' => array('path' => '/modules/bar'),
	);


	/**
	 * @return array
	 */
	public static function dataExpandPath()
	{
		return array(
			array('a/b/c', 'a/b/c'),
			array('/a/b/c', '/a/b/c'),
			array('/foo', '@fooModule'),
			array('/foo', '@FooModule'),
			array('/foo/test', '@fooModule/test'),
			array('/foo/test', '@FooModule/test'),
			array('/modules/bar', '@barModule'),
			array('/modules/bar', '@barModule'),
			array('/modules/bar/test', '@barModule/test'),
			array('/modules/bar/test', '@barModule/test'),
		);
	}


	/**
	 * @dataProvider dataExpandPath
	 *
	 * @param string $expect
	 * @param string $path
	 */
	public function testExpandPath($expect, $path)
	{
		$this->assertEquals($expect, Helpers::expandPath($path, $this->parameters));
	}


	/**
	 * @expectedException \Nette\InvalidArgumentException
	 */
	public function testExpandPathException()
	{
		Helpers::expandPath('@cmsModule/foo', $this->parameters);
	}


	/**
	 * @return array
	 */
	public static function dataExpandResource()
	{
		return array(
			array('a/b/c', 'a/b/c'),
			array('/a/b/c', '/a/b/c'),
			array('resources/fooModule', '@fooModule'),
			array('resources/fooModule', '@FooModule'),
			array('resources/fooModule/test', '@fooModule/test'),
			array('resources/fooModule/test', '@FooModule/test'),
			array('resources/barModule', '@barModule'),
			array('resources/barModule', '@barModule'),
			array('resources/barModule/test', '@barModule/test'),
			array('resources/barModule/test', '@barModule/test'),
		);
	}


	/**
	 * @dataProvider dataExpandResource
	 *
	 * @param string $expect
	 * @param string $path
	 */
	public function testExpandResource($expect, $path)
	{
		$this->assertEquals($expect, Helpers::expandResource($path));
	}
}




