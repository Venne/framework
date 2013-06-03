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

use Nette\Callback;
use Nette\DI\Container;
use Nette\Latte\Engine;
use Nette\Object;
use Nette\Templating\Template;

/**
 * @author     Josef Kříž
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TemplateConfigurator extends Object implements ITemplateConfigurator
{

	/** @var \SystemContainer|Container */
	protected $container;

	/** @var array */
	protected $macroFactories = array();

	/** @var Engine */
	protected $latte;

	/** @var Callback */
	protected $latteFactory;


	/**
	 * @param Container $container
	 */
	public function __construct(Container $container, Callback $latteFactory)
	{
		$this->container = $container;
		$this->latteFactory = $latteFactory;
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
		// translator
		if (($translator = $this->container->getByType('Nette\Localization\ITranslator', FALSE)) !== NULL) {
			$template->setTranslator($translator);
		}
		$template->registerHelperLoader(array($this->container->venne->helpers, "loader"));
	}


	public function prepareFilters(Template $template)
	{
		$this->latte = $this->latteFactory->invoke();
		foreach ($this->macroFactories as $factory) {
			$this->container->{Container::getMethodName($factory, FALSE)}($this->latte->getCompiler());
		}
		$template->registerFilter($this->latte);
	}


	/**
	 * Returns Latter parser for the last prepareFilters call.
	 *
	 * @return Engine
	 */
	public function getLatte()
	{
		return $this->latte;
	}
}
