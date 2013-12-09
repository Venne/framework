<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Config\Extensions;

use Nette\Caching\Cache;
use Nette\Caching\Storages\PhpFileStorage;
use Nette\Utils\PhpGenerator\ClassType;
use Venne\Config\CompilerExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ProxyExtension extends CompilerExtension
{

	/** @var string */
	private $classesFile;


	public function beforeCompile()
	{
		$container = $this->getContainerBuilder();

		$code = '';
		foreach ($container->findByTag('proxy') as $factory => $meta) {
			$definition = $container->getDefinition($factory);

			$class = substr($definition->class, strrpos($definition->class, '\\') + 1);
			$namespace = substr($definition->class, 0, strrpos($definition->class, '\\'));
			$extend = ltrim($meta, '\\');

			$code .= $this->generateCode($class, $extend, $namespace);
		}

		if ($code) {
			$cache = $this->getCache();
			$cache->save('file', $code);

			// find the file
			$cached = $cache->load('file');
			$this->classesFile = $cached['file'];
			\Nette\Utils\LimitedScope::evaluate($code);
		}
	}


	/**
	 * @param ClassType $class
	 */
	public function afterCompile(ClassType $class)
	{
		if ($this->classesFile) {
			$init = $class->methods['initialize'];
			$init->addBody('include_once ?;', array($this->classesFile));
		}
	}


	private function generateCode($class, $extend, $namespace = NULL)
	{
		$ret = "<?php\n\n";

		if ($namespace) {
			$ret .= "namespace $namespace;\n\n";
		}

		$ret .= "if (!class_exists('{$namespace}\\{$class}')) {\n\n";

		$php = new ClassType($class);
		$php->addExtend('\\' . $extend);

		$ret .= $php->__toString() . "\n\n}\n?>";
		return $ret;
	}


	/**
	 * @return Cache
	 */
	private function getCache()
	{
		$cacheDir = $this->getContainerBuilder()->expand('%tempDir%/cache');
		return new Cache(new PhpFileStorage($cacheDir), 'Venne.DicProxies');
	}
}

