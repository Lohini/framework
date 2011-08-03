<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Script;
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
 * ParserException class
 */
class ParserException
extends \Lohini\WebLoader\Filters\Sass\Exception
{}

/**
 * ScriptLexerException class
 */
class LexerException
extends ParserException
{}

/**
 * ScriptOperationException class
 */
class OperationException
extends ParserException
{}

/**
 * ScriptFunctionException class
 */
class FunctionException
extends ParserException
{}
