<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Templating;

/**
 * @author Josef Kříž
 */
interface ITheme {



	public function __construct(\Nette\DI\Container $context);



	public function getName();



	public function getVersion();



	public function getDescription();



	public function getDependencies();



	/**
	 * @param \Nette\Templating\ITemplate $template
	 */
	public function setTemplate(\Nette\Templating\ITemplate $template);



	/**
	 * @param \Nette\Latte\Parser $parser
	 */
	public function setMacros(\Nette\Latte\Parser $parser);
}

