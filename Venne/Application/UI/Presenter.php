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
use Nette\Application\UI\InvalidLinkException;

/**
 * Description of Presenter
 *
 * @author Josef Kříž
 * 
 * @property-read \SystemContainer $context
 */
class Presenter extends \Nette\Application\UI\Presenter {


	const ROBOTS_INDEX = 1;
	const ROBOTS_NOINDEX = 2;
	const ROBOTS_FOLLOW = 4;
	const ROBOTS_NOFOLLOW = 8;

	/** @persistent */
	public $lang;

	/** @var array of array */
	protected $paths = array();

	/** @var string */
	public $keywords;

	/** @var string */
	public $description;

	/** @var array */
	public $js = array();

	/** @var array */
	public $css = array();

	/** @var string */
	public $robots;

	/** @var string */
	public $author;

	/** @var string */
	public $title;

	/** @var string */
	public $titleTemplate;

	/** @var string */
	public $titleSeparator;



	public function __construct(\Nette\DI\IContainer $context)
	{
		\Venne\Panels\Stopwatch::start();
		parent::__construct($context);
	}



	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();

		/* Language */
		if ($this->context->parameters["website"]["multilang"]) {
			if (!$this->lang) {
				$this->lang = $this->getDefaultLanguageAlias();
			}

			$httpResponse = $this->context->httpResponse;
			$httpResponse->setCookie("lang", $this->lang, 60 * 60 * 24 * 365);
		}

		/* Meta */
		$this->titleTemplate = $this->context->parameters["website"]["title"];
		$this->titleSeparator = $this->context->parameters["website"]["titleSeparator"];
		$this->author = $this->context->parameters["website"]["author"];
		$this->keywords = $this->context->parameters["website"]["keywords"];
		$this->description = $this->context->parameters["website"]["description"];

		\Venne\Panels\Stopwatch::stop("base startup");
		\Venne\Panels\Stopwatch::start();
	}



	/**
	 * Get theme
	 * @return \Venne\Templating\ITheme
	 */
	public function getTheme()
	{
		return $this->context->{$this->context->parameters["website"]["theme"] . "Theme"};
	}



	/**
	 * Get module
	 * @return \Venne\Module\IModule 
	 */
	public function getModule()
	{
		return $this->context->{$this->getModuleName() . "Theme"};
	}



	/**
	 * Get module name
	 * @return string
	 */
	public function getModuleName()
	{
		return lcfirst(substr($this->name, 0, strpos($this->name, ":")));
	}



	/**
	 * Checks authorization.
	 * @return void
	 */
	public function checkRequirements($element)
	{
		parent::checkRequirements($element);

		$methods = array();
		$methods[] = "startup";
		$methods[] = $this->formatActionMethod(ucfirst($this->getAction()));
		$signal = $this->getSignal();
		if ($signal) {
			$methods[] = $this->formatSignalMethod(ucfirst($signal[1]));
		}

		if (!$this->isMethodAllowed($methods)) {
			throw new \Nette\Application\ForbiddenRequestException;
		}
	}



	/**
	 * Checks authorization on methods.
	 * @param array $methods
	 * @return bool 
	 */
	protected function isMethodAllowed($methods)
	{
		$methods = (array) $methods;
		$ref = $this->getReflection();

		if ($ref->hasAnnotation("secured")) {
			$secured = $ref->getAnnotation("secured");

			if (isset($secured["resource"])) {
				$resource = $secured["resource"];
			} else {
				$resource = $ref->getName();
			}
			$resource = substr($resource, 0, 4) == "App\\" ? substr($resource, 4) : $resource;

			if (!$this->user->isAllowed($resource)) {
				return false;
			}

			foreach ($methods as $method) {
				if ($ref->hasMethod($method)) {
					$mRef = $ref->getMethod($method);
					if ($mRef->hasAnnotation("resource")) {
						$methodResource = $mRef->getAnnotation("resource");
					} else {
						$methodResource = $resource;
					}
					$methodResource = substr($methodResource, 0, 4) == "App\\" ? substr($methodResource, 4) : $methodResource;

					$methodResource .= $mRef->hasAnnotation("privilege") ? "\\" . $mRef->getAnnotation("privilege") : "";

					if ($methodResource != $resource && !$this->user->isAllowed($methodResource)) {
						return false;
					}
				}
			}
		}
		return true;
	}



	/**
	 * Common render method.
	 * @return void
	 */
	public function beforeRender()
	{
		\Venne\Panels\Stopwatch::stop("module startup and action");
		\Venne\Panels\Stopwatch::start();
		parent::beforeRender();

		$this->template->venneModeAdmin = $this->getContext()->parameters['venneModeAdmin'];
		$this->template->venneModeFront = $this->getContext()->parameters['venneModeFront'];
		$this->template->venneModeInstallation = $this->getContext()->parameters['venneModeInstallation'];
		$this->template->venneVersionId = VENNE_VERSION_ID;
		$this->template->venneVersionState = VENNE_VERSION_STATE;
	}



	/**
	 * @param  Nette\Application\IResponse  optional catched exception
	 * @return void
	 */
	public function shutdown($response)
	{
		parent::shutdown($response);
		\Venne\Panels\Stopwatch::stop("template render");
		if (\Nette\Diagnostics\Debugger::isEnabled()) {
			\Nette\Diagnostics\Debugger::addPanel(new \Venne\Panels\Stopwatch());
		}
		\Venne\Panels\Stopwatch::start();
	}



	/**
	 * Template factory.
	 * @param string $class
	 * @return \Nette\Templating\ITemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$template = $this->getContext()->templateContainer->createTemplate($this, $class);
		$this->theme->setTemplate($template);
		$this->theme->setMacros($this->context->latteEngine->parser);
		return $template;
	}



	/**
	 * Component factory. Delegates the creation of components to a createComponent<Name> method.
	 * @param  string      component name
	 * @return IComponent  the created component (optionally)
	 */
	public function createComponent($name)
	{
		$control = parent::createComponent($name);

		if ($control) {
			return $control;
		}

		$method = "create" . ucfirst($name) . "Control";
		if (method_exists($this->context, $method)) {
			return $this->context->$method();
		}
	}



	/**
	 * Formats layout template file names.
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		$theme = $this->context->parameters["venneModeFront"] ? $this->context->parameters["website"]["theme"] : "admin";
		$layout = "layout";
		$list = array(
			$this->getContext()->parameters["wwwDir"] . "/themes/$theme/layouts/@$layout.latte"
		);
		return $list;
	}



	/**
	 * Formats view template file names.
	 * @return array
	 */
	public function formatTemplateFiles()
	{
		$theme = $this->context->parameters["venneModeFront"] ? $this->context->parameters["website"]["theme"] : "admin";
		$name = $this->getName();
		$presenter = substr($name, strrpos(':' . $name, ':'));
		$dir = dirname(dirname($this->getReflection()->getFileName()));

		$path = str_replace(":", "Module/", substr($name, 0, strrpos($name, ":"))) . "Module";
		$subPath = substr($name, strrpos($name, ":") !== FALSE ? strrpos($name, ":") + 1 : 0);
		if ($path) {
			$path .= "/";
		}

		return array(
			$this->getContext()->parameters["wwwDir"] . "/themes/$theme/templates/$path$presenter/$this->view.latte",
			$this->getContext()->parameters["wwwDir"] . "/themes/$theme/templates/$path$presenter.$this->view.latte",
			"$dir/templates/$presenter/$this->view.latte",
			"$dir/templates/$presenter.$this->view.latte",
		);
	}



	/**
	 * Determines whether it links to the current page.
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
					if (strpos($destination, ":") === false) {
						$destination = ":" . $this->name . ":" . $destination;
					} else {
						$destination = ":" . substr($this->name, 0, strrpos($this->name, ":")) . ":" . $destination;
					}
				}

				$reg = "/^" . str_replace("*", ".*", str_replace("#", "\/", $destination)) . "$/";
				return ((bool) preg_match($reg, ":" . $this->name . ":" . $this->view));
			}
		}
		return $this->getPresenter()->getLastCreatedRequestFlag('current');
	}



	/**
	 * Determines whether it URL to the current page.
	 * @param  string   $url
	 * @return bool
	 */
	public function isUrlCurrent($url)
	{
		$url2 = $this->getContext()->httpRequest->getUrl()->getPath();
		$link = explode("?", $url);
		$basePath = $this->getContext()->httpRequest->getUrl()->getBasePath();

		if ($url2 == $basePath && $link[0] == $basePath) {
			return true;
		}

		if (!$link[0] && !$url) {
			return true;
		}

		if (!$link[0]) {
			return false;
		}

		if ($url2 == $link[0]) {
			return true;
		} else {
			return false;
		}
	}



	/**
	 * @param type $destination
	 */
	public function isAllowed($destination)
	{
		if ($this->context->params['venneModeInstallation'])
			return true;
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
					unset($name[count($name)-1]);
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



	/**
	 * @param string $text 
	 */
	public function setKeywords($text)
	{
		$this->keywords = $text;
	}



	/**
	 * @param string $text 
	 */
	public function setDescription($text)
	{
		$this->description = $text;
	}



	/**
	 * @param string $text 
	 */
	public function setTitle($text)
	{
		$this->title = $text;
	}



	/**
	 * @param string $text 
	 */
	public function setAuthor($text)
	{
		$this->author = $text;
	}



	/**
	 * @param string $content 
	 */
	public function addCss($content)
	{
		$this->css[$content] = $content;
	}



	/**
	 * @param string $content
	 */
	public function addJs($content)
	{
		$this->js[$content] = $content;
	}



	/**
	 * @param int|string $robots
	 */
	public function setRobots($robots)
	{
		if (is_numeric($robots)) {
			$arr = array();
			if ($robots & self::ROBOTS_INDEX)
				$arr[] = "index";
			if ($robots & self::ROBOTS_NOINDEX)
				$arr[] = "noindex";
			if ($robots & self::ROBOTS_FOLLOW)
				$arr[] = "follow";
			if ($robots & self::ROBOTS_NOFOLLOW)
				$arr[] = "nofollow";
			$this->robots = implode(", ", $arr);
		}else {
			$this->robots = $robots;
		}
	}



	/**
	 * @param string $name
	 * @param string $url 
	 */
	public function addPath($name, $url)
	{
		$this->paths[] = array("name" => $name, "url" => $url);
	}



	/**
	 * @return array
	 */
	public function getPaths()
	{
		return $this->paths;
	}

}

