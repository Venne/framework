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
class DialogMacro extends \Nette\Latte\Macros\MacroSet {



	public static function make($type = null, $args = array())
	{
		$args["type"] = $type;
		return '<div data-venne-ui-dialog="' . str_replace('"', "'", json_encode($args)) . '">';
	}



	public function start(\Nette\Latte\MacroNode $node, $writer)
	{
		return $writer->write('echo \App\CoreModule\DialogMacro::make(%node.word, %node.array?)');
	}



	public function stop(\Nette\Latte\MacroNode $node, $writer)
	{
		return $writer->write('?></div><?php');
	}



	public static function install(\Nette\Latte\Parser $parser)
	{
		$me = new static($parser);
		$me->addMacro('dialog', array($me, "start"), array($me, "stop"));
	}

}

