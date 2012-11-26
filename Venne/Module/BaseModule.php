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

use Venne;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BaseModule extends Object implements IModule
{


	protected $name;

	protected $description;

	protected $keywords;

	protected $version;

	protected $license = array();

	protected $authors = array();


	public function getName()
	{
		return $this->name;
	}


	public function getDescription()
	{
		return $this->description;
	}


	public function getKeywords()
	{
		return $this->keywords;
	}


	public function getLicense()
	{
		return $this->license;
	}


	public function getVersion()
	{
		return $this->version;
	}


	public function getAuthors()
	{
		return $this->authors;
	}


	public function getAutoload()
	{
		return array();
	}


	public function getRequire()
	{
		return array();
	}


	public function getConfiguration()
	{
		return array();
	}


	public function getExtra()
	{
		return array();
	}


	public function getPath()
	{
		return dirname($this->getReflection()->getFileName());
	}


	public function getClassName()
	{
		return get_class($this);
	}


	public function getInstallers()
	{
		return array('Venne\Module\Installers\BaseInstaller');
	}
}

