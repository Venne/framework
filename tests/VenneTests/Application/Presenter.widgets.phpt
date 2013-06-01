<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace VenneTests\Application;

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PresenterTest extends \Tester\TestCase
{


	/** @var Presenter */
	protected $presenter;


	public function setUp()
	{
		$this->presenter = new BasePresenter();

		$widgetManager = new \Venne\Widget\WidgetManager();
		$widgetManager->addWidget('foo', \Nette\Callback::create(function () {
			return new FooControl();
		}));
		$widgetManager->addWidget('bar', \Nette\Callback::create(function () {
			return new BarControl();
		}));

		$this->presenter->injectWidgetManager($widgetManager);
	}


	public function testCreateComponent()
	{
		Assert::equal('VenneTests\Application\FooControl', get_class($this->presenter['foo']));
		Assert::equal('VenneTests\Application\Bar2Control', get_class($this->presenter['bar']));
	}


	public function testCreateComponentException()
	{
		$presenter = $this->presenter;
		Assert::exception(function () use ($presenter) {
			get_class($presenter['foo2']);
		}, 'Nette\InvalidArgumentException');
	}
}

class BasePresenter extends \Venne\Application\UI\Presenter
{

	protected function createComponentBar()
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

\run(new PresenterTest);
