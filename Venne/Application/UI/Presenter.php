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

use Venne;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\PresenterComponentReflection;
use Nette\Application\ForbiddenRequestException;
use Venne\Templating\ITemplateConfigurator;
use Venne\Security\IComponentVerifier;

/**
 * Description of Presenter
 *
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read \SystemContainer|\Nette\DI\Container $context
 *
 * @method \SystemContainer|\Nette\DI\Container getContext() getContext()
 */
class Presenter extends \Nette\Application\UI\Presenter
{

	/** @var ITemplateConfigurator */
	protected $templateConfigurator;

	/** @var IComponentVerifier */
	protected $componentVerifer;



	public function __construct(\Nette\DI\Container $container)
	{
		parent::__construct($container);

		// template configurator
		if ($container->hasService('venne.templateConfigurator')) {
			$this->setTemplateConfigurator($container->venne->templateConfigurator);
		}

		// component verifer
		if ($container->hasService('venne.componentVerifier')) {
			$this->setComponentVerifer($container->venne->componentVerifier);
		}
	}



	/**
	 * @param ITemplateConfigurator $configurator
	 */
	public function setTemplateConfigurator(ITemplateConfigurator $configurator = NULL)
	{
		$this->templateConfigurator = $configurator;
	}



	/**
	 * @param \Venne\Security\IComponentVerifier $componentVerifer
	 */
	public function setComponentVerifer($componentVerifer)
	{
		$this->componentVerifer = $componentVerifer;
	}



	/**
	 * Checks authorization.
	 *
	 * @return void
	 */
	public function checkRequirements($element)
	{
		if ($this->componentVerifer && !$this->componentVerifer->isAllowed($element)) {
			throw new ForbiddenRequestException;
		}
	}



	/**
	 * @param string|null $class
	 *
	 * @return \Nette\Templating\Template
	 */
	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);

		if ($this->templateConfigurator !== NULL) {
			$this->templateConfigurator->configure($template);
		}

		return $template;
	}



	/**
	 * @param \Nette\Templating\Template $template
	 *
	 * @return void
	 */
	public function templatePrepareFilters($template)
	{
		if ($this->templateConfigurator !== NULL) {
			$this->templateConfigurator->prepareFilters($template);

		} else {
			$template->registerFilter(new \Nette\Latte\Engine);
		}
	}



	/**
	 * Component factory. Delegates the creation of components to a createComponent<Name> method.
	 *
	 * @param  string	  component name
	 * @return IComponent  the created component (optionally)
	 */
	public function createComponent($name)
	{
		$control = parent::createComponent($name);
		if ($control) {
			$method = 'createComponent' . ucfirst($name);
			if (method_exists($this, $method)) {
				$this->checkRequirements($this->getReflection()->getMethod($method));
			}

			return $control;
		}


		// widget from DIC
		$method = "create" . ucfirst($name) . "Widget";
		if (method_exists($this->context, $method)) {
			$context = $this->context;
			return new WidgetMultiplier(function() use ($context, $method)
			{
				return $context->$method();
			});
		}
	}



	/**
	 * Determines whether it links to the current page.
	 *
	 * @param  string   destination in format "[[module:]presenter:]action" or "signal!" or "this"
	 * @param  array|mixed
	 * @return bool
	 * @throws InvalidLinkException
	 */
	public function isLinkCurrent($destination = NULL, $args = array())
	{
		if ($destination !== NULL) {
			if (!is_array($args)) {
				$args = func_get_args();
				array_shift($args);
			}
			if (count($args) > 0) {
				return parent::isLinkCurrent($destination, $args);
			} else {
				if (substr($destination, 0, 1) !== ":") {
					$destination = strpos($destination, ":") === false
						? ":" . $this->name . ":" . $destination
						: ":" . substr($this->name, 0, strrpos($this->name, ":")) . ":" . $destination;
				}

				$reg = "/^" . str_replace("*", ".*", str_replace("#", "\/", $destination)) . "$/";
				return ((bool)preg_match($reg, ":" . $this->name . ":" . $this->view));
			}
		}
		return $this->getPresenter()->getLastCreatedRequestFlag('current');
	}



	/**
	 * Determines whether it URL to the current page.
	 *
	 * @param  string   $url
	 * @return bool
	 */
	public function isUrlCurrent($url)
	{
		$path = $this->getContext()->httpRequest->getUrl()->getPath();
		$basePath = $this->getContext()->httpRequest->getUrl()->getBasePath();
		$link = reset(explode("?", $url));

		if ($path == $basePath && $link == $basePath || (!$link && !$url) || ($link && $path == $link)) {
			return true;
		}
		return false;
	}



	/**
	 * @param type $destination
	 */
	public function isAllowed($destination)
	{
		if ($destination == "this") {
			$action = "action" . ucfirst($this->action);
			$class = $this->name;
		} else if (substr($destination, -1, 1) == "!") {
			$action = "handle" . ucfirst(substr($destination, 0, -1));
			$class = str_replace(":", "Module\\", $this->name) . "Presenter";
		} else {
			$destination = explode(":", $destination);
			if (count($destination) == 1) {
				$action = "action" . ucfirst($destination[count($destination) - 1]);
				$class = str_replace(":", "Module\\", $this->name) . "Presenter";
			} else {
				if ($destination[0] == "") {
					$action = "action" . ucfirst($destination[count($destination) - 1]);
					unset($destination[count($destination) - 1]);
					$destination = array_slice($destination, 1);
					$class = "\\";
					foreach ($destination as $key => $item) {
						if ($key > 0) {
							$class .= "\\";
						}
						if ($key == count($destination) - 1) {
							$class .= $item . "Presenter";
						} else {
							$class .= $item . "Module";
						}
					}
					$class = substr($class, 1);
				} else {
					$name = explode(":", $this->name);
					unset($name[count($name) - 1]);
					unset($destination[count($destination) - 1]);
					$name = implode(":", $name);
					$class = str_replace(":", "Module\\", $name) . "Module\\";
					foreach ($destination as $key => $item) {
						if ($key > 0) {
							$class .= "\\";
						}
						if ($key == count($destination) - 1) {
							$class .= $item . "Presenter";
						} else {
							$class .= $item . "Module";
						}
					}
				}
			}
		}
		return $this->user->isAllowed($class);
	}



	/**************************** services *********************************/


	/**
	 * Get AssetManager
	 *
	 * @return Venne\Assets\AssetManager
	 */
	public function getAssetManager()
	{
		return $this->getContext()->assets->assetManager;
	}


}

