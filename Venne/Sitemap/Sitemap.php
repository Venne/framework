<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\CMS\Sitemap;

/**
 * @author Josef Kříž
 */
class Sitemap {

	const CHANGE_ALWAYS = "always";
	const CHANGE_HOURLY = "hourly";
	const CHANGE_DAILY = "daily";
	const CHANGE_WEEKLY = "weekly";
	const CHANGE_MONTHLY = "monthly";
	const CHANGE_YEARLY = "yearly";
	const CHANGE_NEVER = "never";
	
	protected $arr = array();
	
	public function addItem($url, $lastMod, $changeFreq, $priority)
	{
		$this->arr[] = array($url, $lastMod, $changeFreq, $priority);
	}
	
	public function getXml()
	{
		$template = new \Nette\Templating\FileTemplate();
		$template->registerFilter(new \Nette\Latte\Engine);
		$template->setFile(__DIR__ . "/template.latte");
		$template->arr = $this->arr;
		return $template->__toString();
	}

}
