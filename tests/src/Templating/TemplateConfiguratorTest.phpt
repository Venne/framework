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

use Nette\DI\Container;
use Nette\Latte\Engine;
use Nette\Localization\ITranslator;
use Tester\Assert;
use Tester\TestCase;
use Venne\Templating\Helpers;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TemplateConfiguratorTest extends TestCase
{


	/** @var \Venne\Templating\TemplateConfigurator */
	private $configurator;

	/** @var Engine */
	private $engine;

	/** @var ITranslator */
	private $translator;

	/** @var Helpers */
	private $helpers;


	public function setUp()
	{
		$container = new Container;
		$this->engine = new Engine;
		$this->translator = new Translator;
		$this->helpers = new Helpers($container);
		$this->configurator = new \Venne\Templating\TemplateConfigurator($container, $this->engine, $this->helpers, $this->translator);
	}


	public function testConfigure()
	{
		$template = new \Nette\Templating\FileTemplate();
		$this->configurator->configure($template);
		$helpers = $template->getHelpers();
		$helperLoaders = $template->getHelperLoaders();

		Assert::same($this->translator, $helpers['translate'][0]);
		Assert::equal(1, count($helperLoaders));
		foreach ($helperLoaders as $callback) {
			Assert::same($this->helpers, $callback[0]);
		}
	}


	public function testPrepareFilters()
	{
		$template = new \Nette\Templating\FileTemplate();
		$this->configurator->prepareFilters($template);
		$filters = $template->getFilters();

		Assert::equal(1, count($filters));
		Assert::same($this->engine, $filters[0]);
	}
}

class Translator implements ITranslator
{

	function translate($message, $count = NULL)
	{

	}

}

$testCache = new TemplateConfiguratorTest;
$testCache->run();
