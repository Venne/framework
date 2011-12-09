<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\ContentExtension;

use Venne;
use Venne\Application\UI\Element;

/**
 * @author Josef Kříž
 */
class BaseElement extends Element {

	protected $moduleName;
	protected $moduleItemId;

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL, $element, $key = NULL)
	{
		parent::__construct($parent, $name, $element, $key);
		$key = explode("SEP",$this->key);
		$this->moduleName = $key[1];
		$this->moduleItemId = $key[2];
	}

}

