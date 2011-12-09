<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine\Mapping\FieldTypes;

use Venne;
use Venne\Doctrine\Mapping;
use Nette;



/**
 * @author Filip Procházka
 */
class IntegerType extends Nette\Object implements Mapping\IFieldType
{

	/**
	 * @param integer $value
	 * @param integer $current
	 * @return integer
	 */
	public function load($value, $current)
	{
		return $value;
	}



	/**
	 * @param integer $value
	 * @return integer
	 */
	public function save($value)
	{
		return $value;
	}

}