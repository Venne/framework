<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Tests\Application\UI;

use Venne;
use Venne\Testing\TestCase;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PresenterTest extends TestCase
{


	/** @var Presenter */
	protected $presenter;


	public function setUp()
	{
		$this->presenter = new Presenter();

		$widgetManager = new \Venne\Widget\WidgetManager();
		$widgetManager->addWidget('foo', \Nette\Callback::create(function () {
			return new FooControl();
		}));
		$widgetManager->addWidget('bar', \Nette\Callback::create(function () {
			return new BarControl();
		}));

		$this->presenter->injectWidgetManager($widgetManager);
	}


	/**
	 * @return array
	 */
	public function dataServiceNamesAndPresenters()
	{
		return array(
			array('FooPackage:Foo', 'fooPackage.fooPresenter'),
			array('BarPackage:FooFoo', 'barPackage.fooFooPresenter'),
			array('BarPackage:Foo:FooBar', 'barPackage.foo.fooBarPresenter'),
			array('MocksBazPackage:Foo:FooBar', 'mocksBazPackage.foo.fooBarPresenter'),
		);
	}


	public function testCreateComponent()
	{
		$this->assertEquals('Venne\Tests\Application\UI\FooControl', get_class($this->presenter['foo']));
		$this->assertEquals('Venne\Tests\Application\UI\Bar2Control', get_class($this->presenter['bar']));
	}


	/**
	 * @expectedException Nette\InvalidArgumentException
	 */
	public function testCreateComponentException()
	{
		get_class($this->presenter['foo2']);
	}
}

class Presenter extends \Venne\Application\UI\Presenter
{

	public function createComponentBar()
	{
		return new Bar2Control();
	}
}

class FooControl extends \Nette\Application\UI\Control
{

}


class BarControl extends \Nette\Application\UI\Control
{

}

class Bar2Control extends \Nette\Application\UI\Control
{

}


