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

use Nette\Object;

/**
 * @author Josef Kříž
 */
class BasePageEntity extends \Venne\Doctrine\ORM\BaseEntity {


	const LINK = "";

	/**
	 * @var \App\CoreModule\PageEntity
	 * @OneToOne(targetEntity="\App\CoreModule\PageEntity", cascade={"persist", "remove", "detach"})
	 * @JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $page;

	/**
	 * @Form(type="manyToMany", targetEntity="\App\CoreModule\LanguageEntity")
	 */
	protected $languages;

	/**
	 * @Form(type="manyToOne", targetEntity="\App\CoreModule\PageEntity")
	 */
	protected $translationFor;



	public function __construct()
	{
		$this->page = new \App\CoreModule\PageEntity(static::LINK);
		parent::construct();
	}



	public function __toString()
	{
		return $this->title;
	}



	public function getPage()
	{
		if (!$this->page) {
			$this->page = new \App\CoreModule\PageEntity(static::LINK);
		}
		return $this->page;
	}



	public function getUrl()
	{
		return $this->getPage()->url;
	}



	public function setUrl($url)
	{
		$this->getPage()->url = $url;
	}



	public function getParams()
	{
		return $this->getPage()->params;
	}



	public function setParams($params)
	{
		$this->getPage()->params = $params;
	}



	public function getTitle()
	{
		return $this->getPage()->title;
	}



	public function setTitle($title)
	{
		$this->getPage()->title = $title;
	}



	public function getDescription()
	{
		return $this->getPage()->description;
	}



	public function setDescription($description)
	{
		$this->getPage()->description = $description;
	}



	public function getKeywords()
	{
		return $this->getPage()->keywords;
	}



	public function setKeywords($keywords)
	{
		$this->getPage()->keywords = $keywords;
	}



	public function getLayout()
	{
		return $this->getPage()->layout;
	}



	public function setLayout($layout)
	{
		$this->getPage()->layout = $layout;
	}



	public function getType()
	{
		return $this->getPage()->type;
	}



	public function setType($type)
	{
		$this->getPage()->type = $type;
	}



	public function getRobots()
	{
		return $this->getPage()->robots;
	}



	public function setRobots($robots)
	{
		$this->getPage()->robots = $robots;
	}



	public function getLanguages()
	{
		return $this->getPage()->languages;
	}



	public function setLanguages($languages)
	{
		$this->getPage()->languages = $languages;
	}



	public function getTranslationFor()
	{
		return $this->getPage()->translationFor;
	}



	public function setTranslationFor($translationFor)
	{
		$this->getPage()->translationFor = $translationFor;
	}



	public function getTranslations()
	{
		return $this->getPage()->translations;
	}



	public function setTranslations($translations)
	{
		$this->getPage()->translations = $translations;
	}

}

