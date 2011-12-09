<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Config;

use Venne;
use Nette\Object;
use Nette\Config\NeonAdapter;
use Nette\OutOfRangeException;

/**
 * @author Josef Kříž
 */
class ConfigBuilder extends Object implements \ArrayAccess, \Countable, \IteratorAggregate {


	/** @var array */
	protected $data;

	/** @var array */
	protected $dataOrig;

	/** @var array */
	protected $sections;

	/** @var string */
	protected $fileName;



	/**
	 * @param string $fileName 
	 */
	public function __construct($fileName)
	{
		$this->fileName = $fileName;
	}



	/**
	 * Load data
	 */
	public function load()
	{
		$sep = NeonAdapter::$sectionSeparator;
		NeonAdapter::$sectionSeparator = "|";
		$this->data = \Nette\ArrayHash::from(NeonAdapter::load($this->fileName), true);

		NeonAdapter::$sectionSeparator = $sep;

		$this->sections = array();
		foreach ($this->data as $key => $item) {
			$pos = strpos($key, "<");
			if ($pos !== false) {
				$newKey = trim(substr($key, 0, strpos($key, "<")));
				$parentKey = trim(substr($key, $pos + 1));

				$this->data[$newKey] = \Nette\Utils\Arrays::mergeTree($this->data[$key], $this->data[$parentKey]);
				$this->data = \Nette\ArrayHash::from($this->data, true);
				unset($this->data[$key]);

				$this->sections[$newKey] = $parentKey;
			} else {
				$this->sections[$key] = NULL;
			}
		}
		$this->dataOrig = \Nette\ArrayHash::from((array)$this->data, true);
	}



	/**
	 * Optimize section
	 * @param array $array1
	 * @param array $array2
	 * @return array 
	 */
	private function optimize($array1, $array2)
	{
		foreach ($array1 as $key => $item) {
			if (isset($array2[$key])) {
				if (is_array($item)) {
					$ret = $this->optimize($array1[$key], $array2[$key]);
					if (count($ret) == 0) {
						unset($array1[$key]);
					} else {
						$array1[$key] = $ret;
					}
				} else {
					if ($array1[$key] === $array2[$key]) {
						unset($array1[$key]);
					}
				}
			}
		}
		return $array1;
	}



	/**
	 * Optimize section
	 * @param array $array1
	 * @param array $array2
	 * @return array 
	 */
	private function doChanges($array1, $array2, $new)
	{
		foreach ($array1 as $key => $item) {
			if (isset($array2[$key])) {
				if (is_array($item)) {
					$ret = $this->optimize($array1[$key], $array2[$key], $new);
					if (count($ret) == 0) {
						unset($array1[$key]);
					} else {
						$array1[$key] = $ret;
					}
				} else {
					if ($array1[$key] === $array2[$key]) {
						$array1[$key] = $new[$key];
					}
				}
			}
		}
		return $array1;
	}



	/**
	 * Save data
	 */
	public function save()
	{
		foreach ($this->sections as $section => $parent) {
			if ($parent) {
				$this->data[$section] = $this->doChanges($this->data[$section], $this->dataOrig[$parent], $this->data[$parent]);
				$this->data[$section . " < " . $parent] = $this->optimize($this->data[$section], $this->data[$parent]);
			}
		}
		foreach ($this->sections as $section => $parent) {
			if ($parent) {
				unset($this->data[$section]);
			}
		}
		NeonAdapter::save($this->data, $this->fileName);
		$this->load();
	}



	/**
	 * @return array 
	 */
	public function getSections()
	{
		return array_keys($this->sections);
	}



	/**
	 * @param string $section
	 * @return array
	 */
	public function getSection($section)
	{
		return $this->data[$section];
	}



	/**
	 * @param string $section
	 * @return string 
	 */
	public function getParentSection($section)
	{
		return $this->sections[$section];
	}



	/**
	 * @param string $section
	 * @param string $parentSection 
	 */
	public function setParentSection($section, $parentSection)
	{
		$this->sections[$section] = $parentSection;
	}



	/**
	 * @param string $section
	 * @return array
	 */
	public function getChildrenSections($section)
	{
		$ret = array();
		foreach ($this->sections as $name => $parent) {
			if ($parent == $section) {
				$ret[] = $name;
			}
		}
		return $ret;
	}



	/**
	 * @param string $section
	 * @param string $parent 
	 */
	public function createSection($section, $parent = NULL)
	{
		$this->data[$section] = array();
		$this->sections[$section] = $parent;
	}



	/**
	 * @param string $section 
	 */
	public function removeSection($section)
	{
		unset($this->data[$section]);
		unset($this->sections[$section]);
	}



	/**
	 * @param string $section
	 * @param string $newName 
	 */
	public function renameSection($section, $newName)
	{
		$this->data[$newName] = $this->data[$section];
		$this->sections[$newName] = $this->sections[$section];
		unset($this->data[$section]);
		unset($this->sections[$section]);
	}

	/* ------------------------------ Interfaces -------------------------------- */



	/**
	 * Returns items count.
	 * @return int
	 */
	public function count()
	{
		return $this->count($this->data);
	}



	/**
	 * Returns an iterator over all items.
	 * @return \RecursiveArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->data);
	}



	/**
	 * Determines whether a item exists.
	 * @param  mixed
	 * @return bool
	 */
	public function offsetExists($index)
	{
		return $index >= 0 && $index < count($this->data);
	}



	/**
	 * Returns a item.
	 * @param  mixed
	 * @return mixed
	 */
	public function offsetGet($index)
	{
		if ($index < 0 || $index >= count($this->data)) {
			throw new OutOfRangeException("Offset invalid or out of range");
		}
		$a = $this->data[$index];
		return $a;
	}



	/**
	 * Replaces or appends a item.
	 * @param  mixed
	 * @param  mixed
	 * @return void
	 */
	public function offsetSet($index, $value)
	{
		if ($index === NULL) {
			$this->data[] = is_array($value) ? \Nette\ArrayHash::from($value, true) : $value;
		} else {
			$this->data[$index] = is_array($value) ? \Nette\ArrayHash::from($value, true) : $value;
		}
	}



	/**
	 * Removes the element from this list.
	 * @param  mixed
	 * @return void
	 */
	public function offsetUnset($index)
	{
		if ($index < 0 || $index >= count($this->data)) {
			throw new OutOfRangeException("Offset invalid or out of range");
		}
		array_splice($this->data, $index, 1);
	}

}

