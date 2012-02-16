<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine\ORM;

use Doctrine;
use Venne;
use Nette;


/**
 * @author Filip Procházka
 */
abstract class Type extends Doctrine\DBAL\Types\Type
{

	const CALLBACK = 'callback';

	const PASSWORD = 'password';

	// todo: texy, image, ...

}