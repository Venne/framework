<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\Entities;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @Entity(repositoryClass="\App\CoreModule\Repositories\PageRepository")
 * @Table(name="page")
 */
class PageEntity extends \Venne\Doctrine\ORM\BaseEntity {


	/** @Column(type="string") */
	protected $url;

	/** @Column(type="string") */
	protected $localUrl;

	/** @Column(type="string") */
	protected $params;

	/** @Column(type="integer") */
	protected $paramCounter;

	/** @Column(type="string") */
	protected $title;

	/** @Column(type="string") */
	protected $description;

	/** @Column(type="string") */
	protected $keywords;

	/** @Column(type="string") */
	protected $robots;

	/** @Column(type="string") */
	protected $type;

	/**
	 * @ManyToOne(targetEntity="PageEntity", inversedBy="childrens", cascade={"persist", "remove", "detach"})
	 * @JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $parent;

	/**
	 * @OneToMany(targetEntity="PageEntity", mappedBy="parent", cascade={"persist", "remove", "detach"})
	 */
	protected $childrens;

	/**
	 * @ManyToMany(targetEntity="LanguageEntity")
	 * @JoinTable(name="pageLanguageLink",
	 *	  joinColumns={@JoinColumn(name="`from`", referencedColumnName="id", onDelete="CASCADE")},
	 *	  inverseJoinColumns={@JoinColumn(name="`to`", referencedColumnName="id", onDelete="CASCADE")}
	 *	  )
	 */
	protected $languages;

	/**
	 * @ManyToOne(targetEntity="PageEntity", inversedBy="id")
	 * @JoinColumn(name="translationFor", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $translationFor;

	/**
	 * @OneToMany(targetEntity="PageEntity", mappedBy="translationFor")
	 */
	protected $translations;

	/**
	 * @ManyToOne(targetEntity="LayoutEntity", inversedBy="id")
	 * @JoinColumn(name="layout_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $layout;

	/** @Column(type="string", nullable=true) */
	protected $layoutFile;



	/**
	 * @param $type
	 */
	public function __construct($type)
	{
		$this->type = $type;
		$this->name = "";
		$this->url = "";
		$this->localUrl = "";
		$this->description = "";
		$this->keywords = "";
		$this->params = json_encode(array());
		$this->paramCounter = 0;
		$this->languages = new \Doctrine\Common\Collections\ArrayCollection;
		$this->childrens = new \Doctrine\Common\Collections\ArrayCollection;
	}



	/**
	 * @return mixed
	 */
	public function __toString()
	{
		return $this->title;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @param $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}



	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}



	/**
	 * @return string
	 */
	public function getLocalUrl()
	{
		return $this->localUrl;
	}



	/**
	 * @param bool $recursively
	 */
	public function generateUrl($recursively = true)
	{
		if ($this->parent !== NULL && method_exists($this->parent, "__load")) {
			$this->parent->__load();
		}
		$this->url = trim(($this->parent !== NULL ? $this->parent->url . "/" : "") . $this->localUrl, "/");

		if ($recursively) {
			foreach ($this->childrens as $children) {
				$children->generateUrl();
			}
		}
	}



	/**
	 * @param $localUrl
	 */
	public function setLocalUrl($localUrl)
	{
		$this->localUrl = $localUrl;
		$this->generateUrl();
	}



	/**
	 * @return array
	 */
	public function getParams()
	{
		return (array)json_decode($this->params);
	}



	/**
	 * @param $params
	 */
	public function setParams($params)
	{
		$delete = array("module", "presenter", "action");
		foreach ($delete as $item) {
			if (isset($params[$item])) {
				unset($params[$item]);
			}
		}

		ksort($params);
		$this->params = json_encode($params);
		$this->paramCounter = count($params);
	}



	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return $this->title;
	}



	/**
	 * @param $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}



	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}



	/**
	 * @param $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}



	/**
	 * @return string
	 */
	public function getKeywords()
	{
		return $this->keywords;
	}



	/**
	 * @param $keywords
	 */
	public function setKeywords($keywords)
	{
		$this->keywords = $keywords;
	}



	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}



	/**
	 * @param $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}



	/**
	 * @return mixed
	 */
	public function getRobots()
	{
		return $this->robots;
	}



	/**
	 * @param $robots
	 */
	public function setRobots($robots)
	{
		$this->robots = $robots;
	}



	/**
	 * @return mixed
	 */
	public function getParent()
	{
		return $this->parent;
	}



	/**
	 * @param $parent
	 */
	public function setParent($parent)
	{
		$this->parent = $parent;
		$this->generateUrl();
	}






	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getLanguages()
	{
		return $this->languages;
	}



	/**
	 * @param $languages
	 */
	public function setLanguages($languages)
	{
		$this->languages = $languages;
	}



	/**
	 * @return mixed
	 */
	public function getTranslationFor()
	{
		return $this->translationFor;
	}



	/**
	 * @param $translationFor
	 */
	public function setTranslationFor($translationFor)
	{
		$this->translationFor = $translationFor;
	}



	/**
	 * @return mixed
	 */
	public function getTranslations()
	{
		return $this->translations;
	}



	/**
	 * @param $translations
	 */
	public function setTranslations($translations)
	{
		$this->translations = $translations;
	}



	/**
	 * @return mixed
	 */
	public function getLayout()
	{
		return $this->layout;
	}



	/**
	 * @param $layout
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;
	}



	/**
	 * Check if page is in language alias.
	 *
	 * @param type $alias
	 * @return type
	 */
	public function isInLanguageAlias($alias)
	{
		return $this->languages->exists(function($key, $entity) use ($alias)
		{
			return ($entity->alias == $alias);
		});
	}



	/**
	 * Return the same page in other language alias.
	 *
	 * @param string $alias
	 * @return \App\CoreModule\Entities\PageEntity
	 */
	public function getPageWithLanguageAlias($alias)
	{
		if ($this->isInLanguageAlias($alias)) {
			return $this;
		}

		if (!$this->translationFor) {
			foreach ($this->translations as $page) {
				if ($page->isInLanguageAlias($alias)) {
					return $page;
				}
			}
		} else {
			if ($this->translationFor->isInLanguageAlias($alias)) {
				return $this->translationFor;
			}

			foreach ($this->translationFor->translations as $page) {
				if ($page === $this) {
					continue;
				}

				if ($page->isInLanguageAlias($alias)) {
					return $page;
				}
			}
		}
		return NULL;
	}



	/**
	 * @param $layoutFile
	 */
	public function setLayoutFile($layoutFile)
	{
		$this->layoutFile = $layoutFile;
	}



	/**
	 * @return mixed
	 */
	public function getLayoutFile()
	{
		return $this->layoutFile;
	}



	/**
	 * @param $childrens
	 */
	public function setChildrens($childrens)
	{
		$this->childrens = $childrens;
	}



	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getChildrens()
	{
		return $this->childrens;
	}


}
