<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Module;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ComposerModule extends BaseModule
{

	/** @var array */
	protected $composerData;


	public function getName()
	{
		$this->loadComposerData();

		return substr($this->composerData['name'], strpos($this->composerData['name'], '/') + 1, -7);
	}


	public function getDescription()
	{
		$this->loadComposerData();

		return $this->composerData['description'];
	}


	public function getKeywords()
	{
		$this->loadComposerData();

		return $this->composerData['keywords'];
	}


	public function getLicense()
	{
		$this->loadComposerData();

		return $this->composerData['license'];
	}


	public function getVersion()
	{
		$this->loadComposerData();

		if (isset($this->composerData['extra']['branch-alias']['dev-master'])) {
			return str_replace('-dev', '', $this->composerData['extra']['branch-alias']['dev-master']);
		}

		return parent::getVersion();
	}


	public function getAuthors()
	{
		$this->loadComposerData();

		return $this->composerData['authors'];
	}


	public function getAutoload()
	{
		$this->loadComposerData();

		$ret = $this->composerData['autoload'];

		if (file_exists(dirname($this->getReflection()->getFileName()) . '/vendor/autoload.php')) {
			return array_merge($ret, array(
				'files' => array('vendor/autoload.php'),
			));
		}

		return $ret;
	}


	public function getRequire()
	{
		$this->loadComposerData();

		return $this->composerData['require'];
	}


	public function getExtra()
	{
		$this->loadComposerData();

		return $this->composerData['extra'];
	}


	public function getConfiguration()
	{
		$this->loadComposerData();

		if (isset($this->composerData['extra']['venne']['configuration'])) {
			return $this->composerData['extra']['venne']['configuration'];
		}

		return parent::getConfiguration();
	}


	protected function loadComposerData()
	{
		if ($this->composerData === NULL) {
			$this->composerData = json_decode(file_get_contents($this->getPath() . '/composer.json'), true);
		}
	}
}

