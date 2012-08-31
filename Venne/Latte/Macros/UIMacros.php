<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Latte\Macros;

use Venne;
use Nette,
Nette\Latte,
Nette\Latte\MacroNode,
Nette\Latte\PhpWriter,
Nette\Latte\CompileException,
Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UIMacros extends \Nette\Latte\Macros\UIMacros
{

	/** @var array */
	protected $modules;


	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('include', array($me, 'macroInclude'));
		$me->addMacro('includeblock', array($me, 'macroIncludeBlock'));
		$me->addMacro('extends', array($me, 'macroExtends'));
		$me->addMacro('layout', array($me, 'macroExtends'));
		$me->addMacro('block', array($me, 'macroBlock'), array($me, 'macroBlockEnd'));
		$me->addMacro('define', array($me, 'macroBlock'), array($me, 'macroBlockEnd'));
		$me->addMacro('snippet', array($me, 'macroBlock'), array($me, 'macroBlockEnd'));
		$me->addMacro('ifset', array($me, 'macroIfset'), 'endif');

		$me->addMacro('widget', array($me, 'macroControl')); // deprecated - use control
		$me->addMacro('control', array($me, 'macroControl'));

		$me->addMacro('href', NULL, NULL, function(MacroNode $node, PhpWriter $writer) use ($me) {
			return ' ?> href="<?php ' . $me->macroLink($node, $writer) . ' ?>"<?php ';
		});
		$me->addMacro('plink', array($me, 'macroLink'));
		$me->addMacro('link', array($me, 'macroLink'));
		$me->addMacro('ifCurrent', array($me, 'macroIfCurrent'), 'endif'); // deprecated; use n:class="$presenter->linkCurrent ? ..."

		$me->addMacro('contentType', array($me, 'macroContentType'));
		$me->addMacro('status', array($me, 'macroStatus'));
		return $me;
	}


	public function injectModules(array $modules)
	{
		$this->modules = $modules;
	}


	public function macroExtends(MacroNode $node, PhpWriter $writer)
	{
		$node->args = \Venne\Module\Helpers::expandPath($node->args, $this->modules);
		$node->tokenizer = new \Nette\Latte\MacroTokenizer($node->args);
		$writer = new PhpWriter($node->tokenizer);
		return parent::macroExtends($node, $writer);
	}
}

