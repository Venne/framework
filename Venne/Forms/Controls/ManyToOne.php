<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Forms\Controls;

use Venne;
use Nette\Forms\Controls\BaseControl;
use Nette;

/**
 * Select box control that allows single item selection.
 *
 * @author     David Grudl
 *
 * @property-read mixed $rawValue
 * @property   array $items
 * @property-read mixed $selectedItem
 * @property-read bool $firstSkipped
 */
class ManyToOne extends BaseControl {


	/** @var array */
	private $items = array();

	/** @var array */
	protected $allowed = array();

	/** @var bool */
	private $prompt = FALSE;

	/** @var bool */
	private $useKeys = TRUE;



	/**
	 * @param  string  label
	 * @param  array   items from which to choose
	 * @param  int     number of rows that should be visible
	 */
	public function __construct($label = NULL, array $items = NULL, $size = NULL)
	{
		parent::__construct($label);
		$this->control->setName('select');
		$this->control->size = $size > 1 ? (int) $size : NULL;
		if ($items !== NULL) {
			$this->setItems($items);
		}
	}



	public function setValue($value)
	{
		if($value instanceof \Venne\Doctrine\ORM\BaseEntity){
			return parent::setValue($value->id);
		}
	}



	/**
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData()
	{
		$path = explode('[', strtr(str_replace(array('[]', ']'), '', $this->getHtmlName()), '.', '_'));
		$this->value = (Nette\Utils\Arrays::get($this->getForm()->getHttpData(), $path, NULL));
	}



	/**
	 * Sets control's default value.
	 * @param  mixed
	 * @return BaseControl  provides a fluent interface
	 */
	public function setDefaultValue($value)
	{
		$form = $this->getForm(FALSE);
		if (!$form || !$form->isAnchored() || !$form->isSubmitted()) {
			$this->setValue($value->id);
		}
		return $this;
	}



	public function getValue()
	{
		foreach ($this->items as $item) {
			if($item instanceof \Venne\Doctrine\ORM\BaseEntity){
				if ($item->id == $this->value) {
					return $item;
				}
			}
		}
		return NULL;
	}



	/**
	 * Returns selected item key (not checked).
	 * @return mixed
	 */
	public function getRawValue()
	{
		return is_scalar($this->value) ? $this->value : NULL;
	}



	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		if ($this->prompt) {
			reset($this->items);
			$control->data('nette-empty-value', key($this->items));
		}

		$selected = (array)$this->getRawValue();
		
		$option = Nette\Utils\Html::el('option');

		foreach ($this->items as $key => $value) {
				if (!is_array($value)) {
					
					if($value instanceof \Venne\Doctrine\ORM\BaseEntity){
						$value = array($value->id => $value);
					}else{
						$value = array($value => $value);	
					}
					
					$dest = $control;
				} else {
					$dest = $control->create('optgroup')->label($this->translate($key));
				}

				foreach ($value as $value2) {
					if($value2 == $this->getForm()->entity->__toString()){
						continue;
					}

					if($value2 instanceof \Venne\Doctrine\ORM\BaseEntity){
						$key2 = $value2->id;
					}else{
						$key2 = $value2;	
					}
					
					if ($value2 instanceof Nette\Utils\Html) {
						$dest->add((string) $value2->selected(isset($selected[$key2])));
					} else {
						$value2 = $this->translate((string) $value2);
						$dest->add((string) $option->value($key2 === $value2 ? NULL : $key2)
										->selected(in_array($key2, $selected))
										->setText($value2));
					}
				}
		}
		return $control;
	}



	/**
	 * Has been any item selected?
	 * @return bool
	 */
	public function isFilled()
	{
		$value = $this->getValue();
		return is_array($value) ? count($value) > 0 : $value !== NULL;
	}



	/**
	 * Ignores the first item in select box.
	 * @param  string
	 * @return SelectBox  provides a fluent interface
	 */
	public function setPrompt($prompt)
	{
		if (is_bool($prompt)) {
			$this->prompt = $prompt;
		} else {
			$this->prompt = TRUE;
			if ($prompt !== NULL) {
				$this->items = array('' => $prompt) + $this->items;
				$this->allowed = array('' => '') + $this->allowed;
			}
		}
		return $this;
	}



	/** @deprecated */
	function skipFirst($v = NULL)
	{
		trigger_error(__METHOD__ . '() is deprecated; use setPrompt() instead.', E_USER_WARNING);
		return $this->setPrompt($v);
	}



	/**
	 * Is first item in select box ignored?
	 * @return bool
	 */
	final public function getPrompt()
	{
		return $this->prompt;
	}



	/**
	 * Are the keys used?
	 * @return bool
	 */
	final public function areKeysUsed()
	{
		return $this->useKeys;
	}



	/**
	 * Sets items from which to choose.
	 * @param  array
	 * @return SelectBox  provides a fluent interface
	 */
	public function setItems(array $items, $useKeys = TRUE)
	{
		$this->items = $items;
		$this->allowed = array();
		$this->useKeys = (bool) $useKeys;

		foreach ($items as $key => $value) {
			if (!is_array($value)) {
				$value = array($key => $value);
			}

			foreach ($value as $key2 => $value2) {
				if (!$this->useKeys) {
					if (!is_scalar($value2)) {
						throw new Nette\InvalidArgumentException("All items must be scalar.");
					}
					$key2 = $value2;
				}

				if (isset($this->allowed[$key2])) {
					throw new Nette\InvalidArgumentException("Items contain duplication for key '$key2'.");
				}

				$this->allowed[$key2] = $value2;
			}
		}
		return $this;
	}



	/**
	 * Returns items from which to choose.
	 * @return array
	 */
	final public function getItems()
	{
		return $this->items;
	}



	/**
	 * Returns selected value.
	 * @return string
	 */
	public function getSelectedItem()
	{
		if (!$this->useKeys) {
			return $this->getValue();
		} else {
			$value = $this->getValue();
			return $value === NULL ? NULL : $this->allowed[$value];
		}
	}

}
