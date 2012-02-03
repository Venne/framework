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
use Nette\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class WebsiteForm extends \Venne\Forms\ConfigForm {


	public function startup()
	{
		parent::startup();

		$this->addGroup("Global meta informations");
		$this->addText("title", "Title")->setOption("description", "(%s - separator, %t - local title)");
		$this->addText("titleSeparator", "Title separator");
		$this->addText("keywords", "Keywords");
		$this->addText("description", "Description");
		$this->addText("author", "Author");

		$url = $this->presenter->context->httpRequest->url;
		$domain = trim($url->host . $url->scriptPath, "/") . "/";
		$params = array("<lang>/", "//$domain<lang>/", "//<lang>.$domain");

		$this->addGroup("System");
		$this->addTextWithSelect("routePrefix", "Route prefix")->setItems($params, false);
	}

}
