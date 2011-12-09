<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\SystemModule;

use Venne\ORM\Column;
use Nette\Utils\Html;
use Venne\Forms\Form;

/**
 * @author Josef Kříž
 */
class SystemDatabaseForm extends \Venne\Forms\EditForm {


	/** @var bool */
	protected $testConnection;

	/** @var bool */
	protected $showTestConnection;



	public function __construct($showTestConnection = true, $testConnection = true)
	{
		$this->testConnection = $testConnection;
		$this->showTestConnection = $showTestConnection;
		parent::__construct();
	}



	public function startup()
	{
		parent::startup();

		$this->addGroup();
		if ($this->showTestConnection)
			$this->addCheckbox("test", "Test connection");
		if ($this->testConnection && $this->showTestConnection)
			$this["test"]->setDefaultValue(true);
		$this->addSelect("driver", "Driver", array("pdo_mysql" => "pdo_mysql", "pdo_pgsql" => "pdo_pgsql"));
		$this->addText("host", "Host");
		$this->addText("user", "User name");
		$this->addPassword("password", "Password");
		$this->addText("dbname", "Database");

		$this["host"]
				->addRule(self::FILLED, 'Enter host');
		$this["user"]
				->addRule(self::FILLED, 'Enter user name');
		$this["dbname"]
				->addRule(self::FILLED, 'Enter database name');
	}



	public function load()
	{
		$config = $this->presenter->context->configManager[$this->presenter->mode]["database"];
		$this->setDefaults($config);
	}



	protected function handleError()
	{
		
	}



	public function save()
	{
		parent::save();
		$values = $this->getValues();

		$config = $this->presenter->context->configManager;

		/*
		 * Test
		 */
		if ((!$this->showTestConnection && $this->testConnection) || ($this->showTestConnection && $values["test"])) {
			set_error_handler(array($this, 'handleError'));
			try {
				$db = new \Nette\Database\Connection(substr($values["driver"], 4) . ':host=' . $values["host"] . ';dbname=' . $values["dbname"], $values["user"], $values["password"]);
			} catch (\PDOException $e) {
				$this->getPresenter()->flashMessage("Cannot connect to database " . $e->getMessage(), "warning");
				return false;
			}
			restore_error_handler();
		}

		$config[$this->presenter->mode]["database"]["host"] = $values["host"];
		$config[$this->presenter->mode]["database"]["driver"] = $values["driver"];
		$config[$this->presenter->mode]["database"]["user"] = $values["user"];
		$config[$this->presenter->mode]["database"]["password"] = $values["password"];
		$config[$this->presenter->mode]["database"]["dbname"] = $values["dbname"];
		$config->save();
	}

}
