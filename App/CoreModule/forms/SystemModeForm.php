<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule;

use Venne\ORM\Column;
use Nette\Utils\Html;
use Venne\Forms\Form;

/**
 * @author Josef Kříž
 */
class SystemModeForm extends \Venne\Forms\EditForm {


	/** @var \Venne\Config\ConfigBuilder */
	protected $configManager;

	/** @var string */
	protected $mode;

	/** @var string */
	protected $configDir;



	public function __construct(\Venne\Config\ConfigBuilder $configManager, $configDir, $mode = NULL)
	{
		$this->configManager = $configManager;
		$this->mode = $mode;
		$this->configDir = $configDir;
		parent::__construct();
	}



	public function startup()
	{
		parent::startup();

		$this->addGroup();
		$this->addText("name", "Name")->setDefaultValue((string) $this->mode);
	}



	public function save()
	{
		if ($this->mode !== NULL) {
			$key = array_search($this->mode, ((array) $this->configManager["parameters"]["modes"]));
			$this->configManager["parameters"]["modes"][$key] = $this["name"]->value;
			rename($this->configDir . "/config." . $this->mode . ".neon", $this->configDir . "/config." . $this["name"]->value . ".neon");
		} else {
			file_put_contents($this->configDir . "/config." . $this["name"]->value . ".neon", "");
			$key = count($this->configManager["parameters"]["modes"]);
			$this->configManager["parameters"]["modes"][$key] = $this["name"]->value;
		}

		if ($this->configManager["parameters"]["mode"] == $this->mode) {
			$this->configManager["parameters"]["mode"] = $this["name"]->value;
		}

		$this->configManager->save();
	}

}
