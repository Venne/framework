<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Application\UI;

use Nette;
use Nette\Forms\ISubmitterControl;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @author Filip Procházka
 */
class Form extends \Nette\Application\UI\Form
{


	public function __construct()
	{
		parent::__construct();
		$this->attachHandlers();
		$this->getElementPrototype()->class[] = "venne-form";
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
		$this->startup();
		$this->setCurrentGroup();
		parent::attached($obj);
	}



	/**
	 * Returns a fully-qualified name that uniquely identifies the component
	 * within the presenter hierarchy.
	 *
	 * @return string
	 */
	public function getUniqueId()
	{
		return $this->lookupPath('Nette\Application\UI\Presenter', TRUE);
	}



	/**
	 * Automatically attach methods
	 */
	protected function attachHandlers()
	{
		if (method_exists($this, 'handleSuccess')) {
			$this->onSuccess[] = callback($this, 'handleSuccess');
		}

		if (method_exists($this, 'handleError')) {
			$this->onError[] = callback($this, 'handleError');
		}

		if (method_exists($this, 'handleValidate')) {
			$this->onValidate[] = callback($this, 'handleValidate');
		}

		foreach ($this->getComponents(TRUE, 'Nette\Forms\ISubmitterControl') as $submitControl) {
			$name = ucfirst((Nette\Utils\Strings::replace($submitControl->lookupPath('Nette\Forms\Form'), '~\-(.)~i', function ($m)
			{
				return strtoupper($m[1]);
			})));

			if (method_exists($this, 'handle' . $name . 'Click')) {
				$submitControl->onClick[] = callback($this, 'handle' . $name . 'Click');
			}

			if (method_exists($this, 'handle' . $name . 'InvalidClick')) {
				$submitControl->onInvalidClick[] = callback($this, 'handle' . $name . 'InvalidClick');
			}
		}
	}



	/**
	 * Fires submit/click events.
	 *
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

		foreach ((array)$listeners as $handler) {
			if ($handler instanceof Nette\Application\UI\Link) {
				$this->getPresenter()->redirectUrl($handler);
			} else {
				callback($handler)->invokeArgs($args);
			}
		}
	}



	/**
	 * Renders form.
	 *
	 * @return void
	 */
	public function render()
	{
		$args = new \Venne\Forms\Events\EventArgs();
		$args->setForm($this);

		$this->presenter->context->eventManager->dispatchEvent(\Venne\Forms\Events\Events::onBeforeRender, $args);
		parent::render();
	}

}

\Nette\Forms\Container::extensionMethod("addDynamic", function(Nette\Forms\Container $container, $name, $factory, $createDefault = 0)
{
	return $container[$name] = new \Venne\Forms\Containers\Replicator($factory, $createDefault);
});

\Nette\Forms\Container::extensionMethod("addTag", function(Nette\Forms\Container $container, $name, $label = NULL)
{
	$container[$name] = new \Venne\Forms\Controls\TagInput($label);
	return $container[$name];
});

\Nette\Forms\Container::extensionMethod("addDate", function(Nette\Forms\Container $container, $name, $label = NULL)
{
	$container[$name] = new \Venne\Forms\Controls\DateInput($label, \Venne\Forms\Controls\DateInput::TYPE_DATE);
	return $container[$name];
});

\Nette\Forms\Container::extensionMethod("addDatetime", function(Nette\Forms\Container $container, $name, $label = NULL)
{
	$container[$name] = new \Venne\Forms\Controls\DateInput($label, \Venne\Forms\Controls\DateInput::TYPE_DATETIME);
	return $container[$name];
});

\Nette\Forms\Container::extensionMethod("addTime", function(Nette\Forms\Container $container, $name, $label = NULL)
{
	$container[$name] = new \Venne\Forms\Controls\DateInput($label, \Venne\Forms\Controls\DateInput::TYPE_TIME);
	return $container[$name];
});

\Nette\Forms\Container::extensionMethod("addDependentSelectBox", function(Nette\Forms\Container $container, $name, $label, $parents, $dataCallback)
{
	$container[$name] = new \DependentSelectBox\DependentSelectBox($label, $parents, $dataCallback);
	return $container[$name];
});

\Nette\Forms\Container::extensionMethod("addEditor", function(Nette\Forms\Container $container, $name, $label = NULL, $cols = 40, $rows = 80)
{
	$container[$name] = new \Nette\Forms\Controls\TextArea($label, $cols, $rows);
	$container[$name]->getControlPrototype()->data('venne-form-editor', true);
	return $container[$name];
});

\Nette\Forms\Container::extensionMethod("addContentEditor", function(Nette\Forms\Container $container, $name, $label = NULL, $cols = 40, $rows = 80)
{
	$container[$name] = new \Venne\Forms\Controls\ContentEditor($label, $cols, $rows);
	$container[$name]->getControlPrototype()->data('venne-form-editor', true);
	return $container[$name];
});

\Nette\Forms\Container::extensionMethod("addCheckboxList", function(Nette\Forms\Container $container, $name, $label, array $items = NULL)
{
	return $container[$name] = new \Venne\Forms\Controls\CheckboxList($label, $items);
});

\Nette\Forms\Container::extensionMethod("addTextWithSelect", function(Nette\Forms\Container $container, $name, $label, $cols = NULL, $maxLength = NULL)
{
	$container[$name] = new \Venne\Forms\Controls\TextWithSelect($label, $cols, $maxLength);
	return $container[$name];
});


