<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters\Sass;
/**
 * Sass literal exception classes.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

/**
 * Sass literal exception
 */
class LiteralException
extends \BailIff\WebLoader\Filters\Sass\ScriptParserException
{}

/**
 * BooleanException class
 */
class BooleanException
extends LiteralException
{}

/**
 * ColourException class
 */
class ColourException
extends LiteralException
{}

/**
 * NumberException class
 */
class NumberException
extends LiteralException
{}

/**
 * StringException class
 */
class StringException
extends LiteralException
{}
