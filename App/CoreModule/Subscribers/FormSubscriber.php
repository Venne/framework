<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\Subscribers;

use Doctrine\Common\EventSubscriber;
use Venne\Forms\Events\Events;
use Venne\Forms\Events\EventArgs;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FormSubscriber implements EventSubscriber {


	/** @var \Venne\Assets\AssetManager */
	protected $assetManager;



	/**
	 * Constructor.
	 *
	 * @param \Venne\Assets\AssetManager $assetManager
	 */
	public function __construct(\Venne\Assets\AssetManager $assetManager)
	{
		$this->assetManager = $assetManager;
	}



	/**
	 * Array of events.
	 *
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(Events::onBeforeRender);
	}



	/**
	 * onBeforeRender event.
	 *
	 * @param EventArgs $args
	 */
	public function onBeforeRender(EventArgs $args)
	{
		$form = $args->getForm();

		foreach ($form->getComponents() as $component) {
			if ($component instanceof \Venne\Forms\Controls\TagInput) {
				$this->assetManager->addJavascript("@CoreModule/Forms/tag/tag.js");
				$this->assetManager->addStylesheet("@CoreModule/Forms/tag/tagInput.css");
			}

			if ($component instanceof \Venne\Forms\Controls\DateInput) {
				$this->assetManager->addStylesheet("@CoreModule/Forms/date/dateInput.css");
				$this->assetManager->addJavascript("@CoreModule/Forms/date/jquery-ui-timepicker-addon.js");
				$this->assetManager->addJavascript("@CoreModule/Forms/date/dateInput.js");
				$this->assetManager->addJavascript("@CoreModule/Forms/date/date.js");
			}

			if ($component instanceof \DependentSelectBox\DependentSelectBox) {
				$this->assetManager->addJavascript("@CoreModule/Forms/dependentSelectBox/dependentSelectBox.js");
			}

			if ($component instanceof \Venne\Forms\Controls\TextWithSelect) {
				$this->assetManager->addJavascript("@CoreModule/Forms/textWithSelect/textWithSelect.js");
			}
		}
	}

}
