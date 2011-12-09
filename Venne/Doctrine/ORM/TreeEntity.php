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

/**
 * @author Josef Kříž
 */
class TreeEntity extends BaseEntity {


	/**
	 * @OneToMany(targetEntity="navigationEntity", mappedBy="parent")
	 * @OrderBy({"order" = "ASC"})
	 */
	protected $childrens;

	/**
	 * @ManyToOne(targetEntity="navigationEntity", inversedBy="id")
	 * @JoinColumn(name="navigation_id", referencedColumnName="id", onDelete="CASCADE")
	 * @OrderBy({"order" = "ASC"})
	 */
	protected $parent;

}

