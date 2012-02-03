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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SystemAdministrationForm extends \Venne\Forms\ConfigForm {


	public function startup()
	{
		parent::startup();

		$this->addGroup();
		$this->addText("routePrefix", "Route prefix");
	}

}
