<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule;

/**
 * @author Josef Kříž
 */
class DependencyException extends \Exception {


	public $dependencies = array();



	public function __construct($message)
	{
		parent::__construct($message);
	}
	
}

