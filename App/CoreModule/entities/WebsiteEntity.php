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

/**
 * @author Josef Kříž
 * @Entity(repositoryClass="\Venne\Doctrine\ORM\BaseRepository")
 * @Table(name="website")
 * 
 * @property string $name
 * @property string $regex;
 * @property string $routePrefix
 * @property string $template
 * @property string $langType
 * @property string $langValue
 * @property int $langDefault
 */
class WebsiteEntity extends \Venne\Doctrine\ORM\BaseEntity {
	
	CONST LANG_OFF = 0;
	CONST LANG_PARSE_URL = "url";
	CONST LANG_IN_GET = "get";
	
	
	/**
	 * @Column(type="string",  length=300) 
	 */
	protected $name;
	
	/**
	 * @Column(type="string",  length=300) 
	 */
	protected $regex;
	
	/**
	 * @Column(type="string",  length=300) 
	 */
	protected $routePrefix;
	
	/**
	 * @Column(type="string",  length=300) 
	 */
	protected $skin;
	
	/**
	 *  @Column(type="string", length=30)
	 */
	protected $langType;
	
	/**
	 *  @Column(type="string", length=30)
	 */
	protected $langValue;
	
	/**
	 * @Column(type="integer")
	 */
	protected $langDefault;
	
}

