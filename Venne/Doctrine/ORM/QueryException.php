<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine\ORM;

use Doctrine;
use Venne;
use Nette;

/**
 * @author Filip Procházka
 */
class QueryException extends \Exception {


	/** @var Doctrine\ORM\Query */
	private $query;



	/**
	 * @param string $message
	 * @param Doctrine\ORM\Query $query
	 * @param \Exception $previous
	 */
	public function __construct($message = "", Doctrine\ORM\Query $query, \Exception $previous = NULL)
	{
		parent::__construct($message, NULL, $previous);

		$this->query = $query;
	}



	/**
	 * @return Doctrine\ORM\Query
	 */
	public function getQuery()
	{
		return $this->query;
	}

}