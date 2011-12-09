<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\SecurityModule;

use Venne;

/**
 * @author Josef Kříž
 */
class AuthorizatorFactory extends \Nette\Security\Permission {


	/** @var \Nette\DI\Container */
	protected $context;

	/** @var array */
	protected $defaultRoles = array("admin");



	/**
	 * @param \Nette\DI\Container
	 */
	public function __construct(\Nette\DI\Container $context)
	{
		$this->context = $context;
	}



	public function create()
	{
		$session = $this->context->session->getSection("Venne.Security.Authorizator");
		if ($session["permission"]) {
			return $session["permission"];
		}

		$permission = new \Nette\Security\Permission;
		foreach($this->defaultRoles as $role){
			$permission->addRole($role);
		}

		/*
		 * Add resources
		 */
		foreach ($this->context->modules->getModules() as $key => $module) {
			$this->context->modules->$key->setPermissions($this->context, $permission);
		}

		$roles = array();
		if ($this->context->doctrineContainer->checkConnection()) {

			/*
			 * Add roles
			 */
			$res = $this->context->roleRepository->findAll();
			foreach ($res as $item) {
				if (!$permission->hasRole($item->name)) {
					$permission->addRole($item->name, $item->parent ? $item->parent->name : NULL);
					if (in_array($item->name, $this->context->user->roles)) {
						$roles[] = $item->id;
					}
				}
			}

			$rules = $this->context->permissionRepository->findAll();
			foreach ($rules as $perm) {
				if ($permission->hasResource($perm->resource)) {
					if ($perm->allow) {
						$permission->allow($perm->role->name, $perm->resource, $perm->privilege ? $perm->privilege : NULL);
					} else {
						$permission->deny($perm->role->name, $perm->resource, $perm->privilege ? $perm->privilege : NULL);
					}
				}
			}
		}

		$permission->allow("admin", \Nette\Security\Permission::ALL);
		return $session["permission"] = $permission;
	}

}
