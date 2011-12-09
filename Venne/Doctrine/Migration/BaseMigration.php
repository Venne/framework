<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine\Migration;

use Venne;
use Nette\Object;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * @author Josef Kříž
 */
abstract class BaseMigration extends AbstractMigration {


	abstract public function getModuleName();
	
	abstract public function getVersion();

}