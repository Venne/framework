<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Application\UI;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @deprecated
 */
class Presenter extends \Nette\Application\UI\Presenter
{

	use PresenterTrait;


	protected function startup()
	{
		trigger_error(__CLASS__ . ' is deprecated, use Venne\Application\UI\\' . __CLASS__ . 'Trait', E_USER_DEPRECATED);

		parent::startup();
	}
}
