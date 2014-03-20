<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne;

use Nette\InvalidStateException;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class BaseFactory extends Object
{


	/**
	 * @throws \Nette\InvalidStateException
	 */
	public function __invoke()
	{
		if (!method_exists($this, 'invoke')) {
			throw new InvalidStateException("Method 'invoke' is not implemented.");
		}

		return call_user_func_array(array($this, 'invoke'), func_get_args());
	}

}

