<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Forms;

use Nette\ComponentModel\IComponent;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
interface IMapper
{

	public function save();


	public function load();


	public function setForm(Form $form);


	public function assign($data, IComponent $component);
}
