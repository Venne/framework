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

use Nette\InvalidStateException;
use Nette\Templating\FileTemplate;
use Venne\Security\IComponentVerifier;
use Venne\Templating\ITemplateConfigurator;
use Venne\Widget\WidgetManager;

/**
 * Description of Control
 *
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @deprecated
 *
 * @property-read Presenter $presenter
 * @method Presenter getPresenter()
 */
class Control extends \Nette\Application\UI\Control
{

	use ControlTrait;

	protected function startup()
	{
		trigger_error(__CLASS__ . ' is deprecated, use Venne\Application\UI\\' . __CLASS__ . 'Trait', E_USER_DEPRECATED);
	}

}

