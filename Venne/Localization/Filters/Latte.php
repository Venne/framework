<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace Venne\Localization\Filters;

use Venne\Localization\Dictionary;

/**
 * Latte translation extractor filter
 *
 * @author	Patrik Votoček
 */
class Latte extends \Nette\Object implements \Venne\Localization\IFilter
{
	/** @var array */
	public $exts = array("*.latte");

	/** @var */
	protected $parser;
	
	public function __construct(\Nette\DI\Container $context)
	{
		$this->parser = $context->latteEngine->parser;
	}
	
	/**
	 * @param \Nella\Localization\Dictionary
	 */
	public function process(Dictionary $dictionary)
	{
		$dictionary->freeze();

		$parser = $this->parser;
		
		$macros = LatteMacros::install($parser);

		$files = \Nette\Utils\Finder::findFiles($this->exts)->from($dictionary->dir);
		foreach ($files as $file) {
			$parser->parse(file_get_contents($file->getRealpath()));
			foreach ($macros->translations as $message) {
				$translation = (array) $message;
				$message = is_array($message) ? reset($message) : $message;

				if ($dictionary->hasTranslation($message)) {
					continue;
				}

				$dictionary->addTranslation($message, $translation, Dictionary::STATUS_UNTRANSLATED);
			}
		}
	}
}
