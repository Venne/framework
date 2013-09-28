<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace VenneTests\Module;

use Nette\Caching\Storages\DevNullStorage;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;
use Venne\Caching\CacheManager;
use Venne\Module\ModuleManager;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ModuleManagerTest extends TestCase
{

	/** @var ModuleManager */
	protected $moduleManager;

	protected function setUp()
	{
		parent::setUp();

		$this->moduleManager = new ModuleManager(
			new Container(),
			new CacheManager(new DevNullStorage(), '', ''),
			'/project/vendor',
			__DIR__ ,
			'/project/app/modules'
		);
	}


	/**
	 * @return array
	 */
	public static function dataGetFormattedPath()
	{
		return array(
			array('/foo/bar', '/foo/bar'),
			array('%libsDir%/foo', '/project/vendor/foo'),
			array('%modulesDir%/foo', '/project/app/modules/foo'),
		);
	}


	/**
	 * @dataProvider dataGetFormattedPath
	 *
	 * @param string $expect
	 * @param string $path
	 */
	public function testGetFormattedPath($expect, $path)
	{
		$class = new \ReflectionClass('Venne\Module\ModuleManager');
		$method = $class->getMethod ('getFormattedPath');
		$method->setAccessible(true);

		Assert::equal($expect, $method->invoke($this->moduleManager, $path));
	}


}

\run(new ModuleManagerTest);
