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
class ConfirmMacro extends \Nette\Latte\Macros\MacroSet {
	
	public static function filter(\Nette\Latte\MacroNode $node, $writer)
	{
		$content = $node->args;
		return $writer->write('echo "data-confirm=\"'.$content.'\";"');
	}
	
	public static function install(\Nette\Latte\Parser $parser)
	{
		$me = new static($parser);
		$me->addMacro('@confirm', array($me, "filter"));
	}
	
}

