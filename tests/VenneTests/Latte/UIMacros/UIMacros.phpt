<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace VenneTests\Latte\UIMacros;

use Nette\Latte\Compiler;
use Nette\Latte\MacroNode;
use Nette\Latte\MacroTokenizer;
use Nette\Latte\PhpWriter;
use Tester\Assert;
use Venne\Config\Configurator;
use Venne\Latte\Macros\UIMacros;
use Venne\Module\Helpers;

require __DIR__ . '/../../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UIMacrosTest extends \Tester\TestCase
{

	/** @var UIMacros */
	private $macros;


	public function setUp()
	{
		$compiler = new Compiler;
		$helper = new Helpers(array(
			'foo' => array('path' => '/foopath'),
		));

		$this->macros = new UIMacros($compiler);
		$this->macros->injectHelper($helper);
	}


	/**
	 * @return array
	 */
	public function dataServiceNamesAndPresenters()
	{
		return array(
			array('foo.latte', 'foo.latte'),
			array('@fooModule/@layout.latte', '/foopath/Resources/layouts/@layout.latte'),
		);
	}


	/**
	 * @dataProvider dataServiceNamesAndPresenters
	 *
	 * @param string $presenterName
	 * @param string $serviceName
	 */
	public function testBlockPath($path, $expect)
	{
		Assert::same($expect, $this->getMacroExtends($path)->args);
		Assert::same($expect, $this->getMacroIncludeBlock($path)->args);
	}


	public function testBlockPathException()
	{
		$_this = $this;
		Assert::exception(function () use ($_this) {
			$_this->getMacroExtends('@barModule/page.latte')->args;
		}, 'Nette\InvalidArgumentException');
		Assert::exception(function () use ($_this) {
			$_this->getMacroIncludeBlock('@barModule/page.latte')->args;
		}, 'Nette\InvalidArgumentException');
	}


	/**
	 * @param $path
	 * @return MacroNode
	 */
	public function getMacroExtends($path)
	{
		$macroNode = new MacroNode($this->macros, '', $path);
		$phpWriter = new PhpWriter(new MacroTokenizer(''));

		$this->macros->macroExtends($macroNode, $phpWriter);
		return $macroNode;
	}


	/**
	 * @param $path
	 * @return MacroNode
	 */
	public function getMacroIncludeBlock($path)
	{
		$macroNode = new MacroNode($this->macros, '', $path);
		$phpWriter = new PhpWriter(new MacroTokenizer(''));

		$this->macros->macroIncludeBlock($macroNode, $phpWriter);
		return $macroNode;
	}
}

\run(new UIMacrosTest);
