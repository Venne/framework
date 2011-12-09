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

/**
 * @author Josef Kříž
 * @Entity
 * @Table(name="layout")
 * 
 * @property LayoutKeyEntity $keys
 * @property string $regex
 * @property string $layout
 * @property string $moduleName
 * @property integer $moduleItemId
 */
class LayoutEntity extends \Venne\ContentExtension\ContentExtensionEntity {


	/** @Column(name="`regex`", type="string") */
	protected $regex;

	/** @Column(type="string") */
	protected $layout;

	/**
	 * @OneToMany(targetEntity="layoutKeyEntity", mappedBy="layout", indexBy="key", cascade={"persist", "remove", "detach"})
	 */
	protected $keys;
	
		/**
	 * @return string 
	 */
	public function getRegex()
	{
		return $this->regex;
	}



	/**
	 * @param string $regex 
	 */
	public function setRegex($regex)
	{
		$this->regex = $regex;
	}
	
		/**
	 * @return string 
	 */
	public function getKeys()
	{
		return $this->keys;
	}



	/**
	 * @param string $keys 
	 */
	public function setKeys($keys)
	{
		$this->keys = $keys;
	}
	
	
		/**
	 * @return string 
	 */
	public function getLayout()
	{
		return $this->layout;
	}



	/**
	 * @param string $layout 
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;
	}

}
