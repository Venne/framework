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
class UserService extends Object {


	
	/** @var \Venne\DI\Container */
	protected $context;
	
	/** @var \Doctrine\ORM\EntityManager */
	public $entityManager;


	public function __construct($context, $moduleName, \Doctrine\ORM\EntityManager $entityManager)
	{
		$this->context = $context;
		$this->entityManager = $entityManager;
	}

	/**
	 * @return \Venne\Doctrine\ORM\BaseRepository 
	 */
	protected function getRepository()
	{
		return $this->entityManager->getRepository("\\App\\CoreModule\\NavigationEntity");
	}

	public function create($values = array(), $withoutFlush = false)
	{
		if (!array_key_exists("salt", $values)) {
			$values["salt"] = \Nette\Utils\Strings::random(8);
		}
		if (!array_key_exists("password", $values)) {
			$values["password"] = md5($values["salt"] . $values["password"]);
		}
		if (!array_key_exists("enable", $values)) {
			$values["enable"] = 1;
		}
		if (!$values["enable"]){
			$values["key"] = \Nette\Utils\Strings::random(30);
		}
		$entity = parent::create($values, true);
		
		try{
			$this->isUserUnique($entity);
		}catch(\Exception $ex){
			$this->entityManager->remove($entity);
			throw $ex;
		}
		
		if(!$withoutFlush){
			$this->entityManager->flush();
		}
		return $entity;
	}
	
	public function isUserUnique($entity)
	{
		$item = $this->repository->findOneByName($entity->name);
		if($item){
			throw new UserNameExistsException("Username ".$entity->name." already exists");
		}
		$item = $this->repository->findOneByEmail($entity->email);
		if($item){
			throw new UserEmailExistsException("E-mail ".$entity->email." already exists");
		}
		return true;
	}

}
