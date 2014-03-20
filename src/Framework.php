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

use Nette\StaticClassException;

/**
 * @author     Josef Kříž
 */
final class Framework
{

	/** Venne Framework version identification */
	const NAME = 'Venne Framework',
		VERSION = '2.1.0',
		VERSION_ID = 20101,
		REVISION = '$WCREV$ released on $WCDATE$';


	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new StaticClassException;
	}
}
