<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
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
class BigIntType extends Nette\Object implements Mapping\IFieldType
{

	/**
	 * @param int $value
	 * @param int $current
	 * @return int
	 */
	public function load($value, $current)
	{
		return $value;
	}



	/**
	 * @param int $value
	 * @return int
	 */
	public function save($value)
	{
		return $value;
	}

}