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

use Venne\Forms\IControlExtension;
use Venne\Forms\IMapper;
use Venne\Forms\IObjectContainer;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Form extends \Nette\Application\UI\Form implements IObjectContainer, IContainer
{

	/** @var array */
	public $onLoad;

	/** @var array */
	public $onSave;

	/** @var array */
	public $onAttached;

	/** @var array */
	public $onBeforeRender;

	/** @var IMapper|NULL */
	protected $mapper;

	/** @var mixed */
	protected $data;

	/** @var IControlExtension[] */
	protected $controlExtensions = array();


	public function add()
	{
		$args = func_get_args();
		$type = $args[0];
		unset($args[0]);

		$method = 'add' . ucfirst($type);

		if (method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), $args);
		}

		if (isset($this->getForm()->controlExtensions[$type])) {
			$obj = $this->controlExtensions[$type];
			$args = array_merge(array($this->getForm()), $args);
			return call_user_func_array(array($obj, $method), $args);
		}

		throw new \Nette\InvalidArgumentException("Type '{$type}' not exists.");
	}


	/**
	 * @param $name
	 * @return \Nette\Forms\Controls\SubmitButton
	 */
	public function addSaveButton($name)
	{
		return $this->addSubmit('_submit', $name);
	}


	/**
	 * @return \Nette\Forms\Controls\SubmitButton
	 */
	public function getSaveButton()
	{
		return $this['_submit'];
	}


	/**
	 * @return bool
	 */
	public function hasSaveButton()
	{
		return isset($this['_submit']);
	}


	public function __call($name, $args)
	{
		if (substr($name, 0, 3) !== 'add') {
			return parent::__call($name, $args);
		}

		$args = array_merge(array(lcfirst(substr($name, 3))), $args);
		return call_user_func_array(array($this, 'add'), $args);
	}


	/**
	 * @param IMapper $mapper
	 */
	public function setMapper(IMapper $mapper = NULL)
	{
		$this->mapper = $mapper;

		if ($this->mapper) {
			$this->mapper->setForm($this);
		}
	}


	/**
	 * @return NULL|IMapper
	 */
	public function getMapper()
	{
		return $this->mapper;
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
	 * @param mixed $data
	 */
	public function setData($data)
	{
		$this->data = $data;
	}


	/**
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}


	/**
	 * @param IControlExtension $controlExtension
	 */
	public function addControlExtension(IControlExtension $controlExtension)
	{
		foreach ($controlExtension->getControls($this) as $type) {
			if (isset($this->controlExtensions[$type])) {
				throw new \Nette\InvalidArgumentException("Control type '{$type}' is already registered.");
			}

			$this->controlExtensions[$type] = $controlExtension;
		}
	}


	public function getControlExtensions()
	{
		return $this->controlExtensions;
	}


	/**
	 * @param \Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if ($this->mapper) {
			$this->mapper->assign($this->data, $this);
		}

		$this->onAttached($this);

		if ($obj instanceof \Nette\Application\UI\Presenter) {
			if (!$this->isSubmitted()) {
				if ($this->mapper) {
					$this->mapper->load();
				}
				$this->onLoad($this);
			}
		}
	}


	public function addContainer($name)
	{
		$control = new Container;
		$control->currentGroup = $this->currentGroup;
		return $this[$name] = $control;
	}


	/**
	 * Fires submit/click events.
	 * @return void
	 */
	public function fireEvents()
	{
		if (!($submittedBy = $this->isSubmitted())) {
			return;
		} elseif ($submittedBy instanceof \Nette\Forms\ISubmitterControl) {
			if (!$submittedBy->getValidationScope() || $this->isValid()) {
				$submittedBy->click();

				if ($this->mapper) {
					$this->mapper->save();
				}
				$this->onSave($this);
			} else {
				$submittedBy->onInvalidClick($submittedBy);
			}
		}

		if ($this->isValid() || $this->valid) {
			$this->onSuccess($this);
		} else {
			$this->onError($this);
		}
	}


	public function render()
	{
		$this->onBeforeRender($this);

		$args = func_get_args();
		call_user_func_array(array('parent', 'render'), $args);
	}


	public function __toString()
	{
		$this->onBeforeRender($this);

		return parent::__toString();
	}
}
