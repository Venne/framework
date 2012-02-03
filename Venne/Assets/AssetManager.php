<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Assets;

use Venne;
use Nette\Object;
use Nette\DI\Container;
use Nette\Caching\Cache;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AssetManager extends Object {


	const PARAM_MEDIA = "media";

	const PARAM_ALIAS = "alias";

	const PARAM_PARNET = "parent";

	const PARAM_TYPE = "type";

	const TYPE_JS = "js";

	const TYPE_CSS = "css";

	/** @var array */
	protected $validParams = array("media", "alias", "parent", "type");

	/** @var \SystemContainer */
	protected $container;

	/** @var array */
	protected $css = array();

	/** @var array */
	protected $js = array();



	/**
	 * Constructor
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}



	/* ---------------------- Add assets ----------------------- */


	/**
	 * Add external javascript file.
	 *
	 * @param type $path
	 * @param array $params
	 */
	public function addJavascript($path, array $params = array())
	{
		$this->addFile($path, $params + array(self::PARAM_TYPE => self::TYPE_JS));
	}



	/**
	 * Add external stylesheet file.
	 *
	 * @param type $path
	 * @param array $params
	 */
	public function addStylesheet($path, array $params = array())
	{
		$this->addFile($path, $params + array(self::PARAM_TYPE => self::TYPE_CSS));
	}



	/**
	 * Add external file.
	 *
	 * @param type $path
	 * @param array $params
	 */
	protected function addFile($path, array $params = array())
	{
		$path = trim($path, "/");

		if (!$this->areParamsValid($params)) {
			throw new \Nette\InvalidArgumentException;
		}

		$absolutePath = $this->getUrl($path);
		$this->{$params[self::PARAM_TYPE]}[$absolutePath] = $params;
	}



	/**
	 * Check params.
	 *
	 * @param array $params
	 * @return boolean
	 */
	protected function areParamsValid($params)
	{
		foreach ($params as $key => $item) {
			if (array_search($key, $this->validParams) === false) {
				return false;
			}
		}
		return true;
	}



	/**
	 * Get all javascript files.
	 *
	 * @return array
	 */
	public function getJavascripts()
	{
		return $this->js;
	}



	/**
	 * Get all stylesheet files.
	 *
	 * @return array
	 */
	public function getStylesheets()
	{
		return $this->css;
	}



	/**
	 * Absolute path for file.
	 *
	 * @param string $path
	 * @return string
	 */
	protected function getUrl($path)
	{
		if (substr($path, 0, 1) == "@") {
			$pos = strpos($path, "/");
			$moduleName = substr($path, 1, $pos - 1);

			return $this->container->parameters["basePath"] . "/resources/" . lcfirst($moduleName) . "/" . substr($path, $pos + 1);
		}

		if(substr($path, 0, 7) == "http://" || substr($path, 0, 8) == "https://"){
			return $path;
		}

		return $this->container->parameters["basePath"] . "/" . $path;
	}

}

