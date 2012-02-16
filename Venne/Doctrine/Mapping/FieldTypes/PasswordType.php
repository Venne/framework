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
class PasswordType extends Nette\Object implements Mapping\IFieldType
{

	/**
	 * @param string $value
	 * @param Venne\Types\Password $current
	 * @return Venne\Types\Password
	 */
	public function load($value, $current)
	{
		if ($value) {
			$password = new Venne\Types\Password($current->getHash());
			$password->setSalt($current->getSalt());
			$password->setPassword($value);

			return $password;
		}

		return $current ? : new Venne\Types\Password();
	}



	/**
	 * @param string $value
	 * @return Venne\Types\Password
	 */
	public function save($value)
	{
		return NULL;
	}

}