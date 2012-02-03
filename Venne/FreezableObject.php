<?php
/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne;

/**
 * Freezable object
 *
 * @author	Patrik Votoček
 */ abstract class FreezableObject extends \Nette\FreezableObject {

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