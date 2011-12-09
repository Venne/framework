<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Application\UI;

use Venne;

/**
 * Description of Control
 *
 * @author Josef Kříž
 */
class Control extends \Nette\Application\UI\Control {

	/**
	 * @return Kdyby\Templating\FileTemplate
	 */
	protected function createTemplate($class = NULL)
	{
		return $this->context->templateContainer->createTemplate($this, $class);
	}

	/**
	 * Descendant can override this method to customize template compile-time filters.
	 * @param  Nette\Templating\Template
	 * @return void
	 */
	public function templatePrepareFilters($template)
	{
		// default filters
		$template->registerHelper("thumb", '\Venne\Templating\ThumbHelper::thumb');
		$template->registerFilter(new Venne\Latte\Engine($this->getPresenter()->getContext()));
	}

}

