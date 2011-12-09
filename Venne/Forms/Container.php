<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Forms;

/**
 * @author     Josef Kříž
 */
class Container extends \Nette\Forms\Container {


	/** @var \Nette\DI\Container */
	protected $container;


	/**
	 * @param \Nette\DI\Container
	 */
	public function __construct(\Nette\DI\Container $container)
	{
		$this->container = $container;
	}


	/**
	 * @param string $name
	 * @param string $label
	 * @param array $suggest
	 * @return \Venne\Forms\Controls\TagInput provides fluent interface
	 */
	public function addTag($name, $label = NULL)
	{
		$this[$name] = new \Venne\Forms\Controls\TagInput($label);
		$this[$name]->setRenderName('tagInputSuggest' . ucfirst($name));

		$this->container->application->presenter->addJs("/js/jquery-1.6.min.js");
		$this->container->application->presenter->addJs("/js/Forms/Controls/tagInput.js");
		$this->container->application->presenter->addCss("/css/Forms/Controls/tagInput.css");

		return $this[$name];
	}

}
