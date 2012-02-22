<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\Forms;

use Venne\ORM\Column;
use Nette\Utils\Html;
use Venne\Forms\Form;
use Venne\Forms\Mapping\ConfigFormMapper;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SystemDatabaseForm extends \Venne\Forms\ConfigForm
{


	/** @var bool */
	protected $testConnection = true;

	/** @var bool */
	protected $showTestConnection = true;


	protected $drivers = array(
		'pdo_mysql',
		'pdo_sqlite',
		'pdo_pgsql',
		'pdo_oci',
		'oci8',
		'pdo_sqlsrv',
	);



	public function getTestConnection()
	{
		return $this->testConnection;
	}



	public function setTestConnection($testConnection)
	{
		$this->testConnection = $testConnection;
	}



	public function getShowTestConnection()
	{
		return $this->showTestConnection;
	}



	public function setShowTestConnection($showTestConnection)
	{
		$this->showTestConnection = $showTestConnection;
	}



	public function startup()
	{
		parent::startup();

		$this->addGroup("Base settings");
		if ($this->showTestConnection) $this->addCheckbox("test", "Test connection");
		if ($this->testConnection && $this->showTestConnection) $this["test"]->setDefaultValue(true);
		$this->addSelect("driver", "Driver")
			->setItems($this->drivers, false)
			->setDefaultValue("pdo_mysql");

		$this->addGroup("Connection settings");
		$this->addText("host", "Host");
		$this->addText("port", "Port");
		$this->addText("user", "User name");
		$this->addPassword("password", "Password");
		$this->addText("dbname", "Database");
		$this->addTextWithSelect("path", "Path")->setItems(array("%tempDir%/database.db"), false);
		$this->addCheckbox("memory", "Db in memory");
		$this->addTextWithSelect("charset", "Charset")->setItems(array("utf8"), false);
		$this->addTextWithSelect("collation", "Collation")->setItems(array("utf8_general_ci", "utf8_czech_ci"), false);

		$this->presenter->context->assets->assetManager->addJavascript("@CoreModule/admin/js/systemDatabaseForm.js");
	}



	public function handleError()
	{

	}



	public function fireEvents()
	{
		if (!$this->isSubmitted()) {
			return;
		}

		$values = $this->getValues();
		$host = $values["host"];

		if ($values["driver"] == "pdo_sqlite") {
			$host = $this->presenter->context->expand($host);
		} else {
			$host = "host={$host};dbname={$values["dbname"]}";
		}

		/* Test */
		if ((!$this->showTestConnection && $this->testConnection) || ($this->showTestConnection && $values["test"])) {
			set_error_handler(array($this, 'handleError'));
			try {
				$db = new \Nette\Database\Connection(substr($values["driver"], 4) . ':' . $host, $values["user"], $values["password"]);
			} catch (\PDOException $e) {
				$this->getPresenter()->flashMessage("Cannot connect to database " . $e->getMessage(), "warning");
				return false;
			}
			restore_error_handler();
		}

		parent::fireEvents();
	}

}
