<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine\ORM;

use Doctrine\ORM\Query;
use PDOException;

/**
 * @author Filip Procházka
 */
class SqlException extends \Exception {


	/** @var Query */
	private $query;



	/**
	 * @param PDOException $previous
	 * @param integer $code
	 * @param Query $query
	 * @param string $message
	 */
	public function __construct(PDOException $previous, $code = NULL, Query $query = NULL, $message = "")
	{
		parent::__construct($message ? : $previous->getMessage(), $code, $previous);
		$this->query = $query;
	}



	/**
	 * @return Query|NULL
	 */
	public function getQuery()
	{
		return $this->query;
	}

}