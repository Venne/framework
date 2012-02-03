<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine;

use Venne;
use Nette;


/**
 * @author Filip Procházka
 */
interface IQueryExecutor {

	/**
	 * @param IQueryObject $queryObject
	 * @return integer
	 */
	function count(IQueryObject $queryObject);



	/**
	 * @param IQueryObject $queryObject
	 * @return array
	 */
	function fetch(IQueryObject $queryObject);



	/**
	 * @param IQueryObject $queryObject
	 * @return object
	 */
	function fetchOne(IQueryObject $queryObject);

}