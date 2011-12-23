<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Diagnostics;

/**
 * @author Josef Kříž
 */
class Logger extends \Nette\Diagnostics\Logger {

		/** @var string */
	public static $linkPrefix;


	/**
	 * Default mailer.
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public static function venneMailer($message, $email)
	{
		if (self::$linkPrefix) {
			$data = explode("@@", $message);
			if(isset($data[1])){
				$data = trim($data[1]);
				$message .= "\n\nLink: " . self::$linkPrefix . $data;
			}
		}

		$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] :
				(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '');

		$parts = str_replace(
				array("\r\n", "\n"), array("\n", PHP_EOL), array(
			'headers' => "From: noreply@$host\nX-Mailer: Nette Framework\n",
			'subject' => "PHP: An error occurred on the server $host",
			'body' => "[" . @date('Y-m-d H:i:s') . "] $message", // @ - timezone may not be set
				)
		);

		mail($email, $parts['subject'], $parts['body'], $parts['headers']);
	}

}

