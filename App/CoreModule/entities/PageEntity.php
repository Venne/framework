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
 * @Entity(repositoryClass="\Venne\Doctrine\ORM\BaseRepository")
 * @Table(name="page",uniqueConstraints={
 * 		@UniqueConstraint(name="search_idx", columns={"parent_id", "url"})
 * })
 */
class PageEntity extends \Venne\Doctrine\ORM\BaseEntity {


	const LINK = "";

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

	/** @Column(type="integer") */
	protected $parent_id;

	/**
	 * @OneToMany(targetEntity="PageEntity", mappedBy="parent", cascade={"persist", "remove", "detach"})
	 */
	protected $childrens;

	/**
	 * @ManyToMany(targetEntity="LanguageEntity")
	 * @JoinTable(name="pageLanguageLink",
	 *      joinColumns={@JoinColumn(name="`from`", referencedColumnName="id", onDelete="CASCADE")},
	 *      inverseJoinColumns={@JoinColumn(name="`to`", referencedColumnName="id", onDelete="CASCADE")}
	 *      )
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
		$this->parent_id = -1;
		$this->languages = new \Doctrine\Common\Collections\ArrayCollection;
		$this->childrens = new \Doctrine\Common\Collections\ArrayCollection;
	}



	public function __toString()
	{
		return $this->title;
	}



	public function getName()
	{
		return $this->name;
	}



	public function setName($name)
	{
		$this->name = $name;
	}



	public function getUrl()
	{
		return $this->url;
	}



	public function getLocalUrl()
	{
		return $this->localUrl;
	}



	public function generateUrl($recursively = true)
	{
		$this->url = ($this->parent == true ? $this->parent->url . "/" : "") . $this->localUrl;

		if ($recursively) {
			foreach ($this->childrens as $children) {
				$children->generateUrl();
			}
		}
	}



	public function setLocalUrl($localUrl)
	{
		$this->localUrl = $localUrl;
		$this->generateUrl();
	}



	public function getParams()
	{
		return (array) json_decode($this->params);
	}



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



	public function getTitle()
	{
		return $this->title;
	}



	public function setTitle($title)
	{
		$this->title = $title;
	}



	public function getDescription()
	{
		return $this->description;
	}



	public function setDescription($description)
	{
		$this->description = $description;
	}



	public function getKeywords()
	{
		return $this->keywords;
	}



	public function setKeywords($keywords)
	{
		$this->keywords = $keywords;
	}



	public function getType()
	{
		return $this->type;
	}



	public function setType($type)
	{
		$this->type = $type;
	}



	public function getRobots()
	{
		return $this->robots;
	}



	public function setRobots($robots)
	{
		$this->robots = $robots;
	}



	public function getParent()
	{
		return $this->parent;
	}



	public function setParent($parent)
	{
		$this->parent = $parent;
		$this->parent_id = $parent ? $parent->id : -1;
	}



	public function getLanguages()
	{
		return $this->languages;
	}



	public function setLanguages($languages)
	{
		$this->languages = $languages;
	}



	public function getTranslationFor()
	{
		return $this->translationFor;
	}



	public function setTranslationFor($translationFor)
	{
		$this->translationFor = $translationFor;
	}



	public function getTranslations()
	{
		return $this->translations;
	}



	public function setTranslations($translations)
	{
		$this->translations = $translations;
	}

}
