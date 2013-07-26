<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Module;

use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BaseModule extends Object implements IModule
{

	/** @var string */
	protected $name;

	/** @var string */
	protected $version;

	/** @var string */
	protected $description;

	/** @var array */
	protected $keywords = array();

	/** @var array */
	protected $license = array();

	/** @var array */
	protected $authors = array();


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}


	/**
	 * @return array
	 */
	public function getKeywords()
	{
		return $this->keywords;
	}


	/**
	 * @return array
	 */
	public function getLicense()
	{
		return $this->license;
	}


	/**
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}


	/**
	 * @return array
	 */
	public function getAuthors()
	{
		return $this->authors;
	}


	/**
	 * @return array
	 */
	public function getAutoload()
	{
		return array();
	}


	/**
	 * @return array
	 */
	public function getRequire()
	{
		return array();
	}


	/**
	 * @return array
	 */
	public function getConfiguration()
	{
		return array();
	}


	/**
	 * @return array
	 */
	public function getExtra()
	{
		return array();
	}


	/**
	 * @return string
	 */
	public function getPath()
	{
		return dirname($this->getReflection()->getFileName());
	}


	/**
	 * @return string
	 */
	public function getRelativePublicPath()
	{
		return '/Resources/public';
	}


	/**
	 * @return string
	 */
	public function getClassName()
	{
		return get_class($this);
	}


	/**
	 * @return array
	 */
	public function getInstallers()
	{
		return array('Venne\Module\Installers\BaseInstaller');
	}
}

