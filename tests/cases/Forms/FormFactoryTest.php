<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Tests\Forms;

use Venne;
use Venne\Forms\Form;
use Venne\Testing\TestCase;

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


	/**
	 * @expectedException Nette\InvalidArgumentException
	 */
	public function testInvokeException()
	{
		$formFactory = clone $this->formFactory;

		$formFactory->invoke();
	}


	public function testInvoke()
	{
		$formFactory = clone $this->formFactory;

		$formFactory->injectFactory(new \Nette\Callback(function () {
			return new \Venne\Forms\Form;
		}));

		$this->assertInstanceOf('\Venne\Forms\Form', $formFactory->invoke());
		$this->assertInstanceOf('\Venne\Forms\Form', $formFactory->createForm());

		$form = $formFactory->createForm();
		$this->assertInstanceOf('\Venne\Tests\Forms\NullMapper', $form->getMapper());
	}


	public function testConfigure()
	{
		$formFactory = clone $this->formFactory;

		$this->assertNull($formFactory->status);

		$formFactory->injectFactory(new \Nette\Callback(function () {
			return new \Venne\Forms\Form;
		}));

		$formFactory->invoke();
		$this->assertEquals('configure', $formFactory->status);
	}
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
