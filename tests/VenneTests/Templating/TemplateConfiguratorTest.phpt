<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace VenneTests\Templating;

use Tester\Assert;
use Tester\TestCase;
use Venne\Config\Configurator;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TemplateConfiguratorTest extends TestCase
{


	/** @var \Venne\Templating\TemplateConfigurator */
	protected $configurator;


	public function setUp()
	{
		$container = new Container;
		$container->addService("venne.helpers", new \Venne\Templating\Helpers($container));
		$container->addService('venne', new \Nette\DI\NestedAccessor($container, 'venne'));

		$latteFactory = new \Nette\Callback(function () {
			return new \Nette\Latte\Engine();
		});

		$this->configurator = new \Venne\Templating\TemplateConfigurator($container, $latteFactory);
		$this->configurator->addFactory("fooMacro");
		$this->configurator->addFactory("barMacro");
	}


	public function testConfigure()
	{
		$template = new \Nette\Templating\FileTemplate();

		$this->configurator->configure($template);

		$callbacks = $template->getHelperLoaders();
		Assert::equal(1, count($callbacks));
		foreach ($callbacks as $callback) {
			Assert::type('Nette\Callback', $callback);
		}
	}


	public function testPrepareFilters()
	{
		$template = new \Nette\Templating\FileTemplate();

		$this->configurator->prepareFilters($template);

		$callbacks = $template->getFilters();
		Assert::equal(1, count($callbacks));
		foreach ($callbacks as $callback) {
			Assert::type('Nette\Callback', $callback);
		}
	}
}

class Container extends \Nette\DI\Container
{


	public function createFooMacro($engine)
	{
		return new \Nette\Latte\Macros\UIMacros($engine);
	}


	public function createBarMacro($engine)
	{
		return new \Nette\Latte\Macros\UIMacros($engine);
	}
}

\run(new \VenneTests\Templating\TemplateConfiguratorTest);
