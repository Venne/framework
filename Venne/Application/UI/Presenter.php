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
 * Description of Presenter
 *
 * @author Josef Kříž
 * 
 * @property-read Venne\Application\Container $context
 */
class Presenter extends \Nette\Application\UI\Presenter {


	/** @persistent */
	public $lang;

	const ROBOTS_INDEX = 1;
	const ROBOTS_NOINDEX = 2;
	const ROBOTS_FOLLOW = 4;
	const ROBOTS_NOFOLLOW = 8;

	/* current module */

	protected $moduleName;

	/** @var array of array */
	protected $paths = array();

	/* vars for template */
	public $keywords;
	public $description;
	public $js = array();
	public $css = array();
	public $robots;
	public $author;
	public $title;
	public $titleTemplate;
	public $titleSeparator;



	public function getTheme()
	{
		return $this->context->themes->{$this->context->params["website"]["theme"]};
	}



	/**
	 * @return Doctrine\ORM\EntityManager 
	 */
	public function getEntityManager()
	{
		return $this->getContext()->doctrineContainer->entityManager;
	}



	/**
	 * @param \Nette\Application\UI\PresenterComponentReflection $element 
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



	public function isMethodAllowed($methods)
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


		if (!$this->getReflection()->hasMethod($method)) {
			return true;
		}

		$data = \App\SecurityModule\Authorizator::parseAnnotations(get_called_class(), $method);

		if ($data[\App\SecurityModule\Authorizator::RESOURCE] === NULL) {
			return true;
		}

		if (!$this->user->isAllowed($data[\App\SecurityModule\Authorizator::RESOURCE], $data[\App\SecurityModule\Authorizator::PRIVILEGE])) {
			return false;
		}

		return true;
	}



	/**
	 * @return string
	 */
	protected function getDefaultLanguageAlias()
	{
		$httpRequest = $this->context->httpRequest;

		$lang = $httpRequest->getCookie('lang');
		if (!$lang) {
			$languages = array();
			foreach ($this->context->languageRepository->findAll() as $entity) {
				$languages[] = $entity->alias;
			}
			$lang = $httpRequest->detectLanguage($languages);
			if (!$lang) {
				$lang = $container->params["website"]["defaultLangAlias"];
			}
		}
		return $lang;
	}



	public function startup()
	{
		parent::startup();

		$this["element_panel"];

		/*
		 * Module
		 */
		$this->moduleName = lcfirst(substr($this->name, 0, strpos($this->name, ":")));

		/*
		 * Language
		 */
		if ($this->context->params["website"]["multilang"]) {
			$httpResponse = $this->context->httpResponse;

			if (!$this->lang) {
				$this->lang = $this->getDefaultLanguageAlias();
			}
			$httpResponse->setCookie("lang", $this->lang, 60 * 60 * 24 * 365);
		}

		/*
		 * Translator
		 */
		//$this->getContext()->translatorPanel;
		//if(file_exists(APP_DIR . "/".$this->module."Module/lang/".$this->getContext()->translator->getLang().".mo")){
//			$this->getContext()->translator->addDictionary($this->module."Module", APP_DIR . "/".$this->module."Module/");
//		}
//		if(file_exists(WWW_DIR . "/templates/".$this->getContext()->cms->website->getTemplateName()."/".$this->getContext()->translator->getLang().".mo")){
//			$this->getContext()->translator->addDictionary($this->getContext()->cms->website->getTemplateName().'Template', WWW_DIR . "/templates/".$this->getContext()->cms->website->getTemplateName());
//		}

		/* Meta */
		$this->titleTemplate = $this->context->params["website"]["title"];
		$this->titleSeparator = $this->context->params["website"]["titleSeparator"];
		$this->author = $this->context->params["website"]["author"];
		$this->keywords = $this->context->params["website"]["keywords"];
		$this->description = $this->context->params["website"]["description"];
	}



	public function beforeRender()
	{
		parent::beforeRender();

		$this->getTheme()->setMacros($this->context->latteEngine->parser);

		$this->template->venneModeAdmin = $this->getContext()->params['venneModeAdmin'];
		$this->template->venneModeFront = $this->getContext()->params['venneModeFront'];
		$this->template->venneModeInstallation = $this->getContext()->params['venneModeInstallation'];
		$this->template->venneVersionId = VENNE_VERSION_ID;
		$this->template->venneVersionState = VENNE_VERSION_STATE;

		\Venne\Panels\Stopwatch::start();
	}



	/**
	 * If Debugger is enabled, print template variables to debug bar
	 */
	protected function afterRender()
	{
		parent::afterRender();

		if (\Nette\Diagnostics\Debugger::isEnabled()) { // todo: as panel
			\Nette\Diagnostics\Debugger::barDump($this->template->getParams(), 'Template variables');
			$this->context->translatorPanel;
		}
	}



	public function shutdown($response)
	{
		parent::shutdown($response);
		\Venne\Panels\Stopwatch::stop("render template");
	}



	/**
	 * Template factory.
	 * @param string $class
	 * @return \Nette\Templating\ITemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$template = $this->getContext()->templateContainer->createTemplate($this, $class);
		$this->getTheme()->setTemplate($template);
		return $template;
	}



	/**
	 * Component factory. Delegates the creation of components to a createComponent<Name> method.
	 * @param  string      component name
	 * @return IComponent  the created component (optionally)
	 */
	public function createComponent($name)
	{
		if (substr($name, 0, 8) == "element_") {
			$nameArr = explode("_", $name, 3);
			$control = $this->context->cmsManager->getElementInstance($nameArr[1], array($nameArr[1], isset($nameArr[2]) ? $nameArr[2] : NULL));
			$control->setType($nameArr[1]);
			$control->setKey(isset($nameArr[2]) ? $nameArr[2] : NULL);
			return $control;
		} else {
			return parent::createComponent($name);
		}
	}



	/**
	 * Formats layout template file names.
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		$theme = $this->context->params["venneModeFront"] ? $this->context->params["website"]["theme"] : "admin";
		$layout = "layout";
		$list = array(
			$this->getContext()->params["wwwDir"] . "/themes/$theme/layouts/@$layout.latte"
		);
		return $list;
	}



	/**
	 * Formats view template file names.
	 * @return array
	 */
	public function formatTemplateFiles()
	{
		$theme = $this->context->params["venneModeFront"] ? $this->context->params["website"]["theme"] : "admin";
		$name = $this->getName();
		$presenter = substr($name, strrpos(':' . $name, ':'));
		$dir = dirname(dirname($this->getReflection()->getFileName()));

		$path = str_replace(":", "Module/", substr($name, 0, strrpos($name, ":"))) . "Module";
		$subPath = substr($name, strrpos($name, ":") !== FALSE ? strrpos($name, ":") + 1 : 0);
		if ($path) {
			$path .= "/";
		}

		return array(
			$this->getContext()->params["wwwDir"] . "/themes/$theme/templates/$path$presenter/$this->view.latte",
			$this->getContext()->params["wwwDir"] . "/themes/$theme/templates/$path$presenter.$this->view.latte",
			"$dir/templates/$presenter/$this->view.latte",
			"$dir/templates/$presenter.$this->view.latte",
		);
	}



	/**
	 * @return string
	 */
	public function getModuleName()
	{
		return $this->moduleName;
	}



	/**
	 * @param string $moduleName
	 * @return \Venne\Module\IModule
	 */
	public function getModule($moduleName = NULL)
	{
		if (!$moduleName) {
			$moduleName = $this->moduleName;
		}

		return $this->context->modules->{$moduleName};
	}



	/**
	 * @param type $destination 
	 */
	public function isAllowed($destination)
	{
		return true;
		if ($this->getContext()->params['venneModeInstallation'])
			return true;
		if ($destination == "this") {
			$action = "action" . ucfirst($this->action);
			$class = $this;
		} else if (substr($destination, -1, 1) == "!") {
			$action = "handle" . ucfirst(substr($destination, 0, -1));
			$class = $this;
		} else {
			$destination = explode(":", $destination);
			if (count($destination) == 1) {
				$action = "action" . ucfirst($destination[count($destination) - 1]);
				$class = $this;
			} else {
				$action = "action" . ucfirst($destination[count($destination) - 1]);
				unset($destination[count($destination) - 1]);
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
			}
		}

		$annot = $this->getContext()->authorizator->getClassResource($class);
		if ($annot) {
			if (!$this->getUser()->isAllowed($annot)) {
				return false;
			}
		}

		$annot = $this->getContext()->authorizator->getMethodResource($class, $action);
		if ($annot) {
			if (!$this->getUser()->isAllowed($annot)) {
				return false;
			}
		}
		return true;
	}



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

		if (strpos($url2, $link[0]) === 0 && $link[0] != $this->getContext()->httpRequest->getUrl()->getBasePath()) {
			return true;
		} else {
			return false;
		}
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

