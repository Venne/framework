<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Panels;

use Nette\Object;
use Nette\Diagnostics\IBarPanel;
use Nette\Diagnostics\Debugger;
use Nette\Diagnostics\Helpers;
use Nette\Utils\Html;
use Nette\Application\Responses\TextResponse;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @author jasir
 */
class RequestsPanel extends Object implements IBarPanel {

	const SESSION_SECTION = 'debug/RequestsPanel';

	protected $response;

	protected $presenter;

	protected $application;

	protected $session;

	protected $dumps = array();

	protected $httpRequest;



	public function __construct(\Nette\Application\Application $application, \Nette\Http\Session $session, \Nette\Http\Request $httpRequest, \Nette\Http\Response $httpResponse)
	{
		$this->application = $application;
		$this->presenter = $application->getPresenter();
		$this->session = $session;
		$this->httpRequest = $httpRequest;
		$this->response = $httpResponse;

		$application->onShutdown[] = callback($this, 'onShutdown');
	}



	public static function dump($var, $label = NULL, $depth = NULL)
	{
		if ($depth !== NULL) {
			$saveDepth = Debugger::$maxDepth;
			Debugger::$maxDepth = $depth;
		}
		$s = Helpers::clickableDump($var);
		if ($label === NULL) {
			self::$dumps[] = $s;
		} else {
			self::$dumps[$label] = $s;
		}
		if ($depth !== NULL) {
			Debugger::$maxDepth = $saveDepth;
		}
	}



	/**
	 * Renders HTML code for custom tab.
	 *
	 * @return string
	 * @see IDebugPanel::getTab()
	 */
	public function getTab()
	{
		$logs = $this->session->getSection(self::SESSION_SECTION)->logs;
		$s = '<span title="Requests">';
		$s .= '<img src="data:image/gif;base64,R0lGODlhEAAQAKUkAAAAAIUlEqEtFqkvFrMxGEJdc0VheME1GklngE1shk9vit09HlR2k1d6mOZjSehvV+yKd+2SgJuyxqK3yam9zqu+zrHD0vOzpvO4rPXEusnV4MzX4c/a5PfOxtLc5dXe5vjUzfjWz9ri6dvj6v///////////////////////////////////////////////////////////////////////////////////////////////////////////////yH5BAEKAD8ALAAAAAAQABAAAAZiwJ/wBxgaj0IAqIg8AkIRArNJ7EQih8HU2Vk8IIJAYDsEmC8RgZlsBGDSzLW5nYEnPRXGFhBxqJMcEwV7ckkbgmxlZhqIc0gAHxQWEgkNCYlEHxMTCgaYSSMTCJ9lIqRtRkEAOw%3D%3D">';
		$s .= ($cnt = count($logs)) > 1 ? Html::el('span')->class('nette-warning')->add("[$cnt]") : "[1]";
		$s .= '</span>';
		return $s;
	}



	/**
	 * Renders HTML code for custom panel.
	 *
	 * @return string
	 * @see IDebugPanel::getPanel()
	 */
	public function getPanel()
	{
		$session = $this->session->getSection(self::SESSION_SECTION);
		$logs = $session->logs;
		echo "fsdfsdgdf gsdfgsdf";
		if ($this->response instanceOf TextResponse) {
			echo "ahoj";
			dump($logs);
			unset($session->logs);
			ob_start();
			require __DIR__ . '/bar.requests.panel.phtml';
			return ob_get_clean();
		}
	}



	/**
	 * Returns panel ID.
	 *
	 * @return string
	 * @see IDebugPanel::getId()
	 */
	public function getId()
	{
		return __CLASS__;
	}



	/**
	 * @param $presenter Presenter
	 * @param $response PresenterResponse
	 * @internal
	 */
	public function onShutdown($presenter, $response)
	{
		$this->response = $response;

		$application = $this->application;
		$presenter = $this->presenter;
		$request = $presenter->getRequest();
		$httpRequest = $this->httpRequest;

		$entry = array();

		if ($signal = $presenter->getSignal()) {
			$receiver = empty($signal[0]) ? $presenter->name : $signal[0];
			$signal = $receiver . " :: " . $signal[1];
		}

		if ($response !== NULL) {
			$rInfo = get_class($response);
			if ($response->getReflection()->hasMethod('getCode')) {
				$rInfo .= ' (' . $response->code . ')';
			}
		}

		$entry['info']['presenter'] = $presenter->backlink();
		$entry['info']['response'] = $response === NULL ? 'NO RESPONSE' : $rInfo;
		$entry['info']['uri'] = $httpRequest->getUrl();
		$entry['info']['uriPath'] = $httpRequest->getUrl()->path;
		$entry['info']['request'] = $request->getMethod();
		$entry['info']['signal'] = $signal;
		$entry['info']['time'] = number_format((microtime(TRUE) - Debugger::$time) * 1000, 1, '.', ' ');

		$entry['dumps']['HttpRequest'] = Helpers::clickableDump($httpRequest);
		$entry['dumps']['PresenterRequest'] = Helpers::clickableDump($request);
		$entry['dumps']['Presenter'] = Helpers::clickableDump($presenter);
		$entry['dumps']['PresenterResponse'] = Helpers::clickableDump($response);


		foreach ($this->dumps as $key => $dump) {
			if (is_numeric($key)) {
				$entry['dumps'][] = $dump;
			} else {
				$entry['dumps'][$key] = $dump;
			}
		}

		$session = $this->session->getSection(self::SESSION_SECTION);

		if (!isset($session->logs)) {
			$session->logs = array();
		}
		$session->logs[] = $entry;
	}

}