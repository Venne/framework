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
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
interface IQueryable
{

	/**
	 * Create a new QueryBuilder instance that is prepopulated for this entity name
	 *
	 * @param string|NULL $alias
	 * @return Doctrine\ORM\QueryBuilder|Doctrine\CouchDB\View\AbstractQuery
	 */
	function createQueryBuilder($alias = NULL);


	/**
	 * @param string|NULL $dql
	 * @return Doctrine\ORM\Query
	 */
	function createQuery($dql = NULL);

}