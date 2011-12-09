<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
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