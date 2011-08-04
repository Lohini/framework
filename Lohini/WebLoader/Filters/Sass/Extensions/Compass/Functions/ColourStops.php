<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Extensions\Compass\Functions;
/**
 * Compass extension SassScript colour stop objects and functions class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\WebLoader\Filters\Sass;
 
/**
 * Compass extension SassScript colour stops functions class.
 * A collection of functions for use in SassSCript.
 */
class ColourStops
{
	/**
	 * returns color-stop() calls for use in webkit.
	 * @param Sass\Extensions\Compass\CompassList $colour_list
	 * @return Sass\Script\Literals\String
	 */
	public static function grad_color_stops($colour_list)
	{
		return self::grad_colour_stops($colour_list);
	}

	/**
	 * @param Sass\Extensions\Compass\CompassList $colour_list
	 * @return Sass\Script\Literals\String
	 * @throws Sass\Script\FunctionException
	 */
	public static function grad_colour_stops($colour_list)
	{
		Sass\Script\Literals\Literal::assertType($colour_list, '\Lohini\WebLoader\Filters\Sass\Extensions\Compass\CompassList');
		self::normalize_stops($colour_list);
		$v=array_reverse($colour_list->values);
		$max=$v[0]->stop;
		$last_value=NULL;

		$colourStops=array();

		foreach ($colour_list->values as $pos) {
			# have to convert absolute units to percentages for use in colour stop functions.
			$stop=$pos->stop;
			if ($stop->numeratorUnits===$max->numeratorUnits) {
				$stop=$stop->op_div($max)->op_times(new Sass\Script\Literals\Number('100%'));
				}
			# Make sure the colour stops are specified in the right order.
			if ($last_value && $last_value->value>$stop->value) {
				throw new Sass\Script\FunctionException('Colour stops must be specified in increasing order', Sass\Script\Parser::$context->node);
				}

			$last_value=$stop;
            $colourStops[]="color-stop({$stop->toString()}, {$pos->colour->toString()})";
			}

		return new Sass\Script\Literals\String(join(', ', $colourStops));
	}

	/**
	 * returns the end position of the gradient from the colour stop
	 * @param Sass\Extensions\Compass\CompassList $colourList
	 * @param Sass\Script\Literals\Boolean $radial
	 * @return type
	 */
	public static function grad_end_position($colourList, $radial=NULL)
	{
		Sass\Script\Literals\Literal::assertType($colourList, '\Lohini\WebLoader\Filters\Sass\Extensions\Compass\CompassList');
		if ($radial===NULL) {
			$radial=new Sass\Script\Literals\Boolean(FALSE);
			}
		else {
			Sass\Script\Literals\Literal::assertType($radial, '\Lohini\WebLoader\Filters\Sass\Script\Literals\Boolean');
			}
		return self::grad_position($colourList, new Sass\Script\Literals\Number(sizeof($colourList->values)), new Sass\Script\Literals\Number(100), $radial);
	}

	public static function grad_position($colourList, $index, $default, $radial=NULL)
	{
		Sass\Script\Literals\Literal::assertType($colourList, '\Lohini\WebLoader\Filters\Sass\Extensions\Compass\CompassList');
		if ($radial===NULL) {
			$radial=new Sass\Script\Literals\Boolean(false);
			}
		else {
			Sass\Script\Literals\Literal::assertType($radial, '\Lohini\WebLoader\Filters\Sass\Script\Literals\Boolean');
			}
		$stop=$colourList->values[$index->value-1]->stop;
		if ($stop && $radial->value) {
			$orig_stop=$stop;
			if ($stop->isUnitless()) {
				if ($stop->value<=1) {
					# A unitless number is assumed to be a percentage when it's between 0 and 1
					$stop=$stop->op_times(new Sass\Script\Literals\Number('100%'));
					}
				else {
					# Otherwise, a unitless number is assumed to be in pixels
					$stop=$stop->op_times(new Sass\Script\Literals\Number('1px'));
					}
				}

			if ($stop->numeratorUnits==='%'
				&& isset($colourList->values[sizeof($colourList->values)-1]->stop)
				&& $colourList->values[sizeof($colourList->values)-1]->stop->numeratorUnits==='px'
				) {
				$stop=$stop->op_times($colourList->values[sizeof($colourList->values)-1]->stop)->op_div(new Sass\Script\Literals\Number('100%'));
				}
			//Compass::Logger.new.record(:warning, "Webkit only supports pixels for the start and end stops for radial gradients. Got: #{orig_stop}") if stop.numerator_units != ["px"];
			return $stop->op_div(new Sass\Script\Literals\Number('1'.$stop->units));
			}
		elseif ($stop) {
			return $stop;
			}
		return $default;
	}

	/**
	 * takes the given position and returns a point in percentages
	 * @param type $position
	 * @return Sass\Script\Literals\String
	 */
	public static function grad_point($position)
	{
		$position=$position->value;
		if (strpos($position, ' ')!==FALSE) {
			if (preg_match('/(top|bottom|center) (left|right|center)/', $position, $matches)) {
				$position="{$matches[2]} {$matches[1]}";
				}
			}
		else {
			switch ($position) {
				case 'top':
				case 'bottom':
					$position="left $position";
					break;
				case 'left':
				case 'right':
					$position.=' top';
					break;
				}
			}

		return new Sass\Script\Literals\String(preg_replace(
				array('/top/', '/bottom/', '/left/', '/right/', '/center/'),
				array('0%', '100%', '0%', '100%', '50%'),
				$position
				));
	}

	public static function color_stops()
	{
		$args=func_get_args();
		return call_user_func_array(array('\Lohini\WebLoader\Filters\Sass\Extentions\Compass\Functions\ColourStops', 'colour_stops'), $args);
	}

	/**
	 * @return Sass\Extentions\Compass\CompassList
	 * @throws Sass\Script\FunctionException
	 */
	public static function colour_stops()
	{
		$args=func_get_args();
		$list=array();

		foreach ($args as $arg) {
			if ($arg instanceof Sass\Script\Literals\Colour) {
				$list[]=new Sass\Extentions\Compass\ColourStop($arg);
				}
			elseif ($arg instanceof Sass\Script\Literals\String) {
				# We get a string as the result of concatenation
				# So we have to reparse the expression
				$colour= $stop= NULL;
				if (empty($parser)) {
					$parser=new Sass\Script\Parser;
					}
				$expr=$parser->parse($arg->value, Sass\Script\Parser::$context);

				$x=array_pop($expr);

				if ($x instanceof Sass\Script\Literals\Colour) {
					$colour=$x;
					}
				elseif ($x instanceof Sass\Script\Operation) {
					if ($x->operator!='concat') {
						# This should never happen.
						throw new Sass\Script\FunctionException("Couldn't parse a colour stop from: $arg->value", Sass\Script\Parser::$context->node);
						}
					$colour=$expr[0];
					$stop=$expr[1];
					}
				else {
					throw new Sass\Script\FunctionException("Couldn't parse a colour stop from: $arg->value", Sass\Script\Parser::$context->node);
					}
				$list[]=new Sass\Extentions\Compass\ColourStop($colour, $stop);
				}
			else {
				throw new Sass\Script\FunctionException("Not a valid color stop: $arg->value", Sass\Script\Parser::$context->node);
				}
			}
		return new Sass\Extentions\Compass\CompassList($list);
	}

	/**
	 * @param type $colourList
	 * @return NULL
	 * @throws Sass\Script\FunctionException
	 */
	private static function normalize_stops($colourList)
	{
		$positions=$colourList->values;
		$s=sizeof($positions);

		# fill in the start and end positions, if unspecified
		if (empty($positions[0]->stop)) {
			$positions[0]->stop=new Sass\Script\Literals\Number(0);
			}
		if (empty($positions[$s-1]->stop)) {
			$positions[$s-1]->stop=new Sass\Script\Literals\Number('100%');
			}

		# fill in empty values
		for ($i=0; $i<$s; $i++) {
			if ($positions[$i]->stop===NULL) {
				$num=2;
				for ($j=$i+1; $j<$s; $j++) {
					if (isset($positions[$j]->stop)) {
						$positions[$i]->stop=$positions[$i-1]->stop->op_plus($positions[$j]->stop->op_minus($positions[$i-1]->stop))->op_div(new Sass\Script\Literals\Number($num));
						break;
						}
					$num+=1;
					}
				}
			}
		# normalize unitless numbers
		foreach ($positions as &$pos) {
			if ($pos->stop->isUnitless()) {
				$pos->stop= $pos->stop->value<=1
						? $pos->stop->op_times(new Sass\Script\Literals\Number('100%'))
						: $pos->stop->op_times(new Sass\Script\Literals\Number('1px'))
					;
				}
			}
		if ($positions[$s-1]->stop->op_eq(new Sass\Script\Literals\Number('0px'))->toBoolean()
			|| $positions[$s-1]->stop->op_eq(new Sass\Script\Literals\Number('0%'))->toBoolean()
			) {
			throw new Sass\Script\FunctionException('Colour stops must be specified in increasing order', Sass\Script\Parser::$context->node);
			}
		return NULL;
	}
}
