<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Forms;

use Nette\Callback;
use Nette\InvalidStateException;
use Nette\Utils\Strings;
use Venne\BaseFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FormFactory extends BaseFactory
{

	/** @var Callback */
	protected $factory;


	/**
	 * @param Callback|NULL $formFactory
	 */
	public function injectFactory($factory = NULL)
	{
		$this->factory = Callback::create($factory);
	}


	/**
	 * @param null $data
	 * @return Form
	 * @throws \Nette\InvalidStateException
	 */
	public function invoke($data = NULL)
	{
		if (!$this->factory) {
			throw new InvalidStateException('Form factory has not been set');
		}

		/** @var $form Form */
		$form = $this->factory->invoke();

		if ($data) {
			$form->setData($data);
		}

		$form->setMapper($this->getMapper());
		foreach ($this->getControlExtensions() as $controlExtension) {
			$form->addControlExtension($controlExtension);
		}

		$this->configure($form);
		$this->attachHandlers($form);

		return $form;
	}


	/**
	 * @param null $data
	 * @return Form
	 * @deprecated
	 */
	public function createForm($data = NULL)
	{
		trigger_error(__METHOD__ . '() is deprecated; use create() instead.', E_USER_WARNING);

		return $this->__invoke($data);
	}


	/**
	 * Automatically attach methods
	 */
	protected function attachHandlers($form)
	{
		if (method_exists($this, 'handleSuccess')) {
			$form->onSuccess[] = callback($this, 'handleSuccess');
		}

		if (method_exists($this, 'handleError')) {
			$form->onError[] = callback($this, 'handleError');
		}

		if (method_exists($this, 'handleValidate')) {
			$form->onValidate[] = callback($this, 'handleValidate');
		}

		if (method_exists($this, 'handleSave')) {
			$form->onSave[] = callback($this, 'handleSave');
		}

		if (method_exists($this, 'handleLoad')) {
			$form->onLoad[] = callback($this, 'handleLoad');
		}

		if (method_exists($this, 'handleAttached')) {
			$form->onAttached[] = callback($this, 'handleAttached');
		}

		if (method_exists($this, 'handleBeforeRender')) {
			$form->onBeforeRender[] = callback($this, 'handleBeforeRender');
		}

		foreach ($form->getComponents(TRUE, 'Nette\Forms\ISubmitterControl') as $submitControl) {
			$name = ucfirst((Strings::replace($submitControl->lookupPath('Nette\Forms\Form'), '~\-(.)~i', function ($m) {
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
	 * @param $form Form
	 */
	protected function configure(Form $form)
	{
	}


	protected function getMapper()
	{
		return NULL;
	}


	protected function getControlExtensions()
	{
		return array();
	}
}
