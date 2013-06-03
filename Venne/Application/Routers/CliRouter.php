<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Application\Routers;

use Nette\Application\Request;

/**
 * Console router
 *
 * For use Symfony Console
 *
 * @author    Patrik Votoček
 */
class CliRouter extends \Nette\Object implements \Nette\Application\IRouter
{

	/** @var \Nette\Callback */
	private $callback;


	/**
	 * @param \Symfony\Component\Console\Application
	 */
	public function __construct(\Nette\DI\Container $container)
	{
		$this->callback = callback(function () use ($container) {
			$container->console->console->run();
		});
	}


	/**
	 * Maps command line arguments to a Request object
	 *
	 * @param  \Nette\Http\IRequest
	 * @return \Nette\Application\Request|NULL
	 */
	public function match(\Nette\Http\IRequest $httpRequest)
	{
		if (PHP_SAPI !== 'cli') {
			return NULL;
		}

		return new Request('Nette:Micro', 'CLI', array('callback' => $this->callback));
	}


	/**
	 * This router is only unidirectional
	 *
	 * @param  \Nette\Application\Request
	 * @param  \Nette\Http\Url
	 * @return NULL
	 */
	public function constructUrl(Request $appRequest, \Nette\Http\Url $refUrl)
	{
		return NULL;
	}
}
