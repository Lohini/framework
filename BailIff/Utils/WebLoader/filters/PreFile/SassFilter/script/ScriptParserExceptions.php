<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters\Sass;
/**
 * SassScript Parser exception class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

/**
 * ScriptParserException class
 */
class ScriptParserException
extends \BailIff\WebLoader\Filters\Sass\Exception
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
