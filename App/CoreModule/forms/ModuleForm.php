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
class ModuleForm extends \Venne\Forms\EditForm {


	protected $key;

	/** @var \Venne\Config\ConfigBuilder */
	protected $configManager;
	protected $mode = "default";



	/**
	 * Application form constructor.
	 */
	public function __construct(\Venne\Config\ConfigBuilder $configManager, $key)
	{
		$this->configManager = $configManager;
		$this->key = $key;
		parent::__construct();
	}



	/**
	 * @param string $mode 
	 */
	public function setMode($mode)
	{
		$this->mode = $mode;
	}



	public function startup()
	{
		parent::startup();

		$this->addGroup("Basic setup");
		$this->addCheckbox("run", "Run")->setDefaultValue(true);
	}



	public function load()
	{
		foreach($this->getValues() as $key=>$value){
			if(isset($this->configManager[$this->mode]["modules"][$this->key][$key])){
			$this[$key]->setDefaultValue($this->configManager[$this->mode]["modules"][$this->key][$key]);
			}
		}
	}



	public function save()
	{
		foreach($this->getValues() as $key=>$value){
			$this->configManager[$this->mode]["modules"][$this->key][$key] = $value;			
		}
		$this->configManager->save();
	}

}
