<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass;
/**
 * SassScript Parser exception class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * ScriptParserException class
 */
class ScriptParserException
extends \Lohini\WebLoader\Filters\Sass\Exception
{}

/**
 * ScriptLexerException class
 */
class ScriptLexerException
extends ScriptParserException
{}

/**
 * ScriptOperationException class
 */
class ScriptOperationException
extends ScriptParserException
{}

/**
 * ScriptFunctionException class
 */
class ScriptFunctionException
extends ScriptParserException
{}
