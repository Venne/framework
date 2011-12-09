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
use Doctrine\Common\Collections\Collection;
use Nette;



/**
 * @author Filip Procházka
 */
interface IDao extends IQueryExecutor
{

	const FLUSH = FALSE;
	const NO_FLUSH = TRUE;


	/**
	 * @param object|array|Collection
	 * @param boolean $withoutFlush
	 */
	function save($entity, $withoutFlush = self::FLUSH);


	/**
	 * @param object|array|Collection
	 * @param boolean $withoutFlush
	 */
	function delete($entity, $withoutFlush = self::FLUSH);

}
