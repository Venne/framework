<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Tools;

use Nette\MemberAccessException;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Objects extends Object
{

	/**
	 * @param $object
	 * @param $propertyName
	 * @return bool
	 */
	public static function hasProperty($object, $propertyName)
	{
		if (is_array($object) || $object instanceof \ArrayAccess || $object instanceof \ArrayObject) {
			return TRUE;
		} elseif (is_object($object)) {
			if (method_exists($object, $method = 'get' . ucfirst($propertyName))) {
				return TRUE;
			} elseif (isset($object->$propertyName)) {
				return TRUE;
			} elseif (method_exists($object, $method = 'is' . ucfirst($propertyName))) {
				return TRUE;
			}
		}

		return FALSE;
	}


	/**
	 * @param $object
	 * @param $propertyName
	 * @param bool $need
	 * @return mixed
	 * @throws MemberAccessException
	 */
	public static function getProperty($object, $propertyName, $need = TRUE)
	{
		if (is_array($object) || $object instanceof \ArrayAccess || $object instanceof \ArrayObject) {
			return $object[$propertyName];
		} elseif (is_object($object)) {
			if (method_exists($object, $method = 'get' . ucfirst($propertyName))) {
				return $object->$method();
			} elseif (isset($object->$propertyName)) {
				return $object->$propertyName;
			} elseif (method_exists($object, $method = 'is' . ucfirst($propertyName))) {
				return $object->$method();
			}
		}

		if ($need) {
			throw new MemberAccessException("Given" . (is_object($object) ? " entity " . get_class($object) : " array") . " has no public parameter or accesor named '" . $propertyName . "', or doesn't exists.");
		}
	}


	/**
	 * @param $object
	 * @param $propertyName
	 * @param $value
	 * @param bool $exceptionOnInvalid
	 * @throws MemberAccessException
	 */
	public static function setProperty($object, $propertyName, $value, $exceptionOnInvalid = TRUE)
	{
		if (property_exists($object, $propertyName)) {
			$object->$propertyName = $value;
		} elseif (method_exists($object, $method = "set" . ucfirst($propertyName))) {
			$object->$method($value);
		} elseif (method_exists($object, $method = "add" . ucfirst($propertyName))) {
			$object->$method($value);
		} elseif ($exceptionOnInvalid) {
			throw new MemberAccessException("Property with name '$propertyName' is not publicly writable, or doesn't exists.");
		}
	}
}

