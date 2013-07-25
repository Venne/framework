<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Module\DependencyResolver;

use Nette\InvalidArgumentException;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Problem extends Object
{

	/** @var Job[] */
	protected $solutions = array();


	/**
	 * @param Job $job
	 * @throws \Nette\InvalidArgumentException
	 */
	public function addSolution(Job $job)
	{
		if ($this->hasSolution($job)) {
			throw new InvalidArgumentException("Solution '{$job->getModule()->getName()}:{$job->getAction()}' is already added.");
		}

		$this->solutions[$job->getModule()->getName()] = $job;
	}


	/**
	 * @param Job $job
	 */
	public function hasSolution(Job $job)
	{
		return isset($this->solutions[$job->getModule()->getName()]);
	}


	/**
	 * @return Job[]
	 */
	public function getSolutions()
	{
		return array_merge($this->solutions);
	}
}

