<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Utils;

/**
 * Browser utils
 *
 * @author Lopo <lopo@lohini.net>
 */
class Browser
{
	public static function getLanguagesPriority()
	{
		if (!$header=\Nette\Environment::getHttpRequest()->getHeader('accept-language')) {
			return NULL;
			}
		$prefered_languages=array();
		if (preg_match_all("#([^;,]+)(;[^,0-9]*([0-9\.]+)[^,]*)?#i", $header, $matches, PREG_SET_ORDER)) {
			$priority=1.0;
			foreach ($matches as $match) {
				if (!isset($match[3])) {
					$pr=$priority;
					$priority-=0.001;
					}
				else {
					$pr=floatval($match[3]);
					}
				$prefered_languages[$match[1]]=$pr;
				}
			arsort($prefered_languages, SORT_NUMERIC);
			return $prefered_languages;
			}
	}
}
