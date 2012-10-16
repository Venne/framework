<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Config;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Loader extends \Nette\Config\Loader
{


	/** @var array */
	protected $parameters;


	/**
	 * @param $file
	 * @param null $section
	 * @return array
	 */
	public function load($file, $section = NULL)
	{
		$file = \Nette\DI\Helpers::expand($file, $this->parameters);

		if (substr(PHP_OS, 0, 3) === 'WIN') {
			if ($pos = strpos($file, ':\\')) {
				$file = substr($file, $pos - 1);
			}
		} else {
			if ($pos = strpos($file, '//')) {
				$file = substr($file, $pos + 1);
			}
		}

		return parent::load($file, $section);
	}


	/**
	 * @param array $parameters
	 */
	public function setParameters($parameters)
	{
		$this->parameters = $parameters;
	}


	/**
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}
}
