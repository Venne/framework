<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Latte;

use Venne;
use Nette\Latte\Compiler;
use Nette\Latte\Parser;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Engine extends \Nette\Latte\Engine
{

	/** @var Parser */
	private $parser;

	/** @var Compiler */
	private $compiler;


	public function __construct()
	{
		$this->parser = new Parser;
		$this->compiler = new Compiler;
		$this->compiler->defaultContentType = Compiler::CONTENT_XHTML;

		\Nette\Latte\Macros\CoreMacros::install($this->compiler);
		$this->compiler->addMacro('cache', new \Nette\Latte\Macros\CacheMacro($this->compiler));
		\Nette\Latte\Macros\FormMacros::install($this->compiler);
	}


	/**
	 * Invokes filter.
	 * @param  string
	 * @return string
	 */
	public function __invoke($s)
	{
		return $this->compiler->compile($this->parser->parse($s));
	}


	/**
	 * @return Parser
	 */
	public function getParser()
	{
		return $this->parser;
	}


	/**
	 * @return Compiler
	 */
	public function getCompiler()
	{
		return $this->compiler;
	}
}

