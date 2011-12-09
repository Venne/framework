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

use Venne;

/**
 * @author Josef Kříž
 */
class JsMacro extends \Nette\Latte\Macros\MacroSet {
	
	public static function filter(\Nette\Latte\MacroNode $node, $writer)
	{
		return ('$control->getPresenter()->addJs("'.$node->args.'"); ');
	}

	public static function install(\Nette\Latte\Parser $parser)
	{
		$me = new static($parser);
		$me->addMacro('js', array($me, "filter"));
	}
	
}

