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

/**
 * @author Josef Kříž
 */
class DbRoute extends Application\Routers\Route {


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
	 * @param \Venne\Doctrine\ORM\BaseRepository $repository
	 * @param string $prefix 
	 */
	public function __construct(\App\CoreModule\CmsManager $cmsManager, BaseRepository $pageRepository, BaseRepository $langRepository, $cacheStorage, $prefix = "", $multilang = false, $defaultLangAlias = false)
	{
		$this->multilang = $multilang;
		$this->defaultLangAlias = $defaultLangAlias;
		$this->langRepository = $langRepository;
		$this->pageRepository = $pageRepository;
		$this->cmsManager = $cmsManager;
		$this->cache = new Cache($cacheStorage, "Venne.Route");
		parent::__construct($prefix . "[<url .+>][/<module>/<presenter>]", array("presenter" => "Default", "module" => "Content", "action" => "default", "lang" => NULL));
	}



	/**
	 * Maps HTTP request to a Request object.
	 * @param  Nette\Http\IRequest
	 * @return Nette\Application\Request|NULL
	 */
	public function match(\Nette\Http\IRequest $httpRequest)
	{
		$ret = parent::match($httpRequest);

		if ($ret === NULL || !array_key_exists("url", $ret->params)) {
			return NULL;
		}

		$params = $ret->params;

		if ($params["url"] === NULL) {
			$params["url"] = "";
		}

		/*
		 * Cache
		 */
//		ksort($params);
//		$cacheKey = json_encode($params);
//		$data = $this->cache->load(self::CACHE_MATCH_PREFIX . $cacheKey);
//		if ($data) {
//			$page = $this->pageRepository->find($data["pageId"]);
//			$params = $page->params;
//			$params["page"] = $page;
//			$type = explode(":", $page->type);
//			$params["action"] = $type[count($type) - 1];
//			$params["lang"] = $ret->params["lang"];
//			unset($type[count($type) - 1]);
//			$presenter = join(":", $type);
//			$ret->setParams($params);
//			$ret->setPresenterName($presenter);
//			return $ret;
//		}


		/*
		 * Search PageEntity
		 */
		if ($this->multilang) {
			if (!isset($params["lang"])) {
				$params["lang"] = $this->defaultLangAlias;
			}

			try {
				$page = $this->pageRepository->createQueryBuilder("a")
								->leftJoin("a.languages", "p")
								->where("a.url = :url")
								->andWhere("p.alias = :lang")
								->setParameter("lang", $params["lang"])
								->setParameter("url", $params["url"])
								->getQuery()->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {
				try {
					$page = $this->pageRepository->createQueryBuilder("a")
									->leftJoin("a.languages", "p")
									->leftJoin("a.translations", "b")
									->leftJoin("a.translationFor", "c")
									->leftJoin("c.translations", "d")
									->where("b.url = :url")
									->orWhere("c.url = :url")
									->orWhere("d.url = :url")
									->andWhere("p.alias = :lang")
									->setParameter("url", $params["url"])
									->setParameter("lang", $params["lang"])
									->getQuery()->getSingleResult();
				} catch (\Doctrine\ORM\NoResultException $e) {
					return NULL;
				}
			}
		} else {
			try {
				$page = $this->pageRepository->createQueryBuilder("a")
								->where("a.url = :url")
								->setParameter("url", $params["url"])
								->getQuery()->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {
				return NULL;
			}
		}


		$params = (array) $page->params;
		$type = explode(":", $page->type);
		$params["action"] = $type[count($type) - 1];
		unset($type[count($type) - 1]);
		$presenter = join(":", $type);


		$params["lang"] = $ret->params["lang"];

		$params = $params + $ret->getParams();

//		$this->cache->save(self::CACHE_MATCH_PREFIX . $cacheKey, array(
//			"pageId" => $page->id,
//		));

		$params["page"] = $page;

		$ret->setParams($params);
		$ret->setPresenterName($presenter);

		return $ret;
	}



	/**
	 * Constructs absolute URL from Request object.
	 * @param  Nette\Application\Request
	 * @param  Nette\Http\Url
	 * @return string|NULL
	 */
	public function constructUrl(\Nette\Application\Request $appRequest, \Nette\Http\Url $refUrl)
	{
		$params = $appRequest->getParams();

		if (!array_key_exists("url", $params)) {
			return NULL;
		}

		$presenter = $appRequest->getPresenterName();
		$params[self::PRESENTER_KEY] = $presenter;

		$a = strrpos($presenter, ':');

		if ($a !== false) {
			$params[self::MODULE_KEY] = substr($presenter, 0, $a);
			$params[self::PRESENTER_KEY] = substr($presenter, $a + 1);
		} else {
			$params[self::MODULE_KEY] = '';
		}

		if ($params["url"] === NULL) {
			$params["url"] = "";
		}

		foreach ($params as $key => $param) {
			if ($param === NULL) {
				unset($params[$key]);
			}
		}

//		ksort($params);
//		$cacheKey = json_encode($params);
//		$data = $this->cache->load(self::CACHE_CONSTRUCT_PREFIX . $cacheKey);
//		if ($data) {
//			$appRequest->setPresenterName("Content:Default");
//			$appRequest->setParams($data["params"]);
//			return parent::constructUrl($appRequest, $refUrl);
//		}

		$type = $params[self::MODULE_KEY] . ":" . $params[self::PRESENTER_KEY] . ":" . $params["action"];


		if (!$this->cmsManager->hasContentType($type)) {
			return NULL;
		}

		$contentParams = $this->cmsManager->getContentParams($type);

		$params2 = array();
		foreach ($contentParams as $item) {
			$params2[$item] = $params[$item];
		}
		ksort($params2);
		$params2 = json_encode($params2);

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
								->setParameter("params", $params2)
								->getQuery()->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {
				try {
					$page = $this->pageRepository->createQueryBuilder("a")
									->leftJoin("a.languages", "p")
									->leftJoin("a.translations", "b")
									->leftJoin("a.translationFor", "c")
									->leftJoin("c.translations", "d")
									->where("b.type = :type")
									->orWhere("c.type = :type")
									->orWhere("d.type = :type")
									->andWhere("p.alias = :lang")
									->andWhere("b.params = :params OR c.params = :params OR d.params = :params")
									->setParameter("type", $type)
									->setParameter("params", $params2)
									->setParameter("lang", $params["lang"])
									->getQuery()->getSingleResult();
				} catch (\Doctrine\ORM\NoResultException $e) {
					return NULL;
				}
			}
		} else {
			try {
				$page = $this->pageRepository->createQueryBuilder("a")
								->where("a.type = :type")
								->andWhere("a.params = :params")
								->setParameter("type", $type)
								->setParameter("params", $params2)
								->getQuery()->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {
				return NULL;
			}
		}

		$entityParams = (array) $page->params;

		$params = $entityParams + $params;

		foreach (array("page", "module", "presenter") as $item) {
			if (isset($params[$item])) {
				unset($params[$item]);
			}
		}
		$params["module"] = "Content";
		$params["presenter"] = "Default";
		$appRequest->setPresenterName("Content:Default");
		$appRequest->setParams($params);


//		$this->cache->save(self::CACHE_CONSTRUCT_PREFIX . $cacheKey, array(
//			"params" => $params,
//		));

		return parent::constructUrl($appRequest, $refUrl);
	}

}
