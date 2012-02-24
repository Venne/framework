<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\Helpers;

use Venne;
use Nette;
use Nette\Object;
use Venne\Templating\IHelper;
use ITemplateConfigurator;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LatteHelper extends Object implements IHelper
{

	/** @var ITemplateConfigurator */
	protected $templateConfigurator;

	/** @var \Nette\Application\Application */
	protected $application;

	/** @var LatteHelper */
	protected static $instance;



	/**
	 * @param \Nette\Application\Application $application
	 */
	public function __construct(\Nette\Application\Application $application, \Venne\Templating\ITemplateConfigurator $templateConfigurator)
	{
		$this->application = $application;
		$this->templateConfigurator = $templateConfigurator;
		self::$instance = $this;
	}



	/**
	 * @param $text
	 * @return string
	 */
	protected function filterText($text)
	{
		$template = $this->application->getPresenter()->createTemplate("\Nette\Templating\Template");
		$template->setSource($text);
		return $template->__toString();
	}



	/**
	 * @static
	 * @param $text
	 * @return string
	 */
	public static function filter($text)
	{
		return self::$instance->filterText($text);
	}

}

