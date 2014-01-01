<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Latte\Macros;

use Nette\Latte;

/**
 * /--code latte
 * {* asAttachment *}
 * {asAttachment "filename.ext"}
 * \--
 *
 * @author Lopo <lopo@lohini.net>
 */
class UIMacros
extends Latte\Macros\MacroSet
{
	/**
	 * @param Latte\Engine
	 * @return Latte\Macros\MacroSet
	 */
	public static function factory(Latte\Engine $engine)
	{
		return static::install($engine->getCompiler());
	}

	/**
	 * @param Latte\Compiler $compiler
	 * @return UIMacros
	 */
	public static function install(Latte\Compiler $compiler)
	{
		$set=new static($compiler);
		$set->addMacro('asAttachment', [$set, 'macroAsAttachment']);
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
		return $writer->write('$netteHttpResponse->setHeader("Content-Disposition", "attachment; filename=\"%raw\"")', $node->args);
	}
}
