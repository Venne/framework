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
use Nette\DI\Container;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class HeadMacro extends \Nette\Latte\Macros\MacroSet {


	public function headBegin(\Nette\Latte\MacroNode $node, $writer)
	{
		return $writer->write('ob_start();');
	}



	public function headEnd(\Nette\Latte\MacroNode $node, $writer)
	{
		return $writer->write('$_headMacroData = ob_get_clean();');
	}



	public function bodyBegin(\Nette\Latte\MacroNode $node, $writer)
	{
		return $writer->write('ob_start(); echo $presenter["vennePanel"]->render();');
	}



	public function bodyEnd(\Nette\Latte\MacroNode $node, $writer)
	{
		return $writer->write('$_bodyMacroData = ob_get_clean();?><head>
<?php $presenter->context->eventManager->dispatchEvent(\App\CoreModule\Events\RenderEvents::onHeadBegin); ?>
<?php echo $presenter["head"]->render(); echo $_headMacroData;?><?php $presenter->context->eventManager->dispatchEvent(\App\CoreModule\Events\RenderEvents::onHeadEnd); ?>
</head>

<body<?php if($basePath){?> data-venne-basepath="<?php echo $basePath;?>"<?php } ?>><?php $presenter->context->eventManager->dispatchEvent(\App\CoreModule\Events\RenderEvents::onBodyBegin); ?>
<?php echo $_bodyMacroData;?><?php $presenter->context->eventManager->dispatchEvent(\App\CoreModule\Events\RenderEvents::onBodyEnd); ?>
</body>
<?php
');
	}



	public static function install(\Nette\Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('head', array($me, "headBegin"), array($me, "headEnd"));
		$me->addMacro('body', array($me, "bodyBegin"), array($me, "bodyEnd"));
	}

}

