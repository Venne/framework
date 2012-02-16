<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Assets\Macros;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class JsMacro extends \Nette\Latte\Macros\MacroSet
{


	public static function filter(\Nette\Latte\MacroNode $node, $writer)
	{
		$path = $node->tokenizer->fetchWord();
		$params = $writer->formatArray();

		if (!$node->args) {
			return 'ob_start();';
		}

		return ('$control->getPresenter()->getAssetManager()->addJavascript("' . $path . '", ' . $params . '); ');
	}



	public static function end(\Nette\Latte\MacroNode $node, $writer)
	{
		$path = $node->tokenizer->fetchWord();
		$params = $writer->formatArray();

		if (!$node->args) {
			return '<?php $control->getPresenter()->getAssetManager()->addRawJavascript(ob_get_clean()); ?>';
		}

		return ('$control->getPresenter()->getAssetManager()->addJavascript("' . $path . '", ' . $params . '); ');
	}



	public static function install(\Nette\Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('js', array($me, "filter"));
		$me->addMacro('@external', array($me, "filter"), array($me, "end"));
	}

}

