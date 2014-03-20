<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace VenneTests\Forms;

use Tester\Assert;
use Tester\TestCase;
use Venne\Forms\Form;

require __DIR__ . '/../bootstrap.php';


/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FormFactoryTest extends TestCase
{


	/** @var FormFactory */
	protected $formFactory;


	public function setUp()
	{
		$this->formFactory = new FormFactory;
	}


	public function testInvokeException()
	{
		$formFactory = $this->formFactory;
		Assert::exception(function () use ($formFactory) {
			$formFactory->invoke();
		}, 'Nette\InvalidStateException');
	}


	public function testInvoke()
	{
		$formFactory = clone $this->formFactory;

		$formFactory->injectFactory(new BaseFormFactory);
		$form = $formFactory->invoke();

		Assert::type('\Venne\Forms\Form', $formFactory->invoke());
		Assert::type('\Venne\Forms\Form', $formFactory->invoke());
		Assert::type('\VenneTests\Forms\NullMapper', $form->getMapper());
	}


	public function testConfigure()
	{
		$formFactory = clone $this->formFactory;

		Assert::null($formFactory->status);

		$formFactory->injectFactory(new BaseFormFactory);

		$formFactory->invoke();

		Assert::equal('configure', $formFactory->status);
	}
}

class BaseFormFactory
{
	function create() { return new \Venne\Forms\Form; }
}

class FormFactory extends \Venne\Forms\FormFactory
{

	public $status;


	public function getMapper()
	{
		return new NullMapper;
	}


	public function getControlExtensions()
	{
		return array(
			new ControlExtension(),
		);
	}


	public function configure(Form $form)
	{
		$this->status = 'configure';
	}
}

class NullMapper implements \Venne\Forms\IMapper
{

	public function save()
	{
	}


	public function load()
	{
	}


	public function setForm(Form $form)
	{
	}


	public function assign($data, \Nette\ComponentModel\IComponent $component)
	{
	}
}

class ControlExtension implements \Venne\Forms\IControlExtension
{

	/**
	 * @return array
	 */
	public function getControls(Form $form)
	{
		return array('foo', 'bar');
	}


	public function addFoo($form, $a)
	{
		return $form[$a] = new \Nette\Forms\Controls\HiddenField();
	}


	public function addBar($form, $a, $b)
	{
		return $form[$a] = new \Nette\Forms\Controls\TextInput($b);
	}
}

$testCache = new FormFactoryTest;
$testCache->run();
