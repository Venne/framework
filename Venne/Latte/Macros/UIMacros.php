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

use Nette\Latte\Compiler;
use Nette\Latte\MacroNode;
use Nette\Latte\MacroTokenizer;
use Nette\Latte\PhpWriter;
use Nette\Utils\Strings;
use Venne\Module\Helpers;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UIMacros extends \Nette\Latte\Macros\UIMacros
{

	/** @var Helpers */
	protected $moduleHelpers;


	/**
	 * @param Helpers $helper
	 */
	public function injectHelper(Helpers $helper)
	{
		$this->moduleHelpers = $helper;
	}


	/**
	 * @param Compiler $compiler
	 * @return void|static
	 */
	public static function install(Compiler $compiler)
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

		$me->addMacro('href', NULL, NULL, function (MacroNode $node, PhpWriter $writer) use ($me) {
			return ' ?> href="<?php ' . $me->macroLink($node, $writer) . ' ?>"<?php ';
		});
		$me->addMacro('plink', array($me, 'macroLink'));
		$me->addMacro('link', array($me, 'macroLink'));
		$me->addMacro('ifCurrent', array($me, 'macroIfCurrent'), 'endif'); // deprecated; use n:class="$presenter->linkCurrent ? ..."

		$me->addMacro('contentType', array($me, 'macroContentType'));
		$me->addMacro('status', array($me, 'macroStatus'));

		$me->addMacro('path', array($me, 'macroPath'));
		$me->addMacro('ifLinkExists', array($me, 'macroIfLinkExists'), array($me, 'macroEndIfLinkExists'));
		$me->addMacro('ifPlinkExists', array($me, 'macroIfLinkExists'), array($me, 'macroEndIfLinkExists'));

		return $me;
	}


	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 */
	public function macroExtends(MacroNode $node, PhpWriter $writer)
	{
		$node->args = $this->moduleHelpers->expandPath($node->args, 'Resources/layouts');
		$node->tokenizer = new MacroTokenizer($node->args);
		$writer = new PhpWriter($node->tokenizer);
		return parent::macroExtends($node, $writer);
	}


	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 * @return string
	 */
	public function macroIncludeBlock(MacroNode $node, PhpWriter $writer)
	{
		$node->args = $this->moduleHelpers->expandPath($node->args, 'Resources/layouts');
		$node->tokenizer = new MacroTokenizer($node->args);
		$writer = new PhpWriter($node->tokenizer);
		return parent::macroIncludeBlock($node, $writer);
	}


	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 * @return string
	 */
	public function macroPath(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write("echo \$basePath . '/' . \$presenter->context->venne->moduleHelpers->expandResource(%node.word)");
	}


	/**
	 * {ifLinkExists destination [,] [params]}
	 * n:ifLinkExists="destination [,] [params]"
	 */
	public function macroIfLinkExists(MacroNode $node, PhpWriter $writer)
	{
		$c = '$_macroIfLinkExistsOld = $_presenter->invalidLinkMode;
		$_presenter->invalidLinkMode = $_presenter::INVALID_LINK_EXCEPTION;
		$_macroIfLinkExistsPresenter = %node.word;
		$_macroIfLinkExistsArgs = %node.array?;

		try {
			$_macroIfLinkExistslink = %escape(%modify(' . ($node->name === 'ifPlinkExists' ? '$_presenter' : '$_control') . '->link($_macroIfLinkExistsPresenter, $_macroIfLinkExistsArgs)));
			$_macroIfLinkExistsRet = TRUE;
		} catch (\Nette\Application\UI\InvalidLinkException $e) {
			$_macroIfLinkExistslink = \'#\';
			$_macroIfLinkExistsRet = FALSE;
			$_presenter->invalidLinkMode = $_macroIfLinkExistsOld;
		}
		$_presenter->invalidLinkMode = $_macroIfLinkExistsOld;
		';
		if ($node->prefix === $node::PREFIX_TAG) {
			return $writer->write($c . ($node->htmlNode->closing ? 'if (array_pop($_l->ifs)):' : 'if ($_l->ifs[] = ($_macroIfLinkExistsRet)):'));
		}
		return $writer->write($c . 'if ($_macroIfLinkExistsRet):');
	}


	/**
	 * {/ifLinkExists}
	 */
	public function macroEndIfLinkExists(MacroNode $node, PhpWriter $writer)
	{
		return 'endif';
	}


	/**
	 * {link destination [,] [params]}
	 * {plink destination [,] [params]}
	 * n:href="destination [,] [params]"
	 * n:link
	 */
	public function macroLink(MacroNode $node, PhpWriter $writer)
	{
		if ($node->args == '') {
			if ($node->htmlNode) {
				$node->attrCode = $writer->write(' href="<?php echo $_macroIfLinkExistslink; ?>"');
				return;
			}
			return $writer->write('echo $_macroIfLinkExistslink');
		}
		return parent::macroLink($node, $writer);
	}
}
