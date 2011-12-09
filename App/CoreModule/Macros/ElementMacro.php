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
class ElementMacro extends \Nette\Latte\Macros\MacroSet {



	/**
	 * {control name[:method] [params]}
	 */
	public function filter(\Nette\Latte\MacroNode $node, $writer)
	{
		$pair = $node->tokenizer->fetchWord();
		if ($pair === FALSE) {
			throw new ParseException("Missing control name in {control}");
		}
		$pair = explode(':', $pair, 2);
		$name = $writer->formatWord($pair[0]);
		$method = isset($pair[1]) ? '."_".' . $writer->formatWord($pair[1]) : "";
		$param = $writer->formatArray();
		if (strpos($node->args, '=>') === FALSE) {
			$param = substr($param, 6, -1); // removes array()
		}
		return ('$_ctrl = $control->getPresenter()->getWidget("element_".' . $name . $method . '); '
				. 'if ($_ctrl instanceof Nette\Application\UI\IPartiallyRenderable) $_ctrl->validateControl(); '
				. "\$_ctrl->setParams($param); "
				. "\$_ctrl->render();");
	}



	public static function install(\Nette\Latte\Parser $parser)
	{
		$me = new static($parser);
		$me->addMacro('element', array($me, "filter"));
	}

}

