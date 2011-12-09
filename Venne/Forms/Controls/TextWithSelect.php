<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Forms\Controls;

use Nette\Utils\Html;

/**
 * @author     Josef Kříž
 */
class TextWithSelect extends \Nette\Forms\Controls\TextInput {


	/** @var \Nette\Utils\Html  container element template */
	protected $container;

	/** @var array */
	private $items = array();

	/** @var array */
	protected $allowed = array();
	
	/** @var bool */
	private $prompt = FALSE;

	/** @var bool */
	private $useKeys = TRUE;



	public function __construct($label = NULL, $cols = NULL, $maxLength = NULL)
	{
		$this->container = Html::el();
		$this->prompt = true;
		parent::__construct($label, $cols, $maxLength);
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
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$container = clone $this->container;

		$container->add(parent::getControl() . " ");

		$text = Html::el("select");
		$text->attrs["data-venne-form-textwithselect"] = true;
		$option = Html::el('option');
		$text->add((string) $option->value("")->setText("----------"));
		$text->data('nette-empty-value', $this->useKeys ? key($this->items) : current($this->items));
		foreach ($this->items as $key => $value) {
			if (!is_array($value)) {
				$value = array($key => $value);
				$dest = $text;

			} else {
				$dest = $control->create('optgroup')->label($this->translate($key));
			}

			foreach ($value as $key2 => $value2) {
				if ($value2 instanceof Nette\Utils\Html) {
					$dest->add((string) $value2->selected(isset($selected[$key2])));

				} else {
					$key2 = $this->useKeys ? $key2 : $value2;
					$value2 = $this->translate((string) $value2);
					$text->add((string) $option->value($key2 === $value2 ? NULL : $key2)
						->selected($key2 == $this->value)
						->setText($value2));
				}
			}
		}

		$container->add($text);

		return $container;
	}

}
