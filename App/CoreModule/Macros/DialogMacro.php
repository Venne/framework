<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\Macros;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class DialogMacro extends \Nette\Latte\Macros\MacroSet {


	public static function make($type = null, $args = array())
	{
		$args["type"] = $type;
		return '<div data-venne-ui-dialog="' . str_replace('"', "'", json_encode($args)) . '">';
	}



	public function start(\Nette\Latte\MacroNode $node, $writer)
	{
		return $writer->write('echo \App\CoreModule\Macros\DialogMacro::make(%node.word, %node.array?)');
	}



	public function stop(\Nette\Latte\MacroNode $node, $writer)
	{
		return $writer->write('?></div><?php');
	}



	public static function install(\Nette\Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('dialog', array($me, "start"), array($me, "stop"));
	}

}

