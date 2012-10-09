<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Config\Extensions;

use Venne;
use Nette\DI\ContainerBuilder;
use Nette\Config\CompilerExtension;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ConsoleExtension extends CompilerExtension
{

	/** @var string|NULL */
	protected $consoleEntityManager;


	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		// console
		$container->addDefinition($this->prefix('helperSet'))
			->setClass('Symfony\Component\Console\Helper\HelperSet');

		$container->addDefinition($this->prefix('console'))
			->setClass('Symfony\Component\Console\Application')
			->addSetup('setHelperSet', array('@console.helperSet'))
			->addSetup('setCatchExceptions', !$container->parameters['debugMode']);
	}


	public function beforeCompile()
	{
		$container = $this->getContainerBuilder();

		$this->registerCommands();
		$this->registerHelpers();
	}


	protected function registerHelpers()
	{
		$container = $this->getContainerBuilder();
		$definition = $container->getDefinition($this->prefix('helperSet'));

		foreach ($container->findByTag("commandHelper") as $item => $meta) {
			$definition->addSetup("set", array("@{$item}", $meta));
		}
	}


	protected function registerCommands()
	{
		$container = $this->getContainerBuilder();
		$console = $container->getDefinition($this->prefix('console'));

		foreach ($container->findByTag("command") as $item => $meta) {
			$console->addSetup("add", "@{$item}");
		}
	}
}

