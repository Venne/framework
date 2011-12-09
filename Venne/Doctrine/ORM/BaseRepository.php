<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine\ORM;

use Doctrine;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Venne;
use Venne\Doctrine\IQueryObject;
use Venne\Doctrine\Mapping\EntityValuesMapper;
use Nette;
use Nette\ObjectMixin;

/**
 * @author Filip Procházka
 * @author Josef Kříž
 * @author Patrik Votoček
 *
 * @method Mapping\ClassMetadata getClassMetadata() getClassMetadata()
 */
class BaseRepository extends Doctrine\ORM\EntityRepository implements Venne\Doctrine\IDao, Venne\Doctrine\IQueryable, Venne\Doctrine\IObjectFactory {


	/** @var EntityValuesMapper */
	private $entityMapper;



	public function __construct($em, \Doctrine\ORM\Mapping\ClassMetadata $class)
	{
		parent::__construct($em, $class);
		$this->entityMapper = new EntityValuesMapper($em, new \Venne\Doctrine\Mapping\TypeMapper());
	}



	/**
	 * @param array $values
	 */
	public function createNew($arguments = array(), $values = array())
	{
		$class = $this->getEntityName();
		if (!$arguments) {
			$entity = new $class;
		} else {
			$reflection = new Nette\Reflection\ClassType($class);
			$entity = $reflection->newInstanceArgs($arguments);
		}

		if ($values) {
			if (!$this->entityMapper) {
				throw new Nette\InvalidArgumentException("EntityMapper service was not injected, therefore DAO cannot set values.");
			} else {
				$this->entityMapper->load($entity, $values);
			}
		}
		return $entity;
	}



	/**
	 * @param EntityValuesMapper $mapper
	 */
	public function setEntityMapper(EntityValuesMapper $mapper)
	{
		$this->entityMapper = $mapper;
	}



	public function update($entity, $values = array(), $withoutFlush = self::FLUSH)
	{
		if ($entity instanceof Collection) {
			return $this->update($entity->toArray(), $values, $withoutFlush);
		}

		if (is_array($entity)) {
			$repository = $this;
			$result = array_map(function ($entity) use ($repository) {
						return $repository->update($entity, $values, BaseRepository::NO_FLUSH);
					}, $entity);

			return $result;
		}

		if ($values) {
			if (!$this->entityMapper) {
				throw new Nette\InvalidArgumentException("EntityMapper service was not injected, therefore DAO cannot set values.");
			} else {
				$this->entityMapper->load($entity, $values);
			}
		}

		$this->flush($withoutFlush);

		return $entity;
	}



	/**
	 * @param object|array|Collection $entity
	 * @param boolean $withoutFlush
	 * @return object|array
	 */
	public function save($entity, $withoutFlush = self::FLUSH)
	{
		if ($entity instanceof Collection) {
			return $this->save($entity->toArray(), $withoutFlush);
		}

		if (is_array($entity)) {
			$repository = $this;
			$result = array_map(function ($entity) use ($repository) {
						return $repository->save($entity, BaseRepository::NO_FLUSH);
					}, $entity);

			$this->flush($withoutFlush);

			return $result;
		}

		if (!$entity instanceof $this->_entityName) {
			throw new Nette\InvalidArgumentException("Entity is not instanceof " . $this->_entityName . ', ' . get_class($entity) . ' given.');
		}

		$this->getEntityManager()->persist($entity);
		$this->flush($withoutFlush);

		return $entity;
	}



	/**
	 * @param object|array|Collection $entity
	 * @param boolean $withoutFlush
	 */
	public function delete($entity, $withoutFlush = self::FLUSH)
	{
		if ($entity instanceof Collection) {
			return $this->delete($entity->toArray(), $withoutFlush);
		}

		if (is_array($entity)) {
			$repository = $this;
			array_map(function ($entity) use ($repository) {
						return $repository->delete($entity, BaseRepository::NO_FLUSH);
					}, $entity);

			$this->flush($withoutFlush);
			return;
		}

		if (!$entity instanceof $this->_entityName) {
			throw new Nette\InvalidArgumentException("Entity is not instanceof " . $this->_entityName . ', ' . get_class($entity) . ' given.');
		}

		$this->getEntityManager()->remove($entity);
		$this->flush($withoutFlush);
	}



	/**
	 * @param boolean $withoutFlush
	 */
	protected function flush($withoutFlush)
	{
		if ($withoutFlush === BaseRepository::FLUSH) {
			try {
				$this->getEntityManager()->flush();
			} catch (\PDOException $e) {
				throw new SqlException($e);
			}
		}
	}



	/**
	 * @param string $alias
	 * @return Doctrine\ORM\QueryBuilder|Doctrine\CouchDB\View\AbstractQuery $qb
	 */
	public function createQueryBuilder($alias = NULL)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();

		if ($alias !== NULL) {
			$qb->select($alias)->from($this->getEntityName(), $alias);
		}

		return $qb;
	}



	/**
	 * @param string $alias
	 * @return Doctrine\ORM\Query
	 */
	public function createQuery($dql = NULL)
	{
		return $this->getEntityManager()->createQuery($dql);
	}



	/**
	 * @param callabke $callback
	 * @return type
	 */
	public function transactional($callback)
	{
		$connection = $this->getEntityManager()->getConnection();
		$connection->beginTransaction();

		try {
			$return = callback($callback)->invoke($this);
			$this->flush();
			$connection->commit();
			return $return ? : TRUE;
		} catch (\Exception $e) {
			$connection->rollback();
			throw $e;
		}
	}



	/**
	 * @param IQueryObject $queryObject
	 * @return integer
	 */
	public function count(IQueryObject $queryObject)
	{
		try {
			return $queryObject->count($this->getEntityManager()->createQueryBuilder());
		} catch (\Exception $e) {
			return $this->handleQueryExceptions($e, $queryObject);
		}
	}



	/**
	 * @param IQueryObject $queryObject
	 * @return array
	 */
	public function fetch(IQueryObject $queryObject)
	{
		try {
			return $queryObject->fetch($this);
		} catch (\Exception $e) {
			return $this->handleQueryExceptions($e, $queryObject);
		}
	}



	/**
	 * @param IQueryObject $queryObject
	 * @return object
	 */
	public function fetchOne(IQueryObject $queryObject)
	{
		try {
			return $queryObject->fetchOne($this);
		} catch (NoResultException $e) {
			return NULL;
		} catch (NonUniqueResultException $e) { // this should never happen!
			throw new Nette\InvalidStateException("You have to setup your query using ->setMaxResult(1).", NULL, $e);
		} catch (\Exception $e) {
			return $this->handleQueryExceptions($e, $queryObject);
		}
	}



	/**
	 * Fetches all records like $key => $value pairs
	 *
	 * @param string
	 * @param string
	 * @return array
	 */
	public function fetchPairs($key = NULL, $value = NULL, $where = array())
	{
		$res = $this->createQueryBuilder('uni')->select("uni.$key, uni.$value");
		foreach ($where as $key2 => $item) {
			$res->where("uni.$key2 = :$key2")->setParameter($key2, $item);
		}
		$res = $res->getQuery()->getResult();

		$arr = array();
		foreach ($res as $row) {
			$arr[$row[$key]] = $row[$value];
		}

		return $arr;
	}



	/**
	 * Fetches all records and returns an associative array indexed by key
	 *
	 * @param string
	 * @return array
	 */
	public function fetchAssoc($key, $where = array())
	{
		$res = $this->createQueryBuilder('uni')->select("uni");
		foreach ($where as $key2 => $item) {
			$res->where("uni.$key2 = :$key2")->setParameter($key2, $item);
		}
		$res = $res->getQuery()->getResult();

		$arr = array();
		foreach ($res as $doc) {
			if (array_key_exists($doc->$key, $arr)) {
				throw new \Nette\InvalidStateException("Key value {$doc->{"get" . ucfirst($key)}} is duplicit in fetched associative array. Try to use different associative key");
			}
			$arr[$doc->$key] = $doc;
		}

		return $arr;
	}



	/**
	 * @param \Exception $e
	 * @throws \Exception
	 * @return mixed
	 */
	private function handleQueryExceptions(\Exception $e, IQueryObject $queryObject)
	{
		if ($e instanceof Doctrine\ORM\Query\QueryException) {
			throw new QueryException('(' . get_class($queryObject) . ') ' . $e->getMessage(), $queryObject->getLastQuery(), $e);
		} else {
			throw $e;
		}
	}

	/*	 * ******************* Nette\Object behaviour ****************d*g* */



	/**
	 * @return Nette\Reflection\ClassType
	 */
	public /**/static/**/ function getReflection()
	{
		return new Nette\Reflection\ClassType(/* 5.2*$this *//**/get_called_class()/**/);
	}



	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}



	public function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}



	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}



	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}

}