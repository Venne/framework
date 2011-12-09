<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Forms\Builders;

use Nette\Object;
use Nette\Reflection\ClassType;

/**
 * @author     Josef Kříž
 */
class EntityBuilder extends Object {



	public function build(\Venne\Forms\Form $form, $entityName)
	{
		$ref = new ClassType($entityName);
		foreach($ref->getProperties() as $property){
			if($property->hasAnnotation("Form")){
				$anot = $property->getAnnotation("Form");
				
				$type = isset($anot["type"]) ? $anot["type"] : $property->getAnnotation("Column")->type;
				$name = isset($anot["name"]) ? $anot["name"] : $property->getName();
				$label = isset($anot["label"]) ? $anot["label"] : ucfirst($property->getName());
				
				if(isset($anot["group"])){
					$form->addGroup($anot["group"]);
				}
				
				if($type == "string"){
					$form->addText($name, $label);
					continue;
				}
				
				if($type == "datetime"){
					$form->addDateTime($name, $label);
					continue;
				}
				
				if($type == "text"){
					$form->addTextArea($name, $label);
					continue;
				}
				
				if($type == "editor"){
					$form->addEditor($name, $label);
					continue;
				}
				
				if($type == "selectbox"){
					$form->addSelect($name, $label);
					continue;
				}
				
				if($type == "boolean"){
					$form->addCheckbox($name, $label);
					continue;
				}
			}
		}
	}

}
