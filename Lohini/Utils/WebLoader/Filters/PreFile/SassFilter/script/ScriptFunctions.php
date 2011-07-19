<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass;
/**
 * SassScript functions class file.
 * 
 * Methods in this module are accessible from the SassScript context.
 * For example, you can write:
 *
 * $colour = hsl(120, 100%, 50%)
 * and it will call SassFunctions::hsl().
 *
 * There are a few things to keep in mind when modifying this module.
 * First of all, the arguments passed are SassLiteral objects.
 * Literal objects are also expected to be returned.
 *
 * Most Literal objects support the SassLiteral->value accessor
 * for getting their values. Colour objects, though, must be accessed using
 * SassColour::rgb().
 *
 * Second, making functions accessible from Sass introduces the temptation
 * to do things like database access within stylesheets.
 * This temptation must be resisted.
 * Keep in mind that Sass stylesheets are only compiled once and then left as
 * static CSS files. Any dynamic CSS should be left in <style> tags in the
 * HTML.
 * 
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
 * Script functions class.
 * A collection of functions for use in Sass\Script.
 */
class ScriptFunctions
{
	const DECREASE=FALSE;
	const INCREASE=TRUE;


	/* Colour Creation */
	/**
	 * Creates a Sass\Colour object from red, green, and blue values.
	 * @param Number $red the red component.
	 * A number between 0 and 255 inclusive, or between 0% and 100% inclusive
	 * @param Number $green the green component.
	 * A number between 0 and 255 inclusive, or between 0% and 100% inclusive
	 * @param Number $blue the blue component.
	 * A number between 0 and 255 inclusive, or between 0% and 100% inclusive
	 * @return new Colour Colour object
	 * @throws ScriptFunctionException if red, green, or blue are out of bounds
	 */
	public static function rgb($red, $green, $blue)
	{
		return self::rgba($red, $green, $blue, new Sass\Number(1));
	}

	/**
	 * Creates a Sass\Colour object from red, green, and blue values and alpha 
	 * channel (opacity).
	 * There are two overloads:
	 * * rgba(red, green, blue, alpha)
	 * @param Number the red component.
	 * A number between 0 and 255 inclusive, or between 0% and 100% inclusive
	 * @param Number the green component.
	 * A number between 0 and 255 inclusive, or between 0% and 100% inclusive
	 * @param Number the blue component.
	 * A number between 0 and 255 inclusive, or between 0% and 100% inclusive
	 * @param Number The alpha channel. A number between 0 and 1.
	 *
	 * * rgba(colour, alpha)
	 * @param Colour a Colour object
	 * @param Number The alpha channel. A number between 0 and 1.
	 * 
	 * @return new Colour Colour object
	 * @throws ScriptFunctionException if any of the red, green, or blue 
	 * colour components are out of bounds, or or the colour is not a colour, or
	 * alpha is out of bounds
	 */
	public static function rgba()
	{
		switch (func_num_args()) {
			case 2:
				$colour=func_get_arg(0);
				$alpha=func_get_arg(1);					
				Sass\Literal::assertType($colour, __NAMESPACE__.'\Colour');
				Sass\Literal::assertType($alpha, __NAMESPACE__.'\Number');
				Sass\Literal::assertInRange($alpha, 0, 1);
				return $colour->with(array('alpha' => $alpha->value));
				break;
			case 4:
				$rgba=array();
				$components=func_get_args();
				$alpha=array_pop($components);
				foreach($components as $component) {
					Sass\Literal::assertType($component, __NAMESPACE__.'\Number');
					if ($component->units=='%') {
						Sass\Literal::assertInRange($component, 0, 100, '%');
						$rgba[]=$component->value*2.55;
						}
					else {
						Sass\Literal::assertInRange($component, 0, 255);
						$rgba[]=$component->value;
						}
					}
				Sass\Literal::assertType($alpha, __NAMESPACE__.'\Number');
				Sass\Literal::assertInRange($alpha, 0, 1);
				$rgba[]=$alpha->value;
				return new Sass\Colour($rgba);
				break;
			default:
				throw new Sass\ScriptFunctionException('Incorrect argument count for '.__METHOD__.'; expected 2 or 4, received '.func_num_args(), Sass\ScriptParser::$context->node);
			}		
	}

	/**
	 * Creates a Sass\Colour object from hue, saturation, and lightness.
	 * Uses the algorithm from the
	 * {@link http://www.w3.org/TR/css3-colour/#hsl-colour CSS3 spec}.
	 * @param float $h The hue of the colour in degrees.
	 * Should be between 0 and 360 inclusive
	 * @param mixed $s The saturation of the colour as a percentage.
	 * Must be between '0%' and 100%, inclusive
	 * @param mixed @l The lightness of the colour as a percentage.
	 * Must be between 0% and 100%, inclusive
	 * @return new Colour The resulting colour
	 * @throws ScriptFunctionException if saturation or lightness are out of bounds
	 */
	public static function hsl($h, $s, $l)
	{
		return self::hsla($h, $s, $l, new Sass\Number(1));
	}

	/**
	 * Creates a Sass\Colour object from hue, saturation, lightness and alpha 
	 * channel (opacity).
	 * @param Number $h The hue of the colour in degrees.
	 * Should be between 0 and 360 inclusive
	 * @param Number $s The saturation of the colour as a percentage.
	 * Must be between 0% and 100% inclusive
	 * @param Number $l The lightness of the colour as a percentage.
	 * Must be between 0% and 100% inclusive
	 * @param float $a The alpha channel. A number between 0 and 1. 
	 * @return Colour The resulting colour
	 * @throws ScriptFunctionException if saturation, lightness or alpha are out of bounds
	 */
	public static function hsla($h, $s, $l, $a)
	{
		Sass\Literal::assertType($h, __NAMESPACE__.'\Number');
		Sass\Literal::assertType($s, __NAMESPACE__.'\Number');
		Sass\Literal::assertType($l, __NAMESPACE__.'\Number');
		Sass\Literal::assertType($a, __NAMESPACE__.'\Number');
		Sass\Literal::assertInRange($s, 0, 100, '%');
		Sass\Literal::assertInRange($l, 0, 100, '%');
		Sass\Literal::assertInRange($a, 0,   1);
		return new Sass\Colour(array('hue'=>$h, 'saturation'=>$s, 'lightness'=>$l, 'alpha'=>$a));
	}
	
	/*
	 * Colour Information
	 */

	/**
	 * Returns the red component of a colour.
	 * @param Colour $colour The colour
	 * @return Number The red component of colour
	 * @throws ScriptFunctionException If $colour is not a colour
	 */
	public static function red($colour)
	{
		Sass\Literal::assertType($colour, __NAMESPACE__.'\Colour');
		return new Sass\Number($colour->red);
	}

	/**
	 * Returns the green component of a colour.
	 * @param Colour $colour The colour
	 * @return Number The green component of colour
	 * @throws ScriptFunctionException If $colour is not a colour
	 */
	public static function green($colour)
	{
		Sass\Literal::assertType($colour, __NAMESPACE__.'\Colour');
		return new Sass\Number($colour->green);
	}
	
	/**
	 * Returns the blue component of a colour.
	 * @param Colour $colour The colour
	 * @return Number The blue component of colour
	 * @throws ScriptFunctionException If $colour is not a colour
	 */
	public static function blue($colour)
	{
		Sass\Literal::assertType($colour, __NAMESPACE__.'\Colour');
		return new Sass\Number($colour->blue);
	}

	/**
	 * Returns the hue component of a colour.
	 * @param Colour $colour The colour
	 * @return Number The hue component of colour
	 * @throws ScriptFunctionException If $colour is not a colour
	 */
	public static function hue($colour)
	{
		Sass\Literal::assertType($colour, __NAMESPACE__.'\Colour');
		return new Sass\Number($colour->hue);
	}

	/**
	 * Returns the saturation component of a colour.
	 * @param Colour $colour The colour
	 * @return Number The saturation component of colour
	 * @throws ScriptFunctionException If $colour is not a colour
	 */
	public static function saturation($colour)
	{
		Sass\Literal::assertType($colour, __NAMESPACE__.'\Colour');
		return new Sass\Number($colour->saturation);
	}

	/**
	 * Returns the lightness component of a colour.
	 * @param Colour $colour The colour
	 * @return Number The lightness component of colour
	 * @throws ScriptFunctionException If $colour is not a colour
	 */
	public static function lightness($colour)
	{
		Sass\Literal::assertType($colour, __NAMESPACE__.'\Colour');
		return new Sass\Number($colour->lightness);
	}

	/**
	 * Returns the alpha component (opacity) of a colour.
	 * @param Colour $colour The colour
	 * @return Number The alpha component (opacity) of colour
	 * @throws ScriptFunctionException If $colour is not a colour
	 */
	public static function alpha($colour)
	{
		Sass\Literal::assertType($colour, __NAMESPACE__.'\Colour');
		return new Sass\Number($colour->alpha);
	}

	/**
	 * Returns the alpha component (opacity) of a colour.
	 * @param Colour $colour The colour
	 * @return Number The alpha component (opacity) of colour
	 * @throws ScriptFunctionException If $colour is not a colour
	 */
	public static function opacity($colour)
	{
		Sass\Literal::assertType($colour, __NAMESPACE__.'\Colour');
		return new Sass\Number($colour->alpha);
	}
	
	/*
	 * Colour Adjustments
	 */

	/**
	 * Changes the hue of a colour while retaining the lightness and saturation.
	 * @param Colour $colour The colour to adjust
	 * @param Number $degrees The amount to adjust the colour by
	 * @return Colour The adjusted colour
	 * @throws ScriptFunctionException If $colour is not a colour or $degrees is not a number
	 */
	public static function adjust_hue($colour, $degrees)
	{
		Sass\Literal::assertType($colour, __NAMESPACE__.'\Colour');
		Sass\Literal::assertType($degrees, __NAMESPACE__.'\Number');
		return $colour->with(array('hue' => $colour->hue+$degrees->value));
	}

	/**
	 * Makes a colour lighter.
	 * @param Colour $colour The colour to lighten
	 * @param Number $amount The amount to lighten the colour by
	 * @param Boolean $ofCurrent Whether the amount is a proportion of the current value
	 * (true) or the total range (false).
	 * The default is false - the amount is a proportion of the total range.
	 * If the colour lightness value is 40% and the amount is 50%,
	 * the resulting colour lightness value is 90% if the amount is a proportion
	 * of the total range, whereas it is 60% if the amount is a proportion of the
	 * current value.
	 * @return Colour The lightened colour
	 * @throws ScriptFunctionException If $colour is not a colour or $amount is not a number
	 * @see lighten_rel()
	 */
	public static function lighten($colour, $amount, $ofCurrent=FALSE)
	{
		return self::adjust($colour, $amount, $ofCurrent, 'lightness', self::INCREASE, 0, 100, '%');
	}

	/**
	 * Makes a colour darker.
	 * @param Colour $colour The colour to darken
	 * @param Number $amount The amount to darken the colour by
	 * @param Boolean $ofCurrent Whether the amount is a proportion of the current value
	 * (true) or the total range (false).
	 * The default is false - the amount is a proportion of the total range.
	 * If the colour lightness value is 80% and the amount is 50%,
	 * the resulting colour lightness value is 30% if the amount is a proportion
	 * of the total range, whereas it is 40% if the amount is a proportion of the
	 * current value.
	 * @return Colour The darkened colour
	 * @throws ScriptFunctionException If $colour is not a colour or $amount is not a number
	 * @see adjust()
	 */
	public static function darken($colour, $amount, $ofCurrent=FALSE)
	{
		return self::adjust($colour, $amount, $ofCurrent, 'lightness', self::DECREASE, 0, 100, '%');
	}

	/**
	 * Makes a colour more saturated.
	 * @param Colour $colour The colour to saturate
	 * @param Number $amount The amount to saturate the colour by
	 * @param Boolean $ofCurrent Whether the amount is a proportion of the current value
	 * (true) or the total range (false).
	 * The default is false - the amount is a proportion of the total range.
	 * If the colour saturation value is 40% and the amount is 50%,
	 * the resulting colour saturation value is 90% if the amount is a proportion
	 * of the total range, whereas it is 60% if the amount is a proportion of the
	 * current value.
	 * @return Colour The saturated colour
	 * @throws ScriptFunctionException If $colour is not a colour or $amount is not a number
	 * @see adjust()
	 */
	public static function saturate($colour, $amount, $ofCurrent=FALSE)
	{
		return self::adjust($colour, $amount, $ofCurrent, 'saturation', self::INCREASE, 0, 100, '%');
	}

	/**
	 * Makes a colour less saturated.
	 * @param Colour $colour The colour to desaturate
	 * @param Number $amount The amount to desaturate the colour by
	 * @param Boolean $ofCurrent Whether the amount is a proportion of the current value
	 * (true) or the total range (false).
	 * The default is false - the amount is a proportion of the total range.
	 * If the colour saturation value is 80% and the amount is 50%,
	 * the resulting colour saturation value is 30% if the amount is a proportion
	 * of the total range, whereas it is 40% if the amount is a proportion of the
	 * current value.
	 * @return Colour The desaturateed colour
	 * @throws ScriptFunctionException If $colour is not a colour or $amount is not a number
	 * @see adjust()
	 */
	public static function desaturate($colour, $amount, $ofCurrent=FALSE)
	{
		return self::adjust($colour, $amount, $ofCurrent, 'saturation', self::DECREASE, 0, 100, '%');
	}

	/**
	 * Makes a colour more opaque.
	 * @param Colour $colour The colour to opacify
	 * @param Number $amount The amount to opacify the colour by
	 * @param bool $ofCurrent
	 * If this is a unitless number between 0 and 1 the adjustment is absolute,
	 * if it is a percentage the adjustment is relative.
	 * If the colour alpha value is 0.4
	 * if the amount is 0.5 the resulting colour alpha value  is 0.9,
	 * whereas if the amount is 50% the resulting colour alpha value  is 0.6.
	 * @return Colour The opacified colour
	 * @throws ScriptFunctionException If $colour is not a colour or $amount is not a number
	 * @see opacify_rel()
	 */
	public static function opacify($colour, $amount, $ofCurrent=FALSE)
	{
		$units=self::units($amount);
		return self::adjust($colour, $amount, $ofCurrent, 'alpha', self::INCREASE, 0, ($units==='%'? 100 : 1), $units);
	}

	/**
	 * Makes a colour more transparent.
	 * @param Colour The colour to transparentize
	 * @param Number The amount to transparentize the colour by.
	 * @param bool $ofCurrent
	 * If this is a unitless number between 0 and 1 the adjustment is absolute,
	 * if it is a percentage the adjustment is relative.
	 * If the colour alpha value is 0.8
	 * if the amount is 0.5 the resulting colour alpha value  is 0.3,
	 * whereas if the amount is 50% the resulting colour alpha value  is 0.4.
	 * @return Colour The transparentized colour
	 * @throws ScriptFunctionException If $colour is not a colour or $amount is not a number
	 */
	public static function transparentize($colour, $amount, $ofCurrent=FALSE)
	{
		$units=self::units($amount);
		return self::adjust($colour, $amount, $ofCurrent, 'alpha', self::DECREASE, 0, ($units==='%'? 100 : 1), $units);
	}

	/**
	 * Makes a colour more opaque.
	 * Alias for @link opacify().
	 * @param Colour $colour The colour to opacify
	 * @param Number $amount The amount to opacify the colour by
	 * @param Boolean $ofCurrent Whether the amount is a proportion of the current value
	 * (true) or the total range (false).
	 * @return Colour The opacified colour
	 * @throws ScriptFunctionException If $colour is not a colour or $amount is not a number
	 * @see opacify()
	 */
	public static function fade_in($colour, $amount, $ofCurrent=FALSE)
	{
		return self::opacify($colour, $amount, $ofCurrent);
	}

	/**
	 * Makes a colour more transparent.
	 * Alias for {@link transparentize}.
	 * @param Colour $colour The colour to transparentize
	 * @param Number $amount The amount to transparentize the colour by
	 * @param Boolean $ofCurrent Whether the amount is a proportion of the current value
	 * (true) or the total range (false).
	 * @return Colour The transparentized colour
	 * @throws ScriptFunctionException If $colour is not a colour or $amount is not a number
	 * @see transparentize()
	 */
	public static function fade_out($colour, $amount, $ofCurrent=FALSE)
	{
		return self::transparentize($colour, $amount, $ofCurrent);
	}
	
	/**
	 * Returns the complement of a colour.
	 * Rotates the hue by 180 degrees.
	 * @param Colour $colour The colour
	 * @return Colour The comlemented colour
	 * @uses adjust_hue()
	 */
	public static function complement($colour)
	{
		return self::adjust_hue($colour, new Sass\Number('180deg'));
	}

	/**
	 * Greyscale for non-english speakers.
	 * @param Colour $colour The colour
	 * @return Colour The greyscale colour
	 * @see desaturate()
	 */
	public static function grayscale($colour)
	{
		return self::desaturate($colour, new Sass\Number(100));
	}

	/**
	 * Converts a colour to greyscale.
	 * Reduces the saturation to zero.
	 * @param Colour $colour The colour
	 * @return Colour The greyscale colour
	 * @see desaturate()
	 */
	public static function greyscale($colour)
	{
		return self::desaturate($colour, new Sass\Number(100));
	}

	/**
	 * Mixes two colours together.
	 * Takes the average of each of the RGB components, optionally weighted by the
	 * given percentage. The opacity of the colours is also considered when
	 * weighting the components.
	 * The weight specifies the amount of the first colour that should be included
	 * in the returned colour. The default, 50%, means that half the first colour
	 * and half the second colour should be used. 25% means that a quarter of the
	 * first colour and three quarters of the second colour should be used.
	 * For example:
	 *   mix(#f00, #00f) => #7f007f
	 *   mix(#f00, #00f, 25%) => #3f00bf
	 *   mix(rgba(255, 0, 0, 0.5), #00f) => rgba(63, 0, 191, 0.75)
	 *
	 * @param Colour $colour1 The first colour
	 * @param Colour $colour2 The second colour
	 * @param float $weight Percentage of the first colour to use
	 * @return Colour The mixed colour
	 * @throws ScriptFunctionException If $colour1 or $colour2 is not a colour
	 */
	public static function mix($colour1, $colour2, $weight=NULL) {
		if (is_null($weight)) {
			$weight=new Sass\Number('50%');
			}
		Sass\Literal::assertType($colour1, __NAMESPACE__.'\Colour');
		Sass\Literal::assertType($colour2, __NAMESPACE__.'\Colour');
		Sass\Literal::assertType($weight, __NAMESPACE__.'\Number');
		Sass\Literal::assertInRange($weight, 0, 100, '%');

		/*
		 * This algorithm factors in both the user-provided weight
		 * and the difference between the alpha values of the two colours
		 * to decide how to perform the weighted average of the two RGB values.
		 *
		 * It works by first normalizing both parameters to be within [-1, 1],
		 * where 1 indicates "only use colour1", -1 indicates "only use colour 0",
		 * and all values in between indicated a proportionately weighted average.
		 *
		 * Once we have the normalized variables w and a,
		 * we apply the formula (w + a)/(1 + w*a)
		 * to get the combined weight (in [-1, 1]) of colour1.
		 * This formula has two especially nice properties:
		 *
		 * * When either w or a are -1 or 1, the combined weight is also that number
		 *  (cases where w * a == -1 are undefined, and handled as a special case).
		 *
		 * * When a is 0, the combined weight is w, and vice versa
		 *
		 * Finally, the weight of colour1 is renormalized to be within [0, 1]
		 * and the weight of colour2 is given by 1 minus the weight of colour1.
		 */
		$p=$weight->value/100;
		$w=$p*2-1;
		$a=$colour1->alpha-$colour2->alpha;

		$w1=((($w*$a==-1)? $w : ($w+$a)/(1+$w*$a))+1)/2;
		$w2=1-$w1;

		$rgb1=$colour1->rgb();
		$rgb2=$colour2->rgb();
		$rgba=array();
		foreach ($rgb1 as $key => $value) {
			$rgba[$key]=$value*$w1+$rgb2[$key]*$w2;
			}
		$rgba[]=$colour1->alpha*$p+$colour2->alpha*(1-$p);
		return new Sass\Colour($rgba);
	}
	
	/**
	 * Adjusts the colour
	 * @param Colour $colour the colour to adjust
	 * @param Number $amount the amount to adust by
	 * @param boolean $ofCurrent whether the amount is a proportion of the current value or
	 * the total range
	 * @param string $attribute the attribute to adjust
	 * @param boolean $op whether to decrease (false) or increase (true) the value of the attribute
	 * @param float $min minimum value the amount can be
	 * @param float $max maximum value the amount can bemixed
	 * @param string $units amount units
	 * @return Colour
	 */
	private static function adjust($colour, $amount, $ofCurrent, $attribute, $op, $min, $max, $units='')
	{
		Sass\Literal::assertType($colour, __NAMESPACE__.'\Colour');
		Sass\Literal::assertType($amount, __NAMESPACE__.'\Number');
		Sass\Literal::assertInRange($amount, $min, $max, $units);
		if (!is_bool($ofCurrent)) {
			Sass\Literal::assertType($ofCurrent, __NAMESPACE__.'\Boolean');
			$ofCurrent=$ofCurrent->value;
			}
		
		$amount=$amount->value*(($attribute==='alpha' && $ofCurrent && $units==='')? 100 : 1); 

		return $colour->with(
			array(
				$attribute => self::inRange(
					($ofCurrent?
						$colour->$attribute*(1+($amount*($op===self::INCREASE? 1 : -1))/100)
						: $colour->$attribute+($amount*($op===self::INCREASE? 1 : -1))
						),
					$min,
					$max
					)
				)
			);
	}
	
	/* Number Functions */
	/**
	 * Finds the absolute value of a number.
	 * For example:
	 *		 abs(10px) => 10px
	 *		 abs(-10px) => 10px
	 *
	 * @param Number $number The number to round
	 * @return Number The absolute value of the number
	 * @throws ScriptFunctionException If $number is not a number
	 */
	public static function abs($number)
	{
		Sass\Literal::assertType($number, __NAMESPACE__.'\Number');
		return new Sass\Number(abs($number->value).$number->units);
	}

	/**
	 * Rounds a number up to the nearest whole number.
	 * For example:
	 *		 ceil(10.4px) => 11px
	 *		 ceil(10.6px) => 11px
	 *
	 * @param Number $number The number to round
	 * @return Number The rounded number
	 * @throws ScriptFunctionException If $number is not a number
	 */
	public static function ceil($number)
	{
		Sass\Literal::assertType($number, __NAMESPACE__.'\Number');
		return new Sass\Number(ceil($number->value).$number->units);
	}

	/**
	 * Rounds down to the nearest whole number.
	 * For example:
	 *		 floor(10.4px) => 10px
	 *		 floor(10.6px) => 10px
	 *
	 * @param Number $number The number to round
	 * @return Number The rounded number
	 * @throws ScriptFunctionException If $value is not a number
	 */
	public static function floor($number)
	{
		Sass\Literal::assertType($number, __NAMESPACE__.'\Number');
		return new Sass\Number(floor($number->value).$number->units);
	}

	/**
	 * Rounds a number to the nearest whole number.
	 * For example:
	 *		 round(10.4px) => 10px
	 *		 round(10.6px) => 11px
	 *
	 * @param Number $number The number to round
	 * @return Number The rounded number
	 * @throws ScriptFunctionException If $number is not a number
	 */
	public static function round($number)
	{
		Sass\Literal::assertType($number, __NAMESPACE__.'\Number');
		return new Sass\Number(round($number->value).$number->units);
	}
	
	/**
	 * Returns true if two numbers are similar enough to be added, subtracted,
	 * or compared.
	 * @param Number $number1 The first number to test
	 * @param Number $number2 The second number to test
	 * @return Boolean True if the numbers are similar
	 * @throws ScriptFunctionException If $number1 or $number2 is not a number
	 */
	public static function comparable($number1, $number2)
	{
		Sass\Literal::assertType($number1, __NAMESPACE__.'\Number');
		Sass\Literal::assertType($number2, __NAMESPACE__.'\Number');
		return new Sass\Boolean($number1->isComparableTo($number2));
	}
	
	/**
	 * Converts a decimal number to a percentage.
	 * For example:
	 *		 percentage(100px / 50px) => 200%
	 *
	 * @param Number $number The decimal number to convert to a percentage
	 * @return Number The number as a percentage
	 * @throws ScriptFunctionException If $number isn't a unitless number
	 */
	public static function percentage($number)
	{
		if (!$number instanceof Sass\Number || $number->hasUnits()) {
			throw new Sass\ScriptFunctionException('number must be a unitless Sass\Number', Sass\ScriptParser::$context->node);
			}
		$number->value*=100;
		$number->units='%';
		return $number;
	}

	/**
	 * Inspects the unit of the number, returning it as a quoted string.
	 * Alias for units.
	 * @param Number $number The number to inspect
	 * @return String The units of the number
	 * @throws ScriptFunctionException If $number is not a number
	 * @see units()
	 */
	public static function unit($number)
	{
		return self::units($number);
	}

	/**
	 * Inspects the units of the number, returning it as a quoted string.
	 * @param Number $number The number to inspect
	 * @return String The units of the number
	 * @throws ScriptFunctionException If $number is not a number
	 */
	public static function units($number)
	{
		Sass\Literal::assertType($number, __NAMESPACE__.'\Number');
		return new Sass\String($number->units);
	}

	/**
	 * Inspects the unit of the number, returning a boolean indicating if it is unitless.
	 * @param Number $number The number to inspect
	 * @return Boolean True if the number is unitless, false if it has units.
	 * @throws ScriptFunctionException If $number is not a number
	 */
	public static function unitless($number)
	{
		Sass\Literal::assertType($number, __NAMESPACE__.'\Number');
		return new Sass\Boolean(!$number->hasUnits());
	}

	/* String Functions */
	/**
	 * Add quotes to a string if the string isn't quoted,
	 * or returns the same string if it is.
	 * @param string $string String to quote
	 * @return String Quoted string
	 * @throws ScriptFunctionException If $string is not a string
	 * @see unquote()
	 */
	public static function quote($string)
	{
		Sass\Literal::assertType($string, __NAMESPACE__.'\String');
		return new Sass\String('"'.$string->value.'"');
	}

	/**
	 * Removes quotes from a string if the string is quoted, or returns the same
	 * string if it's not.
	 * @param string $string String to unquote
	 * @return String Unuoted string
	 * @throws ScriptFunctionException If $string is not a string
	 * @see quote()
	 */
	public static function unquote($string)
	{
		Sass\Literal::assertType($string, __NAMESPACE__.'\String');
		return new Sass\String($string->value);
	}

	/**
	 * Returns the variable whose name is the string.
	 * @param string $string String to unquote
	 * @return String
	 * @throws ScriptFunctionException If $string is not a string
	 */
	public static function get_var($string)
	{
		Sass\Literal::assertType($string, __NAMESPACE__.'\String');
		return new Sass\String($string->toVar());
	}

	/* Misc. Functions */
	/**
	 * Inspects the type of the argument, returning it as an unquoted string.
	 * @param Literal $obj The object to inspect
	 * @return String The type of object
	 * @throws ScriptFunctionException If $obj is not an instance of a Literal
	 */
	public static function type_of($obj)
	{
		Sass\Literal::assertType($obj, Sass\Literal);
		return new Sass\String($obj->typeOf);
	}
	
	/**
	 * Ensures the value is within the given range, clipping it if needed.
	 * @param float $value the value to test
	 * @param float $min the minimum value
	 * @param float $max the maximum value
	 * @return the value clipped to the range
	 */
	private static function inRange($value, $min, $max)
	{
		return ($value<$min? $min : ($value>$max? $max : $value));
	}
}
