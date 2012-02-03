<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine\Migration;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */ class MigrationEntity extends \Venne\Doctrine\ORM\BaseEntity {


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
