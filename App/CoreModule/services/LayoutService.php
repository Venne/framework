<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule;

use Venne;
use Nette\Object;

/**
 * @author Josef Kříž
 */
class LayoutService extends Object {


	/** @var \Venne\DI\Container */
	protected $context;

	/** @var \Doctrine\ORM\EntityManager */
	public $entityManager;



	public function __construct($context, $moduleName, \Doctrine\ORM\EntityManager $entityManager)
	{
		$this->context = $context;
		$this->entityManager = $entityManager;
	}



	public function getEntityManager()
	{
		return $this->entityManager;
	}



	/**
	 * @return \Venne\Doctrine\ORM\BaseRepository 
	 */
	protected function getRepository()
	{
		return $this->entityManager->getRepository("\\App\\CoreModule\\layoutEntity");
	}



	public function detectLayout()
	{
		$presenter = $this->context->application->presenter;
		$name = $presenter->getName() . ":" . $presenter->getAction();
		$data = array();


		$query = $this->getEntityManager()->createQuery('
					SELECT u FROM \App\CoreModule\LayoutEntity u ORDER BY u.regex DESC
				');

		foreach ($query->getResult() as $item) {
			$data[$item->regex][] = $item;
		}

		ksort($data);
		$data = array_reverse($data);

		foreach ($data as $items) {
			$layout = false;
			$params = -1;

			foreach ($items as $item) {
				if (strpos($name, $item->regex) !== false) {
					if (count($item->keys) > $params) {
						$ok = true;
						foreach ($item->keys as $key) {
							if ($key->val != $presenter->getParam($key->key)) {
								$ok = false;
								break;
							}
						}
						if ($ok) {
							$params = count($item->keys);
							$layout = $item->layout;
						}
					}
				}
			}

			if ($layout) {
				return $layout;
			}
		}

		return "layout";
	}



	public function removeLayout($id)
	{
		$this->getEntityManager()->remove($this->getRepository()->find($id));
		$this->getEntityManager()->flush();
	}



	public function saveLayout($moduleItemId, $moduleName, $use, $layout, $linkParams)
	{
		$entity = $this->getRepository()->findOneBy(array("moduleItemId" => $moduleItemId, "moduleName" => $moduleName));

		if (!$use) {
			if ($entity) {
				$this->getEntityManager()->remove($entity);
			}
		} else {
			if (!$entity) {
				$entity = new LayoutEntity();
				$this->getEntityManager()->persist($entity);
				$entity->moduleItemId = $moduleItemId;
				$entity->moduleName = $moduleName;
				$entity->regex = "";

				dump($linkParams);

				if (isset($linkParams["module"])) {
					$entity->regex .= $linkParams["module"];
					if (isset($linkParams["presenter"])) {
						$entity->regex .= ":" . $linkParams["presenter"];
						if (isset($linkParams["action"])) {
							$entity->regex .= ":" . $linkParams["action"];
						}
					}
				}

				foreach ($linkParams as $keyName => $param) {
					if ($keyName == "module" || $keyName == "presenter" || $keyName == "action") {
						continue;
					}

					$key = new LayoutKeyEntity();
					$key->key = $keyName;
					$key->val = $param;
					$key->layout = $entity;
					$this->getEntityManager()->persist($key);
				}
			}
			$entity->layout = $layout;
		}
		$this->getEntityManager()->flush();
	}



	public function loadLayout($moduleItemId, $moduleName)
	{
		return $this->getRepository()->findOneBy(array("moduleItemId" => $moduleItemId, "moduleName" => $moduleName));
	}



	public function createLayout($layoutName, $module = NULL, $presenter = NULL, $action = NULL, $params = NULL, $moduleName = NULL, $moduleItemId = NULL)
	{
		$params = (array) $params;

		$layout = new LayoutEntity();
		$layout->layout = $layoutName;
		$layout->regex = "";
		$layout->moduleName = $moduleName;
		$layout->moduleItemId = $moduleItemId;
		if ($module) {
			$layout->regex .= $module;
			if ($presenter) {
				$layout->regex .= ":" . $presenter;
				if ($action) {
					$layout->regex .= ":" . $action;
				}
			}
		}

		$this->getEntityManager()->persist($layout);

		foreach ($params as $keyName => $param) {
			$key = new LayoutKeyEntity;
			$key->key = $keyName;
			$key->val = $param;
			$key->layout = $layout;
			$this->getEntityManager()->persist($key);
		}

		$this->getEntityManager()->flush();
	}



	public function updateLayout($id, $layoutName, $module = NULL, $presenter = NULL, $action = NULL, $params = NULL)
	{
		$params = (array) $params;

		$layout = $this->getRepository()->find($id);

		/* Delete keys */
		foreach ($layout->keys as $key) {
			$this->getEntityManager()->remove($key);
		}

		$layout->layout = $layoutName;
		$layout->regex = "";
		if ($module) {
			$layout->regex .= $module;
			if ($presenter) {
				$layout->regex .= ":" . $presenter;
				if ($action) {
					$layout->regex .= ":" . $action;
				}
			}
		}

		$this->getEntityManager()->persist($layout);

		foreach ($params as $keyName => $param) {
			$key = new LayoutKeyEntity();
			$key->key = $keyName;
			$key->val = $param;
			$key->layout = $layout;
			$this->getEntityManager()->persist($key);
		}

		$this->getEntityManager()->flush();
	}



	public function getLayout($id)
	{
		return $this->getRepository()->find($id);
	}



	/**
	 * @param string $moduleName
	 * @param string $moduleItemId 
	 */
	public function removeItemByModuleName($moduleName, $moduleItemId)
	{
		$item = $this->getRepository()->findOneBy(array("moduleName" => $moduleName, "moduleItemId" => $moduleItemId));
		if ($item) {
			$this->getEntityManager()->remove($item);
			$this->getEntityManager()->flush();
		}
	}

}

