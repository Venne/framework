<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\WebsiteModule;

use Venne\ORM\Column;
use Nette\Utils\Html;
use Nette\Forms\Form;

/**
 * @author Josef Kříž
 */
class WebsiteForm extends \Venne\Forms\EditForm {

	/** @var \App\CoreModule\ScannerService */
	protected $scannerService;
	
	/** @var \Venne\Config\ConfigBuilder */
	protected $configManager;
	
	/** @var string */
	protected $mode;
	
	/** @var \Venne\DI\Container */
	protected $themesContainer;
	
	
	

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
	public function __construct(\App\CoreModule\ScannerService $scannerService, \Venne\Config\ConfigBuilder $configManager, \Venne\DI\Container $themesContainer, $mode = "default")
	{
		parent::__construct();
		$this->scannerService = $scannerService;
		$this->configManager = $configManager;
		$this->themesContainer = $themesContainer;
		$this->mode = $mode;
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
		//$this->addTextWithSelect("routePrefix", "Language route")->setItems(array("<lang>/", "<lang>.company.cz"), false);
		$this->addText("routePrefix", "Route prefix");
		$this->addCheckbox("multilang", "Multilangual");
		$this->addText("defaultLangAlias", "Default language alias");
	}



	public function setup()
	{
		$skins = $this->scannerService->getThemes();

		$this->setDefaults($this->configManager[$this->mode]["website"]);

		$arr = array();
		foreach ($skins as $skin) {
			if ($skin == "admin") {
				continue;
			}
			$arr[$skin] = $this->themesContainer->{$skin}->getDescription();
		}
		$this["theme"]->setItems($arr);
	}



	public function save()
	{
		$this->configManager[$this->mode]["website"]["theme"] = $this["theme"]->getValue();
		$this->configManager[$this->mode]["website"]["title"] = $this["title"]->getValue();
		$this->configManager[$this->mode]["website"]["titleSeparator"] = $this["titleSeparator"]->getValue();
		$this->configManager[$this->mode]["website"]["keywords"] = $this["keywords"]->getValue();
		$this->configManager[$this->mode]["website"]["description"] = $this["description"]->getValue();
		$this->configManager[$this->mode]["website"]["author"] = $this["author"]->getValue();
		$this->configManager[$this->mode]["website"]["routePrefix"] = $this["routePrefix"]->getValue();
		$this->configManager[$this->mode]["website"]["multilang"] = $this["multilang"]->getValue();
		$this->configManager[$this->mode]["website"]["defaultLangAlias"] = $this["defaultLangAlias"]->getValue();
		$this->configManager->save();
	}

}
