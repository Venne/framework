<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Application\UI;

use Venne;

/**
 * Widget for Venne:CMS
 *
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class WidgetMultiplier extends \Nette\Application\UI\Multiplier {


	public function render($param = NULL, $type = NULL)
	{
		$this[0]->render($param, $type);
	}


	public function __call($name, $args)
	{
		if(substr($name, 0, 6) == "render"){
			return call_user_func_array(array($this[0], $name), $args);
		}
	}

}

