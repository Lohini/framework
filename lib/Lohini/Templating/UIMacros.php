<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Templating;

use Nette\Latte;

/**
 */
class UIMacros
extends Latte\Macros\MacroSet
{
	/**
	 * @param Latte\Compiler $compiler
	 * @return CoreMacros
	 */
	public static function install(Latte\Compiler $compiler)
	{
		$set=new static($compiler);
		$set->addMacro('asAttachment', callback($set, 'macroAsAttachment'));
		return $set;
	}

	/**
	 * {asAttachment ...}
	 *
	 * @param Latte\MacroNode $node
	 * @param Latte\PhpWriter $writer
	 * @return string
	 */
	public function macroAsAttachment(Latte\MacroNode $node, Latte\PhpWriter $writer)
	{
		return $writer->write('$netteHttpResponse->setHeader("Content-Disposition", "attachment; filename=%var")', $node->args);
	}
}
