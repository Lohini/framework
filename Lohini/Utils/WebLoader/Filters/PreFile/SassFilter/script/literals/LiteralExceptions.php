<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass;
/**
 * Sass literal exception classes.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Sass literal exception
 */
class LiteralException
extends \Lohini\WebLoader\Filters\Sass\ScriptParserException
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
