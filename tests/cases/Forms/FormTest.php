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
class FormTest extends TestCase
{


	/** @var Form */
	protected $form;


	public function setUp()
	{
		$this->form = new Form;
		$this->form->addSubmit('submit');
		$this->form->addControlExtension(new ControlExtension());
		$this->form->setMapper(new Mapper);
	}


	public function testAdd()
	{
		$this->form->add('text', 'foo');
		$this->form->add('textArea', 'bar');

		$this->assertInstanceOf('Nette\Forms\Controls\TextInput', $this->form['foo']);
		$this->assertInstanceOf('Nette\Forms\Controls\TextArea', $this->form['bar']);

		$this->assertEquals('foo', $this->form['foo']->getName());
		$this->assertEquals('bar', $this->form['bar']->getName());

		unset($this->form['foo']);
		unset($this->form['bar']);
	}


	/**
	 * @expectedException Nette\InvalidArgumentException
	 */
	public function testAddException()
	{
		$this->form->add('text2', 'foo');
	}


	public function testAddSaveButton()
	{
		$this->assertFalse($this->form->hasSaveButton());

		$this->form->addSaveButton('Upload');

		$this->assertTrue($this->form->hasSaveButton());
		$this->assertEquals('Upload', $this->form->getSaveButton()->caption);

		unset($this->form['_save']);
	}


	public function testControlExtension()
	{
		$this->form->addFoo('a');
		$this->form->addBar('b', 'c');

		$this->assertInstanceOf('Nette\Forms\Controls\HiddenField', $this->form['a']);
		$this->assertInstanceOf('Nette\Forms\Controls\TextInput', $this->form['b']);
	}


	/**
	 * @expectedException Nette\InvalidArgumentException
	 */
	public function testControlExtensionException()
	{
		$this->form->addUndefined('a');
	}


	public function testSetMapper()
	{
		$this->assertEquals($this->form, Mapper::$form);
	}

	public function testMapper()
	{
		$presenter = new \Venne\Application\UI\Presenter();
		$presenter->addComponent($this->form, 'form');
		$this->assertEquals('load', Mapper::$status);

		$this->form->setSubmittedBy($this->form['submit']);
		$this->form->fireEvents();
		$this->assertEquals('save', Mapper::$status);
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


class Mapper implements \Venne\Forms\IMapper
{

	public static $status;

	public static $form;

	public static $data;

	public static $component;


	public function save()
	{
		self::$status = 'save';
	}


	public function load()
	{
		self::$status = 'load';
	}


	public function setForm(Form $form)
	{
		self::$form = $form;
	}


	public function assign($data, \Nette\ComponentModel\IComponent $component)
	{
		self::$status = 'assign';
		self::$data = $data;
		self::$component = $component;
	}
}


