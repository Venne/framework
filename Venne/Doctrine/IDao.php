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
