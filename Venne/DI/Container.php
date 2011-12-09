<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\DI;

use Venne;

/**
 * @author Josef Kříž
 * 
 * @property-read \Nette\Loaders\RobotLoader $robotLoader
 * @property-read \Nette\Http\Session $session
 * 
 * @property-read Venne\DI\Container $services
 * @property-read Venne\DI\Container $modules
 * 
 * @property-read \App\CoreModule\NavigationService $navigationService
 * @property-read Venne\Doctrine\ORM\BaseRepository $navigationRepository
 * 
 * @property-read \App\CoreModule\UserService $userService
 * @property-read Venne\Doctrine\ORM\BaseRepository $userRepository
 * 
 * @property-write \Venne\Doctrine\Container $doctrineContainer
 * 
 * @property-read \App\CoreModule\ModuleManager $moduleManager
 * 
 * @property-read Venne\Doctrine\ORM\BaseRepository $roleRepository
 * @property-read Venne\Doctrine\ORM\BaseRepository $permissionRepository
 */
class Container extends \Nette\DI\Container {


	const INJECT_ANNOTATION_CONSTRUCTOR = 0;
	const INJECT_ANNOTATION_PARAMETERS = 1;

	/** @var array */
	protected $serviceInterfaces = array();



	public function addServiceAutoWire($name, $className)
	{
		$container = $this;
		$this->addService($name, function() use ($className, $container) {
					return $container->getAutoWireClassInstance($className);
				});
	}



	public function getAutoWireClassInstance($className, array $args = array())
	{
		if(substr($className, 0, 1) != "\\"){
			$className = "\\" . $className;
		}
		
		$ann = $this->getClassInjectAnnotations($className);
		$ref = new \Nette\Reflection\ClassType($className);

		$ann += $args;
		
		$class = $ref->newInstanceArgs($ann[self::INJECT_ANNOTATION_CONSTRUCTOR]);
		foreach ($ann[self::INJECT_ANNOTATION_PARAMETERS] as $key => $arg) {
			$class->{$key} = $arg;
		}
		return $class;
	}



	/**
	 * Return two dimensional array
	 * @param string $className
	 * @return array
	 */
	public function getClassInjectAnnotations($className)
	{
		$data = array();
		$ref = new \Nette\Reflection\ClassType($className);
		if ($ref->getConstructor()->hasAnnotation("inject")) {
			$data = $ref->getConstructor()->getAnnotation("inject");
		}

		$args = array();
		foreach ($data as $item) {
			$args[] = $item == "context" ? $this : $this->getService($item);
		}

		$propArgs = array();
		foreach ($ref->getProperties() as $property) {
			if ($property->hasAnnotation("inject")) {
				$an = $property->getAnnotation("inject");
				$propArgs[$property->getName()] = $an == "context" ? $this : $an == "params" ? $this->params : $this->getService($an);
			}
		}

		return array(
			self::INJECT_ANNOTATION_CONSTRUCTOR => $args,
			self::INJECT_ANNOTATION_PARAMETERS => $propArgs
		);
	}



	public function getService($name)
	{
		if (substr($name, -9) == "Presenter") {
			$this->addServiceAutoWire($name, "\\" . $name);
		}

		return parent::getService($name);
	}

}