<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Forms\Mapping;

use Doctrine;
use Doctrine\ORM\Mapping\ClassMetadata;
use Venne;
use Doctrine\Common\Persistence\ObjectManager;
use Nette;
use Nette\ComponentModel\IComponent;
use SplObjectStorage;
use Nette\Config\Adapters\NeonAdapter;

/**
 * @author Filip Procházka
 */
class ConfigFormMapper extends \Nette\Object
{


	/** @var string */
	protected $fileName;

	/** @var Nette\Config\Adapters\NeonAdapter */
	protected $adapter;

	/** @var string */
	protected $root;

	/** @var array */
	protected $data;

	/** @var Nette\Forms\Container */
	protected $container;



	/**
	 * @param string $fileName
	 * @param string $root
	 */
	public function __construct($fileName, $root = "")
	{
		$this->fileName = $fileName;
		$this->root = explode(".", $root);
		$this->adapter = new NeonAdapter;
	}



	public function getRoot()
	{
		return implode(".", $this->root);
	}



	public function setRoot($root)
	{
		$this->root = $this->root = $root ? explode(".", $root) : array();
	}



	public function getContainer()
	{
		return $this->container;
	}



	public function setContainer($container)
	{
		$this->container = $container;
	}



	protected function loadConfig()
	{
		$this->data = $this->adapter->load($this->fileName);
		$data = $this->data;

		foreach ($this->root as $item) {
			$data = $data[$item];
		}

		return $data;
	}



	protected function saveConfig($values)
	{
		$this->loadConfig();
		$data = & $this->data;

		foreach ($this->root as $item) {
			$data = & $data[$item];
		}

		$data = ($values + $data);

		file_put_contents($this->fileName, $this->adapter->dump($this->data));
	}



	/**
	 * @return array
	 */
	public function save($container = NULL, $rec = false, $values = NULL)
	{
		$container = $container ? : $this->container;

		if (!$rec) {
			$values = $this->loadConfig();
		} else {
			$values = $values[$rec];
		}

		foreach ($container->getComponents() as $key => $control) {
			if (!Nette\Utils\Strings::startsWith($key, "_")) {
				if ($control instanceof \Nette\Forms\Container) {
					$values[$key] = $this->save($control, $key, $values);
				} else if ($control instanceof \Nette\Forms\IControl) {
					$values[$key] = $control->value;
				}
			}
		}

		if (!$rec) {
			$this->saveConfig($values);
		} else {
			return $values;
		}
	}



	/**
	 * @return array
	 */
	public function load($container = NULL, $rec = false, $values = NULL)
	{
		$container = $container ? : $this->container;

		if (!$rec) {
			$values = $this->loadConfig();
		}

		foreach ($container->getComponents() as $key => $control) {
			if (!Nette\Utils\Strings::startsWith($key, "_")) {
				if ($control instanceof \Nette\Forms\Container) {
					$this->load($control, true, $values[$key]);
				} else if ($control instanceof \Nette\Forms\IControl) {
					$control->value = isset($values[$key]) ? $values[$key] : "";
				}
			}
		}
	}

}