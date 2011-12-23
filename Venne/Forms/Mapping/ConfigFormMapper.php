<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
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
class ConfigFormMapper extends \Nette\Object {


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
		$this->root = $this->root = explode(".", $root);
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
	public function load()
	{
		$valuesOld = $this->loadConfig();
		$values = array();

		foreach ($this->container->getControls() as $control) {
			if (key_exists($control->name, $valuesOld)) {
				$values[$control->name] = $control->value;
			}
		}

		$this->saveConfig($values);
	}



	/**
	 * @return array
	 */
	public function save()
	{
		$values = $this->loadConfig();

		foreach ($this->container->getControls() as $control) {
			if (key_exists($control->name, $values)) {
				$control->value = $values[$control->name];
			}
		}
	}

}