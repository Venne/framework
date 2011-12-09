<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Application\UI;

use Venne;

/**
 * @author Josef Kříž
 */
class FrontPresenter extends \Venne\Application\UI\Presenter {


	const MODE_NORMAL = 0;
	const MODE_MODULE = 1;
	const MODE_LAYOUT = 2;
	const MODE_ELEMENTS = 3;

	public $contentExtensionsKey;

	/** @persistent */
	public $mode = 0;
	protected $_args;



	/**
	 * @return \Venne\Application\UI\EventArgs
	 */
	protected function createArgs()
	{
		if (!$this->_args) {
			$this->_args = new \Venne\Application\UI\EventArgs;
			$this->_args->presenter = $this;
		}
		return $this->_args;
	}



	public function startup()
	{
		$this->context->doctrineContainer->eventManager->dispatchEvent(Venne\Application\UI\Events::beforeStartup, $this->createArgs());
		parent::startup();
	}



	public function checkRequirements($element)
	{
		if ($this->mode != self::MODE_NORMAL && !$this->user->isLoggedIn()) {
			throw new \Nette\Application\ForbiddenRequestException;
		}
		parent::checkRequirements($element);
	}



	public function isModeNormal()
	{
		return ($this->mode == self::MODE_NORMAL);
	}



	public function isModeLayout()
	{
		return ($this->mode == self::MODE_LAYOUT);
	}



	public function isModeModule()
	{
		return ($this->mode == self::MODE_MODULE);
	}



	public function isModeElements()
	{
		return ($this->mode == self::MODE_ELEMENTS);
	}

}

