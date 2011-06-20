<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Localization\Filters;
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 * @author	Patrik Votoček
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

use BailIff\Localization\Dictionary;

/**
 * Latte translation extractor filter
 */
class Latte
extends \Nette\Object
implements \BailIff\Localization\IFilter
{
	/** @var array */
	public $exts=array('*.latte');

	/**
	 * @param \BailIff\Localization\Dictionary
	 */
	public function process(Dictionary $dictionary)
	{
		$dictionary->freeze();

		$parser=new \Nette\Latte\Parser;
		$macros=LatteMacros::install($parser);

		$files=\Nette\Utils\Finder::findFiles($this->exts)->from($dictionary->dir);
		foreach ($files as $file) {
			$parser->parse(file_get_contents($file->getRealpath()));
			foreach ($macros->translations as $message) {
				$translation=(array)$message;
				$message= is_array($message)? reset($message) : $message;

				if ($dictionary->hasTranslation($message)) {
					continue;
					}

				$dictionary->addTranslation($message, $translation, Dictionary::STATUS_UNTRANSLATED);
				}
			}
	}
}
