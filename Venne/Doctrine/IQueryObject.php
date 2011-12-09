<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine;

use Doctrine;
use Venne;
use Venne\Doctrine\IQueryable;
use Nette;



/**
 * @author Filip Procházka
 */
interface IQueryObject
{

	/**
	 * @param IQueryable $repository
	 * @return integer
	 */
	function count(IQueryable $repository);


	/**
	 * @param IQueryable $repository
	 * @return mixed
	 */
	function fetch(IQueryable $repository);


	/**
	 * @param IQueryable $repository
	 * @return object
	 */
	function fetchOne(IQueryable $repository);

}