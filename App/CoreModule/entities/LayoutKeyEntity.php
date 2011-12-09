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
 * @Table(name="layoutKey")
 * 
 * @property string $val
 * @property string $key
 * @property LayoutEntity $layout
 */
class LayoutKeyEntity extends \Venne\Doctrine\ORM\BaseEntity{
	
	/**
	 *  @Column(type="string")
	 */
	protected $val;
	
	/**
	 *  @Column(name="`key`", type="string")
	 */
	protected $key;
	
	/**
	 * @ManyToOne(targetEntity="layoutEntity", inversedBy="id", cascade={"persist", "remove", "detach"})
	 * @JoinColumn(name="layout_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $layout;
	
	public function __toString()
	{
		return $this->val;
	}
	
		/**
	 * @return string 
	 */
	public function getKey()
	{
		return $this->key;
	}



	/**
	 * @param string $key 
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}



	/**
	 * @return string 
	 */
	public function getVal()
	{
		return $this->val;
	}



	/**
	 * @param string $val 
	 */
	public function setVal($val)
	{
		$this->val = $val;
	}



}
