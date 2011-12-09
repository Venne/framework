<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace Venne\Localization;

/**
 * Translation extractor
 *
 * @author	Patrik Votoček
 */
class Extractor extends \Venne\FreezableObject
{
	/** @var Translator */
	protected $translator;
	/** @var array */
	protected $filters = array();
	/** @var \Nette\DI\Container */
	protected $context;

	/**
	 * @param Translator
	 */
	public function __construct(Translator $translator, \Nette\DI\Container $context)
	{
		$this->context = $context;
		$this->translator = $translator;
		$this->addFilter(new Filters\Latte($this->context));
	}

	/**
	 * @param IFilter
	 * @return Extractor
	 * @throws \Nette\InvalidStateException
	 */
	public function addFilter(IFilter $filter)
	{
		$this->updating();
		$this->filters[] = $filter;
		return $this;
	}



	/**
	 * @internal
	 */
	public function run()
	{
		$this->updating();
		$this->freeze();

		foreach ($this->translator->dictionaries as $dictionary) {
			if (!$dictionary->frozen) {
				$dictionary->init($this->translator->lang);
			}
			foreach ($this->filters as $filter) {
				$filter->process($dictionary);
			}
		}
	}
}
