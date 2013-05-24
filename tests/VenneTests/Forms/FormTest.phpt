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

		Assert::type('Nette\Forms\Controls\TextInput', $this->form['foo']);
		Assert::type('Nette\Forms\Controls\TextArea', $this->form['bar']);

		Assert::equal('foo', $this->form['foo']->getName());
		Assert::equal('bar', $this->form['bar']->getName());

		unset($this->form['foo']);
		unset($this->form['bar']);
	}


	public function testAddException()
	{
		$form = $this->form;
		Assert::exception(function () use ($form) {
			$form->add('text2', 'foo');
		}, 'Nette\InvalidArgumentException');
	}


	public function testAddSaveButton()
	{
		Assert::false($this->form->hasSaveButton());

		$this->form->addSaveButton('Upload');

		Assert::true($this->form->hasSaveButton());
		Assert::equal('Upload', $this->form->getSaveButton()->caption);

		unset($this->form['_save']);
	}


	public function testControlExtension()
	{
		$this->form->addFoo('a');
		$this->form->addBar('b', 'c');

		Assert::type('Nette\Forms\Controls\HiddenField', $this->form['a']);
		Assert::type('Nette\Forms\Controls\TextInput', $this->form['b']);
	}


	public function testControlExtensionException()
	{
		$form = $this->form;
		Assert::exception(function () use ($form) {
			$form->addUndefined('a');
		}, 'Nette\InvalidArgumentException');
	}


	public function testSetMapper()
	{
		Assert::same($this->form, Mapper::$form);
	}


	public function testMapper()
	{
		$presenter = new \Venne\Application\UI\Presenter();
		$presenter->addComponent($this->form, 'form');
		Assert::equal('load', Mapper::$status);

		$this->form->setSubmittedBy($this->form['submit']);
		$this->form->fireEvents();
		Assert::equal('save', Mapper::$status);
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

\run(new FormTest);
