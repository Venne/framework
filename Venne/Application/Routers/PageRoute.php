<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Application\Routers;

use Nette\Object;
use Nette\Application\IRouter;
use Nette\Application;
use Venne\Doctrine\ORM\BaseRepository;
use Nette\Caching\Cache;
use Nette\Application\Routers\Route;

/**
 * @author Josef Kříž
 */
class PageRoute extends Route {


	const CACHE_MATCH_PREFIX = "match-";
	const CACHE_CONSTRUCT_PREFIX = "construct-";

	/** @var \Venne\Doctrine\ORM\BaseRepository */
	protected $langRepository;

	/** @var \Venne\Doctrine\ORM\BaseRepository */
	protected $pageRepository;

	/** @var \Nette\Caching\Cache */
	protected $cache;

	/** @var \App\CoreModule\CmsManager */
	protected $cmsManager;

	/** @var bool */
	protected $multilang;

	/** @var string */
	protected $defaultLangAlias;



	/**
	 * Constructor
	 * @param \App\CoreModule\CmsManager $cmsManager
	 * @param BaseRepository $pageRepository
	 * @param BaseRepository $langRepository
	 * @param \Nette\Caching\IStorage $cacheStorage
	 * @param string $prefix
	 * @param bool $multilang
	 * @param string $defaultLangAlias 
	 */
	public function __construct(\App\CoreModule\CmsManager $cmsManager, BaseRepository $pageRepository, BaseRepository $langRepository, $cacheStorage, $prefix = "", $multilang = false, $defaultLangAlias = false)
	{
		$this->multilang = $multilang;
		$this->defaultLangAlias = $defaultLangAlias;
		$this->langRepository = $langRepository;
		$this->pageRepository = $pageRepository;
		$this->cmsManager = $cmsManager;
		$this->cache = new Cache($cacheStorage, "Venne.Route");
		parent::__construct($prefix . "<url .+>[/<module Content>/<presenter Default>]", array(
			"presenter" => "Default",
			"module" => "Content",
			"action" => "default",
			"lang" => "",
			"url" => array(
				self::VALUE => "",
				self::FILTER_IN => NULL,
				self::FILTER_OUT => NULL,
				))
		);
	}



	/**
	 * Maps HTTP request to a Request object.
	 * @param  Nette\Http\IRequest
	 * @return Nette\Application\Request|NULL
	 */
	public function match(\Nette\Http\IRequest $httpRequest)
	{
		$request = parent::match($httpRequest);

		if ($request === NULL || !array_key_exists("url", $request->parameters)) {
			return NULL;
		}

		$parameters = $request->parameters;
		$parameters["url"] !== NULL ? : "";
		ksort($parameters);


		/* Cache */
		//if(($page = $this->loadMatchCache($parameters)) !== NULL){
		//	return $this->modifyMatchRequest($request, $page, $parameters);
		//}


		/* Search PageEntity */
		if ($this->multilang) {
			if (!isset($parameters["lang"])) {
				$parameters["lang"] = $this->defaultLangAlias;
			}

			try {
				$page = $this->pageRepository->createQueryBuilder("a")
								->leftJoin("a.languages", "p")
								->where("a.url = :url")
								->andWhere("p.alias = :lang")
								->setParameter("lang", $parameters["lang"])
								->setParameter("url", $parameters["url"])
								->getQuery()->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {
				return NULL;
			}
		} else {
			try {
				$page = $this->pageRepository->createQueryBuilder("a")
								->where("a.url = :url")
								->setParameter("url", $parameters["url"])
								->getQuery()->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {
				return NULL;
			}
		}


		/* make request */
		$this->saveMatchCache($page, $parameters);
		return $this->modifyMatchRequest($request, $page, $parameters);
	}



	/**
	 * Save page with parameters to cache
	 * @param \App\CoreModule\PageEntity $page
	 * @param array $parameters 
	 */
	protected function saveMatchCache(\App\CoreModule\PageEntity $page, array $parameters)
	{
		$cacheKey = json_encode($parameters);

		$this->cache->save(self::CACHE_MATCH_PREFIX . $cacheKey, array(
			"pageId" => $page->id,
		));
	}



	/**
	 * Load page by parameters from cache
	 * @param array $parameters
	 * @return \App\CoreModule\PageEntity
	 */
	protected function loadMatchCache(array $parameters)
	{
		$cacheKey = json_encode($parameters);

		$data = $this->cache->load(self::CACHE_MATCH_PREFIX . $cacheKey);
		if ($data) {
			$page = $this->pageRepository->find($data["pageId"]);
			return $page;
		}
		return NULL;
	}



	/**
	 * Modify request by page
	 * @param \Nette\Application\Request $appRequest
	 * @param \App\CoreModule\PageEntity $page 
	 * @return \Nette\Application\Request
	 */
	protected function modifyMatchRequest(\Nette\Application\Request $appRequest, \App\CoreModule\PageEntity $page, $parameters)
	{
		$parameters = $page->params + $parameters;
		$parameters["page"] = $page;
		$type = explode(":", $page->type);
		$parameters["action"] = $type[count($type) - 1];
		$parameters["lang"] = $appRequest->parameters["lang"];
		unset($type[count($type) - 1]);
		$presenter = join(":", $type);
		$appRequest->setParameters($parameters);
		$appRequest->setPresenterName($presenter);
		return $appRequest;
	}



	/**
	 * Constructs absolute URL from Request object.
	 * @param  Nette\Application\Request
	 * @param  Nette\Http\Url
	 * @return string|NULL
	 */
	public function constructUrl(\Nette\Application\Request $appRequest, \Nette\Http\Url $refUrl)
	{
		$parameters = $appRequest->getParameters();
		
		if (!array_key_exists("url", $parameters)) {
			return NULL;
		}

		$presenter = $appRequest->getPresenterName();
		$parameters[self::PRESENTER_KEY] = $presenter;

		$a = strrpos($presenter, ':');
		if ($a !== false) {
			$parameters[self::MODULE_KEY] = substr($presenter, 0, $a);
			$parameters[self::PRESENTER_KEY] = substr($presenter, $a + 1);
		} else {
			$parameters[self::MODULE_KEY] = '';
		}

		if ($parameters["url"] === NULL) {
			$parameters["url"] = "";
		}

		foreach ($parameters as $key => $param) {
			if ($param === NULL) {
				unset($parameters[$key]);
			}
		}

		$type = $parameters[self::MODULE_KEY] . ":" . $parameters[self::PRESENTER_KEY] . ":" . $parameters["action"];

		if (!$this->cmsManager->hasContentType($type)) {
			return NULL;
		}


		/* get page params */
		$urlParameters = array();
		$contentParameters = $this->cmsManager->getContentParams($type);
		foreach ($contentParameters as $item) {
			$urlParameters[$item] = $parameters[$item];
		}
		ksort($urlParameters);
		$parametersKey = json_encode($urlParameters);


		/* Cache */
		//if(($page = $this->loadConstructCache($urlParameters)) !== NULL){
		//	$this->modifyConstructRequest($appRequest, $page, $parameters);
		//	return parent::constructUrl($appRequest, $refUrl);
		//}

		
		/* Search PageEntity */
		if ($this->multilang) {
			if (!isset($params["lang"])) {
				$lang = $this->defaultLangAlias;
			}
			try {
				$page = $this->pageRepository->createQueryBuilder("a")
								->leftJoin("a.languages", "p")
								->where("a.type = :type")
								->andWhere("p.alias = :lang")
								->andWhere("a.params = :params")
								->setParameter("type", $type)
								->setParameter("lang", $params["lang"])
								->setParameter("params", $parametersKey)
								->getQuery()->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {
				return NULL;
			}
		} else {
			try {
				$page = $this->pageRepository->createQueryBuilder("a")
								->where("a.type = :type")
								->andWhere("a.params = :params")
								->setParameter("type", $type)
								->setParameter("params", $parametersKey)
								->getQuery()->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {
				return NULL;
			}
		}


		/* make request */
		$this->saveConstructCache($page, $urlParameters);
		$this->modifyConstructRequest($appRequest, $page, $parameters);
		return parent::constructUrl($appRequest, $refUrl);
	}



	/**
	 * Save page with parameters to cache
	 * @param \App\CoreModule\PageEntity $page
	 * @param array $parameters 
	 */
	protected function saveConstructCache(\App\CoreModule\PageEntity $page, array $parameters)
	{
		$cacheKey = json_encode($parameters);

		$this->cache->save(self::CACHE_MATCH_PREFIX . $cacheKey, array(
			"pageId" => $page->id,
		));
	}



	/**
	 * Load page by parameters from cache
	 * @param array $parameters
	 * @return \App\CoreModule\PageEntity
	 */
	protected function loadConstructCache(array $parameters)
	{
		$cacheKey = json_encode($parameters);

		$data = $this->cache->load(self::CACHE_MATCH_PREFIX . $cacheKey);
		if ($data) {
			$page = $this->pageRepository->find($data["pageId"]);
			return $page;
		}
		return NULL;
	}



	/**
	 * Modify request by page
	 * @param \Nette\Application\Request $request
	 * @param \App\CoreModule\PageEntity $page 
	 * @return \Nette\Application\Request
	 */
	protected function modifyConstructRequest(\Nette\Application\Request $request, \App\CoreModule\PageEntity $page, $parameters)
	{
		$parameters = ((array) $page->params) + $parameters;

		if (isset($parameters["page"])) {
			unset($parameters["page"]);
		}
		$parameters["module"] = "Content";
		$parameters["presenter"] = "Default";
		$parameters["url"] = $page->url;

		//dump($parameters);
		//die();

		$request->setPresenterName("Content:Default");
		$request->setParameters($parameters);
		return $request;
	}

}
