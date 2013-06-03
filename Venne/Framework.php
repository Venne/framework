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

final class Framework
{

	/** Venne Framework version identification */
	const NAME = 'Venne Framework',
		VERSION = '2.0.0',
		REVISION = '$WCREV$ released on $WCDATE$';


	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new StaticClassException;
	}
}
