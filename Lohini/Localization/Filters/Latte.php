<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Localization\Filters;
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 * @author	Patrik Votoček
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Localization\Dictionary;

/**
 * Latte translation extractor filter
 */
class Latte
extends \Nette\Object
implements \Lohini\Localization\IFilter
{
	/** @var array */
	public $exts=array('*.latte');

	/**
	 * @param \Lohini\Localization\Dictionary
	 */
	public function process(Dictionary $dictionary)
	{
		$dictionary->freeze();

		$latte=new \Nette\Latte\Engine;
		$parser=$latte->parser;
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
