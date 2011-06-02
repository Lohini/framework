<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters\Sass;
/**
 * SassRenderer class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

use BailIff\WebLoader\Filters\Sass;

/**
 * Renderer class
 */
class Renderer
{
	/**#@+
	 * Output Styles
	 */
	const STYLE_COMPRESSED='compressed';
	const STYLE_COMPACT='compact';
	const STYLE_EXPANDED='expanded';
	const STYLE_NESTED='nested';
	/**#@-*/
	const INDENT='  ';


	/**
	 * Returns the renderer for the required render style.
	 * @param string $style render style
	 * @return Renderer
	 */
	public static function getRenderer($style)
	{
		switch ($style) {
			case self::STYLE_COMPACT:
				return new Sass\CompactRenderer;
			case self::STYLE_COMPRESSED:
				return new Sass\CompressedRenderer;
			case self::STYLE_EXPANDED:
				return new Sass\ExpandedRenderer;
			case self::STYLE_NESTED:
				return new Sass\NestedRenderer;
			}
	}
}