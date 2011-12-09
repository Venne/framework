<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\ModulesModule;

use Venne\ORM\Column;
use Nette\Utils\Html;
use Venne\Forms\Form;

/**
 * @author Josef Kříž
 */
class ModulesCreatorForm extends \Venne\Forms\EditForm {

	public function startup()
	{
		parent::startup();

		$this->addGroup("Package informations");
		$this->addText("pkgname","Package name")->addRule(self::FILLED, "Enter package name");
		$this->addText("pkgver", "Package version")->addRule(self::FILLED, "Enter package version");
		$this->addText("pkgdesc", "Package description")->addRule(self::FILLED, "Enter package description");
		$this->addText("licence", "Licence");
		$this->addTag("dependencies", "Dependencies");
		
		$this->addGroup("Packager");
		$this->addText("packager", "Packager name")->addRule(self::FILLED, "Enter packager name");
		
		$this->addGroup("Files");
		$this->addTextArea("files", "Files");
	}


	public function load()
	{
			$model = $this->presenter->context->scannerService;
			
			$values = $model->loadPackageBuild($this->key);
			$values["files"] = join("\n", $values["files"]);
						
			$this->setValues($values);
			$this["dependencies"]->setDefaultValue($values["dependencies"]);
	}

	public function save()
	{
		parent::save();
		$values = $this->getValues();
		$model = $this->presenter->context->scannerService;

		$values["files"] = str_replace("\r", "", $values["files"]);
		$values["files"] = explode("\n", $values["files"]);
		
		$model->savePackageBuild($values['pkgname'], $values['pkgver'], $values['pkgdesc'], $values['licence'], $values['dependencies'], $values['packager'], $values['files']);
	}

}
