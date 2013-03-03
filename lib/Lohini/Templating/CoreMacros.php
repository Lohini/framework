<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Templating;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Latte;

/**
 */
class CoreMacros
extends Latte\Macros\MacroSet
{
	/**
	 * @param Latte\Compiler $compiler
	 * @return CoreMacros
	 */
	public static function install(Latte\Compiler $compiler)
	{
		$me=new static($compiler);
		$me->addMacro('lohini', NULL, NULL); // dummy placeholder for finalize to take effect
		return $me;
	}

	/**
	 * Finishes template parsing.
	 *
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		$prolog=array(
			'$_l->lohini = (object)NULL;',
			'$_g->lohini = (object)array("assets" => array());',
			);
		return array(implode("\n", $prolog));
	}
}
