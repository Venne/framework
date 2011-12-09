<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Application\UI;

use Nette;

/**
 * @author Josef Kříž
 * @author Filip Procházka
 * 
 * @method \Venne\Forms\Controls\CheckboxList addCheckboxList() addCheckboxList($name, $items)
 * @method \Venne\Forms\Controls\TextWithSelect addCheckboxList() addTextWithSelect($label, $cols, $maxLength)
 */
class Form extends \Nette\Application\UI\Form {


	/** @var string */
	protected $successLink;

	/** @var string */
	protected $successLinkParams;

	/** @var string */
	protected $flash;

	/** @var string */
	protected $flashStatus = "success";



	public function __construct()
	{
		parent::__construct();
		$this->getElementPrototype()->class[] = "venne-form";
		$this->addProtection("Ouchie! Please try to submit the form again, the delivery boy forgot something!");
		$this->startup();
		$this->setCurrentGroup();
	}



	public function setSuccessLink($link, $params = NULL)
	{
		$this->successLink = $link;
		$this->successLinkParams = (array) $params;
	}



	public function setFlashMessage($value, $status = NULL)
	{
		if ($status) {
			$this->flashStatus = $status;
		}
		$this->flash = $value;
	}



	/**
	 * Method get's called on construction
	 */
	public function startup()
	{
		
	}



	/**
	 * @param Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);
		$this->setup();
		$this->afterSetup();
	}



	/**
	 * Method get's called on construction
	 */
	public function setup()
	{
		
	}



	/**
	 * Returns a fully-qualified name that uniquely identifies the component
	 * within the presenter hierarchy.
	 * @return string
	 */
	public function getUniqueId()
	{
		return $this->lookupPath('Nette\Application\UI\Presenter', TRUE);
	}



	/**
	 * Fires submit/click events.
	 * @return void
	 */
	public function fireEvents()
	{
		if (!$this->isSubmitted()) {
			return;
		} elseif ($this->isSubmitted() instanceof ISubmitterControl) {
			if (!$this->isSubmitted()->getValidationScope() || $this->isValid()) {
				$this->dispatchEvent($this->isSubmitted()->onClick, $this->isSubmitted());
				$valid = TRUE;
			} else {
				$this->dispatchEvent($this->isSubmitted()->onInvalidClick, $this->isSubmitted());
			}
		}

		if (isset($valid) || $this->isValid()) {
			$this->dispatchEvent($this->onSuccess, $this);

			if ($this->flash) {
				$this->presenter->flashMessage($this->flash, $this->flashStatus);
			}
		} else {
			$this->dispatchEvent($this->onError, $this);
		}
	}



	/**
	 * @param array|\Traversable $listeners
	 * @param mixed $arg
	 * @param mixed $arg2
	 * @param mixed $arg3
	 */
	protected function dispatchEvent($listeners, $arg = NULL)
	{
		$args = func_get_args();
		$listeners = array_shift($args);

		foreach ((array) $listeners as $handler) {
			if ($handler instanceof Nette\Application\UI\Link) {
				$this->getPresenter()->redirectUrl($handler);
			} else {
				callback($handler)->invokeArgs($args);
			}
		}
	}



	public function afterSetup()
	{
		foreach ($this->getComponents() as $component) {
			if ($component instanceof \Venne\Forms\Controls\TagInput) {
				$this->presenter->addJs("/js/Forms/Controls/tag.js");
			}

			if ($component instanceof \Venne\Forms\Controls\DateInput) {
				$this->presenter->addJs("/js/Forms/Controls/date.js");
			}

			if ($component instanceof \DependentSelectBox\DependentSelectBox) {
				$this->presenter->addJs("/js/Forms/Controls/dependentSelectBox.js");
			}

			if ($component instanceof \Nette\Forms\Controls\TextArea && isset($component->getControlPrototype()->data["venne-form-editor"])) {
				$this->presenter->addJs("/js/Forms/Controls/editor.js");
			}

			if ($component instanceof \Venne\Forms\Controls\TextWithSelect) {
				$this->presenter->addJs("/js/Forms/Controls/textWithSelect.js");
			}
		}
	}

}

\Nette\Forms\Container::extensionMethod("addDynamic", function(Nette\Forms\Container $container, $name, $factory, $createDefault = 0) {
			return $container[$name] = new \Venne\Forms\Containers\Replicator($factory, $createDefault);
		});

\Nette\Forms\Container::extensionMethod("addTag", function(Nette\Forms\Container $container, $name, $label = NULL) {
			$container[$name] = new \Venne\Forms\Controls\TagInput($label);
			return $container[$name];
		});

\Nette\Forms\Container::extensionMethod("addDate", function(Nette\Forms\Container $container, $name, $label = NULL) {
			$container[$name] = new \Venne\Forms\Controls\DateInput($label, \Venne\Forms\Controls\DateInput::TYPE_DATE);
			return $container[$name];
		});

\Nette\Forms\Container::extensionMethod("addDatetime", function(Nette\Forms\Container $container, $name, $label = NULL) {
			$container[$name] = new \Venne\Forms\Controls\DateInput($label, \Venne\Forms\Controls\DateInput::TYPE_DATETIME);
			return $container[$name];
		});

\Nette\Forms\Container::extensionMethod("addTime", function(Nette\Forms\Container $container, $name, $label = NULL) {
			$container[$name] = new \Venne\Forms\Controls\DateInput($label, \Venne\Forms\Controls\DateInput::TYPE_TIME);
			return $container[$name];
		});

\Nette\Forms\Container::extensionMethod("addDependentSelectBox", function(Nette\Forms\Container $container, $name, $label, $parents, $dataCallback) {
			$container[$name] = new \DependentSelectBox\DependentSelectBox($label, $parents, $dataCallback);
			return $container[$name];
		});

\Nette\Forms\Container::extensionMethod("addEditor", function(Nette\Forms\Container $container, $name, $label = NULL, $cols = 40, $rows = 80) {
			$container[$name] = new \Nette\Forms\Controls\TextArea($name, $label, $cols, $rows);
			$container[$name]->getControlPrototype()->data('venne-form-editor', true);
			return $container[$name];
		});

\Nette\Forms\Container::extensionMethod("addCheckboxList", function(Nette\Forms\Container $container, $name, $label, array $items = NULL) {
			return $container[$name] = new \Venne\Forms\Controls\CheckboxList($label, $items);
		});

\Nette\Forms\Container::extensionMethod("addTextWithSelect", function(Nette\Forms\Container $container, $name, $label, $cols = NULL, $maxLength = NULL) {
			$container[$name] = new \Venne\Forms\Controls\TextWithSelect($label, $cols, $maxLength);
			//$container[$name]->getControlPrototype()->data('venne-form-textwithselect', true);
			return $container[$name];
		});


