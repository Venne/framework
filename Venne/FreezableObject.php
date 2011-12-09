<?php
/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne;

/**
 * Freezable object
 *
 * @author	Patrik Votoček
 */
abstract class FreezableObject extends \Nette\FreezableObject
{
	/** @var array */
	public $onFreeze = array();

	/**
	 * Freezes an array
	 *
	 * @return void
	 */
	public function freeze()
	{
		if (!$this->isFrozen()) {
			$this->onFreeze($this);
			parent::freeze();
		}
	}
}