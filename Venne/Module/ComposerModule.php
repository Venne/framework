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

		return $this->normalizeName($this->composerData['name']);
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

		if (isset($this->composerData['version'])) {
			return $this->composerData['version'];
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

		$ret = isset($this->composerData['autoload']) ? $this->composerData['autoload'] : array();

		if (file_exists(dirname($this->getReflection()->getFileName()) . '/vendor/autoload.php')) {
			return array_merge($ret, array(
				'files' => array('vendor/autoload.php'),
			));
		}

		return $ret;
	}


	/**
	 * @return VersionConstraint[]
	 */
	public function getRequire()
	{
		$this->loadComposerData();

		$ret = array();
		foreach ($this->composerData['require'] as $name => $require) {
			if (substr($name, -7) === '-module') {

				if (substr($require, -4) === '-dev') {
					$require = substr($require, 0, -4);
				}

				$ret[$this->normalizeName($name)] = str_replace('*', 'x', $require);
			}
		}

		return $ret;
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


	public function getInstallers()
	{
		$this->loadComposerData();

		if (isset($this->composerData['extra']['venne']['installers'])) {
			return array_merge(parent::getInstallers(), $this->composerData['extra']['venne']['installers']);
		}

		return parent::getInstallers();
	}


	protected function loadComposerData()
	{
		if ($this->composerData === NULL) {
			$this->composerData = json_decode(file_get_contents($this->getPath() . '/composer.json'), true);
		}
	}


	/**
	 * @param $name
	 * @return string
	 */
	protected function normalizeName($name)
	{
		return substr($name, strpos($name, '/') + 1, -7);
	}
}

