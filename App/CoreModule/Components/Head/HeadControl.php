<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\Components\Head;

use Venne;
use Venne\Application\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class HeadControl extends Control
{

	const ROBOTS_INDEX = 1;

	const ROBOTS_NOINDEX = 2;

	const ROBOTS_FOLLOW = 4;

	const ROBOTS_NOFOLLOW = 8;

	/** @var string */
	protected $keywords;

	/** @var string */
	protected $description;

	/** @var string */
	protected $robots;

	/** @var string */
	protected $author;

	/** @var string */
	protected $title;

	/** @var string */
	protected $titleTemplate;

	/** @var string */
	protected $titleSeparator;



	public function startup()
	{
		parent::startup();

		/* Meta */
		$this->titleTemplate = $this->presenter->context->parameters["website"]["title"];
		$this->titleSeparator = $this->presenter->context->parameters["website"]["titleSeparator"];
		$this->author = $this->presenter->context->parameters["website"]["author"];
		$this->keywords = $this->presenter->context->parameters["website"]["keywords"];
		$this->description = $this->presenter->context->parameters["website"]["description"];
	}



	public function viewDefault()
	{
		$this->template->js = $this->presenter->getAssetManager()->getJavascripts();
		$this->template->css = $this->presenter->getAssetManager()->getStylesheets();
	}



	/***************************** Setters/getters ************************************************/


	/**
	 * @param string $author
	 */
	public function setAuthor($author)
	{
		$this->author = $author;
	}



	/**
	 * @return string
	 */
	public function getAuthor()
	{
		return $this->author;
	}



	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}



	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}



	/**
	 * @param string $keywords
	 */
	public function setKeywords($keywords)
	{
		$this->keywords = $keywords;
	}



	/**
	 * @return string
	 */
	public function getKeywords()
	{
		return $this->keywords;
	}



	/**
	 * @param string $robots
	 */
	public function setRobots($robots)
	{
		$this->robots = $robots;
	}



	/**
	 * @return string
	 */
	public function getRobots()
	{
		return $this->robots;
	}



	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}



	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}



	/**
	 * @param string $titleSeparator
	 */
	public function setTitleSeparator($titleSeparator)
	{
		$this->titleSeparator = $titleSeparator;
	}



	/**
	 * @return string
	 */
	public function getTitleSeparator()
	{
		return $this->titleSeparator;
	}



	/**
	 * @param string $titleTemplate
	 */
	public function setTitleTemplate($titleTemplate)
	{
		$this->titleTemplate = $titleTemplate;
	}



	/**
	 * @return string
	 */
	public function getTitleTemplate()
	{
		return $this->titleTemplate;
	}


}
