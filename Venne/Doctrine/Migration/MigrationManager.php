<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine\Migration;

use Venne;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class MigrationManager extends Object {


	/** @var array */
	protected $migrations;



	/**
	 * @param \Venne\DI\Container
	 */
	public function __construct(\Venne\DI\Container $context)
	{
		$this->context = $context;
	}



	public function getMigrations($moduleName = NULL, $versionFrom = NULL, $versionTo = NULL)
	{
		if (!$this->migrations) {
			$this->migrations = array();

			foreach ($this->context->parameters["modules"] as $module) {
				$this->context->modules->{$module}->setMigrations($this);
			}
		}

		$ret = array();
		if ($moduleName) {
			if (!$versionTo) {
				$versionTo = PHP_INT_MAX;
			}

			foreach ($this->migrations[$moduleName] as $version => $migration) {
				if (version_compare($version, $versionFrom, '>') && version_compare($version, $versionTo, '<=')) {
					$ret = $migration;
				}
			}
		}

		foreach ($this->migrations as $module) {
			foreach ($module as $version => $migration) {
				$ret = $migration;
			}
		}

		return $ret;
	}



	public function addMigration(BaseMigration $class)
	{
		if (!isset($this->migrations[$class->getModuleName()])) {
			$this->migrations[$class->getModuleName()] = array();
		}

		$this->migrations[$class->getModuleName()][$class->getVersion()] = $class;
		ksort($this->migrations[$class->getModuleName()]);
	}

}