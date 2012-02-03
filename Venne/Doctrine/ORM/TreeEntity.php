<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine\ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */ class TreeEntity extends BaseEntity {


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

