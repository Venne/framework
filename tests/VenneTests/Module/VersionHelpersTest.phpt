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

use Tester\Assert;
use Tester\TestCase;
use Venne\Module\VersionHelpers;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class VersionHelpersTest extends TestCase
{

	/**
	 * @return array
	 */
	public static function dataNormalizeRequire()
	{
		return array(
			array(array(array('>=' => '2.0.0'), array('<=' => '2.0.999999')), '2.0.x'),
			array(array(array('>=' => '2.0.0'), array('<=' => '2.999999.0')), '2.x.0'),
			array(array(array('=' => '2.1.3')), '=2.1.3'),
			array(array(array('>=' => '2.1.3')), '>=2.1.3'),
			array(array(array('!=' => '2.1.3')), '!=2.1.3'),
		);
	}


	/**
	 * @dataProvider dataNormalizeRequire
	 *
	 * @param string $expect
	 * @param string $path
	 */
	public function testNormalizeRequire($expect, $path)
	{
		Assert::equal($expect, VersionHelpers::normalizeRequire($path));
	}
}

\run(new VersionHelpersTest);
