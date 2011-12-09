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

/**
 * @author Josef Kříž
 */
class MigrationEntity extends \Venne\Doctrine\ORM\BaseEntity {



	public function __construct()
	{
		$this->created = new \Nette\DateTime;
		$this->updated = new \Nette\DateTime;
		$this->mainPage = false;
	}

	/**
	 * @Column(type="string")
	 * @Form(group="Item")
	 */
	protected $moduleName;



}
