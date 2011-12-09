<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\ContentExtension;

use Nette\Object;
use Venne\Doctrine\ORM\BaseEntity;
use App\CoreModule\PageEntity;

/**
 * @author Josef Kříž
 */
class ContentExtensionEntity extends BaseEntity {


	/**
	 * @var \App\CoreModule\PageEntity
	 * @OneToOne(targetEntity="\App\CoreModule\PageEntity", cascade={"persist"})
	 * @JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $page;



	public function __construct(PageEntity $page)
	{
		$this->page = $page;
	}

}

