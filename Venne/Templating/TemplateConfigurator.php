<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Templating;

use Venne;
use Nette\DI\Container;
use Nette\Templating\Template;

/**
 * @author	 Josef Kříž
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TemplateConfigurator extends \Nette\Object implements ITemplateConfigurator
{

	/** @var \SystemContainer|Container */
	protected $container;

	/** @var array */
	protected $macroFactories;


	/** @var \Nette\Latte\Engine */
	protected $latte;



	/**
	 * @param \Nette\DI\Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}



	/**
	 * @param string $factory
	 */
	public function addFactory($factory)
	{
		$this->macroFactories[] = $factory;
	}



	public function configure(Template $template)
	{
		/* translator */
		$template->setTranslator($this->container->translator);
		$template->registerHelperLoader(array($this->container->venne->helpers, "loader"));
	}



	public function prepareFilters(Template $template)
	{
		$this->latte = new \Nette\Latte\Engine();
		foreach ($this->macroFactories as $factory) {
			if (!$this->container->hasService($factory)) {
				continue;
			}

			$this->container->$factory->invoke($this->latte->getCompiler());
		}

		$template->registerFilter($this->latte);
	}



	/**
	 * Returns Latter parser for the last prepareFilters call.
	 *
	 * @return \Nette\Latte\Engine
	 */
	public function getLatte()
	{
		return $this->latte;
	}

}
