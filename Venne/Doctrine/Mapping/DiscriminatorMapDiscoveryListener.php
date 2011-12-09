<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine\Mapping;

use Doctrine;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Driver\Driver;
use Kdyby;
use Nette;
use Nette\Reflection\ClassType;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DiscriminatorMapDiscoveryListener extends Nette\Object implements Doctrine\Common\EventSubscriber
{

	/** @var \Doctrine\Common\Annotations\Reader */
	private $reader;

	/** @var \Doctrine\ORM\Mapping\Driver\Driver */
	private $driver;



	/**
	 * @param \Doctrine\Common\Annotations\Reader $reader
	 */
	public function __construct(Reader $reader)
	{
		$this->reader = $reader;
	}



	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			Events::loadClassMetadata,
		);
	}



	/**
	 * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $args
	 */
	public function loadClassMetadata(LoadClassMetadataEventArgs $args)
	{
		$meta = $args->getClassMetadata();
		$this->driver = $args->getEntityManager()->getConfiguration()->getMetadataDriverImpl();

		if ($meta->isInheritanceTypeNone()) {
			return;
		}

		$map = $meta->discriminatorMap;
		foreach ($this->getChildClasses($meta->name) as $className) {
			if (!in_array($className, $meta->discriminatorMap) && $entry = $this->getEntryName($className)) {
				$map[$entry->name] = $className;
			}
		}

		$meta->setDiscriminatorMap($map);
		$meta->subClasses = array_unique($meta->subClasses);
	}



	/**
	 * @param string $currentClass
	 *
	 * @return array
	 */
	private function getChildClasses($currentClass)
	{
		$classes = array();
		foreach ($this->driver->getAllClassNames() as $className) {
			if (!ClassType::from($className)->isSubclassOf($currentClass)) {
				continue;
			}

			$classes[] = $className;
		}
		$classes = array("App\PagesModule\BaseEntity");
		return $classes;
	}



	/**
	 * @param string $className
	 *
	 * @return string|NULL
	 */
	private function getEntryName($className)
	{
		return $this->reader->getClassAnnotation(
			ClassType::from($className),
			'Doctrine\ORM\Mapping\DiscriminatorEntry'
		) ? : NULL;
	}

}