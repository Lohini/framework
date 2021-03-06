<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 */
namespace Lohini\Latte\Macros;


/**
 * /--code latte
 * {* asAttachment *}
 * {asAttachment "filename.ext"}
 * \--
 *
 * @author Lopo <lopo@lohini.net>
 */
class CoreMacros
extends \Latte\Macros\MacroSet
{
	/**
	 * @param \Latte\Compiler $compiler
	 */
	public static function install(\Latte\Compiler $compiler)
	{
		$set=new static($compiler);
		$set->addMacro('asAttachment', [$set, 'macroAsAttachment']);
	}

	/**
	 * {asAttachment ...}
	 *
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 * @return string
	 */
	public function macroAsAttachment(\Latte\MacroNode $node, \Latte\PhpWriter $writer)
	{
		return $writer->write('$netteHttpResponse->setHeader("Content-Disposition", "attachment; filename=\"%raw\"")', $node->args);
	}
}
