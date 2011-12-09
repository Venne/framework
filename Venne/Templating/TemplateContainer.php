<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Templating;

use Venne;
use Nette;
use Nette\Application\UI\Presenter;
use Nette\Templating\ITemplate;

/**
 * @author Josef Kříž
 * @author Filip Procházka
 */
class TemplateContainer extends \Nette\Object {


	/** @var \Nette\Latte\Engine */
	private $latteEngine;

	/** @var \Venne\Localization\ITranslator */
	private $translator;


	/**
	 * @param \Nette\Latte\Engine $latteEngine
	 */
	public function __construct(\Nette\Latte\Engine $latteEngine, \Venne\Localization\ITranslator $translator = NULL)
	{
		$this->latteEngine = $latteEngine;
		$this->translator = $translator;
	}


	public function createTemplate(\Nette\ComponentModel\Component $component, $class = NULL)
	{
		$template = $class ? new $class : new \Nette\Templating\FileTemplate;

		// find presenter
		$presenter = $component instanceof Presenter ? $component : $component->getPresenter(FALSE);

		// latte
		$template->onPrepareFilters[] = callback($this, 'templatePrepareFilters');

		// helpers
		$template->registerHelperLoader('\Nette\Templating\DefaultHelpers::loader');

		// translator
		if ($this->translator) {
			$template->setTranslator($this->translator);
		}

		// default parameters
		$template->control = $component;
		$template->presenter = $presenter;

		// stuff from presenter
		if ($presenter instanceof Presenter) {
			$template->setCacheStorage($presenter->context->templateCacheStorage);
			$template->user = $presenter->getUser();
			$template->netteHttpResponse = $presenter->context->httpResponse;
			$template->netteCacheStorage = $presenter->context->cacheStorage;
			$template->baseUri = $template->baseUrl = rtrim($presenter->context->httpRequest->getUrl()->getBaseUrl(), '/');
			$template->basePath = preg_replace('#https?://[^/]+#A', '', $template->baseUrl);

			// flash message
			if ($presenter->hasFlashSession()) {
				$id = $component->getParamId('flash');
				$template->flashes = $presenter->getFlashSession()->$id;
			}
		}
		if (!isset($template->flashes) || !is_array($template->flashes)) {
			$template->flashes = array();
		}

		return $template;
	}


	/**
	 * @param ITemplate
	 * @return void
	 */
	public function templatePrepareFilters(\Nette\Templating\ITemplate $template)
	{
		// default filters
		$template->registerFilter($this->latteEngine);
	}

}

