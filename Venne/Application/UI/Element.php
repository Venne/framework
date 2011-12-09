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

use Venne;

/**
 * Description of Element
 *
 * @author Josef Kříž
 */
class Element extends Venne\Application\UI\Control implements IElement {


	/** @var integer */
	protected $key;

	/** @var string */
	protected $type;

	/** @var array of params */
	protected $params;



	public function __construct()
	{
		parent::__construct();
		//$this->findTemplateFile();
	}



	public function getKey()
	{
		return $this->key;
	}



	public function setKey($key)
	{
		$this->key = $key;
	}



	public function getType()
	{
		return $this->type;
	}



	public function setType($type)
	{
		$this->type = $type;
	}
	
	
	protected function attached($presenter)
	{
		parent::attached($presenter);
		$this->findTemplateFile();
	}



	/**
	 * @return array of string 
	 */
	public function findTemplateFile()
	{
		foreach ($this->formatTemplateFiles() as $item) {
			if (file_exists($item)) {
				$this->template->setFile($item);
				return;
			}
		}
	}



	/**
	 * Formats view template file names.
	 * @return array
	 */
	public function formatTemplateFiles()
	{
		$theme = $this->context->params["venneModeFront"] ? $this->context->params["website"]["theme"] : "admin";
		$dir = dirname($this->getReflection()->getFileName());
		$list = array(
			$this->getContext()->params["wwwDir"] . "/themes/" . $theme . "/" . ucfirst($this->type) . "Element/template.latte",
			$dir . "/template.latte"
		);
		return $list;
	}



	public function startup()
	{
		
	}



	public function setParams()
	{
		$params = func_get_args();
		if (isset($params[0])) {
			$this->params = $params[0];
		}
	}



	/**
	 * Gets the context.
	 * @return Nette\DI\IContainer
	 */
	public function getContext()
	{
		return $this->getPresenter()->getContext();
	}



	public function beforeRender()
	{
		
	}



	public function render()
	{
		$this->startup();
		$this->beforeRender();
		if (!file_exists($this->template->getFile()))
			throw new \Nette\FileNotFoundException("Template for element not found. Missing template '" . $this->template->getFile() . "'.");

		$this->template->venneModeAdmin = $this->getContext()->params['venneModeAdmin'];
		$this->template->venneModeFront = $this->getContext()->params['venneModeFront'];
		$this->template->venneModeInstallation = $this->getContext()->params['venneModeInstallation'];

		$this->template->render();
	}



	public function flashMessage($message, $type = 'info')
	{
		return $this->getPresenter()->flashMessage($message, $type);
	}

}
