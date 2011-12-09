<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule;

use Venne;

/**
 * @author Josef Kříž
 */
class ThumbMacro extends \Nette\Latte\Macros\MacroSet {
	
	public static function filter(\Nette\Latte\MacroNode $node, $writer)
	{
		$param = $writer->formatArray();
		if (strpos($node->args, '=>') === FALSE) {
			$param = substr($param, 6, -1); // removes array()
		}
		$param = explode(" ", $param);
		$name = $param[0];
		unset($param[0]);
		if(!count($param)){
			return $writer->write('?>src="<?php echo $basePath;?>' . $name . '"<?php');
		}
		return $writer->write('?>src="<?php echo \App\CoreModule\ThumbMacro::thumb($presenter, "' . $name . '", ' . implode(", ", $param) . '); ?>"<?php');
	}
	
	public static function install(\Nette\Latte\Parser $parser)
	{
		$me = new static($parser);
		$me->addMacro('@src', array($me, "filter"));
	}
	
	/**
	 * Vytvoreni miniatury obrazku a vraceni jeho URI
	 *
	 * @param  string relativni URI originalu (zacina se v document_rootu)
	 * @param  NULL|int sirka miniatury
	 * @param  NULL|int vyska miniatury
	 * @return string absolutni URI miniatury
	 */
	public static function thumb($presenter, $origName, $width = NULL, $height = NULL, $flags = \Nette\Image::FIT, $crop = false, $tag = false)
	{
		$flags = $flags | \Nette\Image::ENLARGE;
	
		if(!$width){
			$width = NULL;
		}
		if(!$height){
			$height = NULL;
		}
		
		$basePath = $presenter->context->httpRequest->url->basePath;
		$path = $basePath . "cache/thumbs/";
		$wwwDir = $presenter->context->params["wwwDir"];
		$dir = $wwwDir . "/cache/thumbs";
		
		if(!$width && !$height){
			return $basePath . $origName;
		}
		
		if($tag){
			$tagWeb = \str_replace("-", "/", \Nette\Utils\Strings::webalize($tag));
			$thumbDirPath = $dir . '/' . $tagWeb;
		}else{
			$thumbDirPath = $dir . '/';
		}
		$origPath = $wwwDir . '/' . $origName;

		if (!\file_exists($thumbDirPath)){
			\mkdir($thumbDirPath, 0777, true);
		}
		
		if (($width === NULL && $height === NULL) || !is_file($origPath) || !is_dir($thumbDirPath) || !is_writable($thumbDirPath)){
			return $basePath . "/" . $origName;
		}

		$thumbName = self::getThumbName($origName, $width, $height, filemtime($origPath), $flags, $crop);

		if($tag){
			$thumbUri = $tagWeb . '/' .$thumbName;
		}else{
			$thumbUri = $thumbName;
		}

		$thumbPath = $thumbDirPath . '/' . $thumbName;

		// miniatura jiz existuje
		if (is_file($thumbPath)) {
			return $path . $thumbUri;
		}

		try {
			$image = \Nette\Image::fromFile($origPath);

			// zachovani pruhlednosti u PNG
			$image->alphaBlending(FALSE);
			$image->saveAlpha(TRUE);

			$origWidth = $image->getWidth();
			$origHeight = $image->getHeight();

			$image->resize($width, $height, $flags);
			$image->sharpen();

			$newWidth = $image->getWidth();
			$newHeight = $image->getHeight();

			if($crop){
				$image->crop('50%', '50%', $width, $height);
			}

			// doslo ke zmenseni -> ulozime miniaturu
			if ($newWidth !== $origWidth || $newHeight !== $origHeight) {

				$image->save($thumbPath);

				if (is_file($thumbPath)) {
					return $path . $thumbUri;
				} else {
					return $basePath . $origName;
				}
			} else {
				return $basePath . $origName;
			}
		} catch (Exception $e) {
			return $basePath . $origName;
		}
	}

	/**
	 * Vytvori jmeno generovane miniatury
	 *
	 * @param  string relativni cesta (document_root/$relPath)
	 * @param  int sirka
	 * @param  int vyska
	 * @param  int timestamp zmeny originalu
	 * @return string
	 */
	private static function getThumbName($relPath, $width, $height, $mtime, $flags, $crop)
	{
		$sep = '.';
		$tmp = explode($sep, $relPath);
		$ext = array_pop($tmp);

		// cesta k obrazku (ale bez pripony)
		$relPath = implode($sep, $tmp);

		// pripojime rozmery a mtime
		$relPath .= $width . 'x' . $height . '-' . $mtime . '.' . $flags. '.' . $crop;

		// zahashujeme a vratime priponu
		$relPath = md5($relPath) . $sep . $ext;

		return $relPath;
	}

	public static function deleteThumbsByTag($tag){
		\Benne\File::rmdir(WWW_DIR . '/' . trim(self::$thumbDirUri, '/\\').'/'. \str_replace("-", "/", \Nette\String::webalize($tag)));
	}
	
}

