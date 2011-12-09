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
class SeoRoute implements IRouter {

	const CACHE_MATCH_PREFIX = "match-";
	const CACHE_CONSTRUCT_PREFIX = "construct-";

	/** @var \Venne\Doctrine\ORM\BaseRepository */
	protected $langRepository;

	/** @var \Venne\Doctrine\ORM\BaseRepository */
	protected $pageRepository;

	/** @var \Nette\Caching\Cache */
	protected $cache;
	
	/** @var string */
	protected $mask;



	/**
	 * @param \Venne\Doctrine\ORM\BaseRepository $repository
	 * @param string $prefix 
	 */
	public function __construct(BaseRepository $pageRepository, BaseRepository $langRepository, $cacheStorage, $prefix = "")
	{
		$this->langRepository = $langRepository;
		$this->pageRepository = $pageRepository;
		$this->cache = new Cache($cacheStorage, "Venne.Route");
		$this->mask = $prefix . "<url .+>";
	}



	/**
	 * Maps HTTP request to a Request object.
	 * @param  Nette\Http\IRequest
	 * @return Nette\Application\Request|NULL
	 */
	public function match(\Nette\Http\IRequest $httpRequest)
	{
		$ret = parent::match($httpRequest);

		if ($ret === NULL || !isset($ret->params["url"])) {
			return NULL;
		}

		$params = $ret->params;
		ksort($params);
		$ret->setParams($params);
		$key = json_encode($ret->params);

		$data = $this->cache->load(self::CACHE_MATCH_PREFIX.$key);

		if ($data) {
			$ret->setParams($data["params"]);
			$ret->setPresenterName($data["presenter"]);
			return $ret;
		}

		$url = $ret->params["url"];

		if (isset($ret->params["lang"])) {
			$page = NULL;

			$pages = $this->pageRepository->findBy(array("url" => $url));
			//dump($url);
			//dump($pages);
			//die();
			if (!$pages) {
				return NULL;
			}
			foreach ($pages as $pageEntity) {
				foreach ($pageEntity->languages as $language) {
					if ($language->alias == $ret->params["lang"]) {
						$page = $pageEntity;
						break;
					}
				}
				if ($page) {
					break;
				}
			}

			if (!$page) {
				$ok = false;
				$pageEntity = $pages[0];

				if ($pageEntity->translationFor instanceof \App\CoreModule\PageEntity) {
					$pageEntity = $pageEntity->translationFor;
					foreach ($pageEntity->languages as $language) {
						if ($language->alias == $ret->params["lang"]) {
							$page = $pageEntity;
							$ok = true;
							break;
						}
					}
				}
				if (!$ok) {
					foreach ($pageEntity->translations as $translation) {
						foreach ($translation->languages as $language) {
							if ($language->alias == $ret->params["lang"]) {
								$page = $translation;
								$ok = true;
								break;
							}
						}
						if ($ok) {
							break;
						}
					}
				}
				if (!$ok) {
					return NULL;
				}
			}
		} else {
			$page = $this->pageRepository->findOneBy(array("url" => $url));
		}



		$params = (array) $page->params;
		$params["pageId"] = $page->id;
		if (isset($params[self::MODULE_KEY])) {
			$presenter = $params[self::MODULE_KEY] . ":" . $params[self::PRESENTER_KEY];
			unset($params[self::MODULE_KEY], $params[self::PRESENTER_KEY]);
		} else {
			$presenter = $params[self::PRESENTER_KEY];
			unset($params[self::PRESENTER_KEY]);
		}

		if (isset($ret->params["lang"])) {
			$params["lang"] = $ret->params["lang"];
		}

		$params = $params + $ret->getParams();

		$this->cache->save(self::CACHE_MATCH_PREFIX.$key, array(
			"params" => $params,
			"presenter" => $presenter
		));


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

		$presenter = $appRequest->getPresenterName();
		$params[self::PRESENTER_KEY] = $presenter;

		$a = strrpos($presenter, ':');

		if ($a !== false) {
			$params[self::MODULE_KEY] = substr($presenter, 0, $a);
			$params[self::PRESENTER_KEY] = substr($presenter, $a + 1);
		} else {
			$params[self::MODULE_KEY] = '';
		}

		foreach ($params as $key => $param) {
			if ($param === NULL) {
				unset($params[$key]);
			}
		}

		ksort($params);
		
		$lang = $params["lang"];
		unset($params["lang"]);
		
		
		$key = json_encode($params);
		$data = $this->cache->load(self::CACHE_CONSTRUCT_PREFIX.$key);
		if ($data) {
			$appRequest->setPresenterName($data["presenter"]);
			$appRequest->setParams($data["params"]);
			return parent::constructUrl($appRequest, $refUrl);
		}
		
		$pages = $this->pageRepository->findBy(array(), array("paramCounter" => "DESC"));
		$pageEntity = NULL;
		foreach ($pages as $page) {
			$entityParams = (array) $page->params;
			if (count(array_intersect_assoc($entityParams, $params)) == count($entityParams)) {
				$pageEntity = $page;
				$entityParams = $entityParams;
				break;
			}
		}
		if (!$pageEntity) {
			return NULL;
		}

		$ok = false;
		foreach ($pageEntity->languages as $language) {
			if ($language->alias == $lang) {
				$ok = true;
			}
		}
		if (!$ok) {

			if ($pageEntity->translationFor instanceof \App\CoreModule\PageEntity) {
				$pageEntity = $pageEntity->translationFor;
				foreach ($pageEntity->languages as $language) {
					if ($language->alias == $lang) {
						$ok = true;
						break;
					}
				}
			}
			if (!$ok) {
				foreach ($pageEntity->translations as $translation) {
					foreach ($translation->languages as $language) {
						if ($language->alias == $lang) {
							$pageEntity = $translation;
							$ok = true;
							break;
						}
					}
					if ($ok) {
						break;
					}
				}
			}
			if (!$ok) {
				return NULL;
			}
		}

		$entityParams = (array) $pageEntity->params;


		$entityParams["lang"] = $lang;
		$params = $entityParams + $params;
		unset($params["pageId"]);
		$appRequest->setPresenterName("Default:Default");
		$appRequest->setParams($params);


		$this->cache->save(self::CACHE_CONSTRUCT_PREFIX.$key, array(
			"params"=>$params,
			"presenter"=>"Default:Default"
		));

		return parent::constructUrl($appRequest, $refUrl);
	}

}
