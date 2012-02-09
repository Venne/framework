<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\AdminModule\SystemModule;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class LogPresenter extends BasePresenter {


	public function startup()
	{
		parent::startup();
		$this->addPath("Logs", $this->link(":Core:Admin:System:Log:"));
	}



	public function handleDelete()
	{
		unlink($this->context->parameters["logDir"] . "/" . $this->getParam("name"));
		$this->flashMessage("Log has been removed", "success");
		$this->redirect("this");
	}



	public function handleDeleteAll()
	{
		foreach ($this->getFiles() as $item) {
			unlink($this->context->parameters["logDir"] . "/" . $item["link"]);
		}

		$this->flashMessage("Logs were removed", "success");
		$this->redirect("this");
	}



	public function renderShow()
	{
		$this->sendResponse(new \Nette\Application\Responses\TextResponse(file_get_contents($this->context->parameters["logDir"] . "/" . $this->getParam("name"))));
	}



	public function renderDefault()
	{
		$this->template->files = $this->getFiles();
	}



	protected function getFiles()
	{
		$ret = array();

		foreach (\Nette\Utils\Finder::findFiles("exception*")->in($this->context->parameters["logDir"]) as $file) {
			$data = explode("-", $file->getFileName());

			$date = "{$data[1]}-{$data[2]}-{$data[3]} {$data[4]}:{$data[5]}:{$data[6]}";
			$info = array("date" => \Nette\DateTime::from($date), "hash" => $data[7], "link" => $file->getFileName());

			$ret[$date] = $info;
		}
		ksort($ret);
		return array_reverse($ret);
	}

}
