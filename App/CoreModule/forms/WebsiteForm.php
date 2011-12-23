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
use Nette\Forms\Form;

/**
 * @author Josef Kříž
 */
class WebsiteForm extends \Venne\Forms\ConfigForm {


	/** @var \App\CoreModule\ScannerService */
	protected $scannerService;

	/** @var \Venne\Config\ConfigBuilder */
	protected $configManager;

	/** @var array */
	protected $themes;

	/** @var \SystemContainer */
	protected $container;



	/**
	 * Constructor
	 * 
	 * @inject(scannerService, configManager, themes)
	 * 
	 * @param \App\CoreModule\ScannerService $scannerService
	 * @param \Venne\Config\ConfigBuilder $configManager
	 * @param \Venne\DI\Container $themesContainer
	 * @param type $mode 
	 */
	public function __construct(\Venne\Forms\Mapping\ConfigFormMapper $mapper, \App\CoreModule\ScannerService $scannerService, \Venne\Config\ConfigBuilder $configManager, \Nette\DI\Container $container)
	{
		parent::__construct($mapper);
		$this->scannerService = $scannerService;
		$this->configManager["venne"] = $configManager;
		$this->container = $container;
	}



	public function getThemes()
	{
		return $this->themes;
	}



	public function setThemes($themes)
	{
		$this->themes = $themes;
	}



	public function startup()
	{
		parent::startup();

		$this->addGroup("Themes");
		$this->addRadioList("theme", "Website theme");

		$this->addGroup("Global meta informations");
		$this->addText("title", "Title")->setOption("description", "(%s - separator, %t - local title)");
		$this->addText("titleSeparator", "Title separator");
		$this->addText("keywords", "Keywords");
		$this->addText("description", "Description");
		$this->addText("author", "Author");

		$this->addGroup("System");
		$this->addText("routePrefix", "Route prefix");
		$this->addCheckbox("multilang", "Multilangual");
		$this->addText("defaultLangAlias", "Default language alias");
	}



	public function setup()
	{
		$themes = $this->scannerService->getThemes();
		$arr = (array) $this->configManager["venne"];

		$this->setDefaults($arr);

		$arr = array();
		foreach ($themes as $theme => $item) {
			if ($theme == "admin") {
				continue;
			}
			
			$class = "\\" . ucfirst($theme) . "Theme\\Theme";
			$item = new $class($this->container);
			$arr[$theme] = $item->getDescription();
		}
		$this["theme"]->setItems($arr);
	}

}
