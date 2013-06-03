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

use Nette;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @author Filip Procházka <filip@prochazka.su>
 */
final class Helpers
{

	/**
	 * @param Nette\Application\UI\PresenterComponent $component
	 * @return array
	 */
	public static function nullLinkParams(Nette\Application\UI\PresenterComponent $component)
	{
		$parent = $component;
		$presenter = $component instanceof Nette\Application\UI\Presenter ? NULL : $component->lookup('Nette\Application\UI\Presenter');
		$params = array();

		do {
			if ($parent && method_exists($parent, 'getPersistentParams')) {
				$name = $parent instanceof Nette\Application\UI\Presenter ? '' : $parent->lookupPath(get_class($presenter));

				foreach ($parent->reflection->getPersistentParams() as $param => $info) {
					$params[($name ? $name . $component::NAME_SEPARATOR : NULL) . $param] = $info['def'] ? : NULL;
				}
			}
		} while ($parent && $parent = $parent->getParent());

		return $params;
	}
}

