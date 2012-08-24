<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Reflection;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class Property
extends \Nette\Reflection\Property
{
	/**
	 * @return int
	 */
	public function getLine()
	{
		$class=$this->getDeclaringClass();

		$context='file';
		$contextBrackets=0;
		foreach (token_get_all(file_get_contents($class->getFileName())) as $token) {
			if ($token==='{') {
				$contextBrackets++;
				}
			elseif ($token === '}') {
				$contextBrackets--;
				}

			if (!is_array($token)) {
				continue;
				}

			if ($token[0]===T_CLASS) {
				$context='class';
				$contextBrackets=0;
				}
			elseif ($context==='class' && $contextBrackets===1 && $token[0]===T_VARIABLE) {
				if ($token[1]==='$'.$this->getName()) {
					return $token[2];
					}
				}
			}

		return NULL;
	}
}
