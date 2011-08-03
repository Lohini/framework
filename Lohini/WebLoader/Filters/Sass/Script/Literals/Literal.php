<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Script\Literals;
/**
 * SassLiteral class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\WebLoader\Filters\Sass\Script;

/**
 * Literal class.
 * Base class for all Sass literals.
 * Sass data types are extended from this class and these override the operation
 * methods to provide the appropriate semantics.
 */
abstract class Literal
{
	/** @var array maps class names to data types */
	static private $typeOf=array(
		'Boolean' => 'bool',
		'Colour' => 'color',
		'Number' => 'number',
		'String' => 'string'
		);
	/** @var mixed value of the literal type */
	protected $value;


	/**
	 * @param string $value value of the literal type
	 * @param $context
	 * @return Literal
	 */
	public function __construct($value=NULL, $context)
	{
		$this->value=$value;
		$this->context=$context;
	}

	/**
	 * Getter.
	 * @param string name of property to get
	 * @return mixed return value of getter function
	 * @throws LiteralException
	 */
	public function __get($name)
	{
		$getter='get'.ucfirst($name);
		if (method_exists($this, $getter)) {
			return $this->$getter();
			}
		else {
			throw new LiteralException("No getter function for $name", Script\Parser::$context->node);
			}
	}
	
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * Returns the boolean representation of the value of this
	 * @return boolean the boolean representation of the value of this
	 */
	public function toBoolean()
	{
		return (boolean)$this->value || $this->value===NULL;
	}

	/**
	 * Returns the type of this
	 * @return string the type of this
	 */
	protected function getTypeOf()
	{
		$class=explode('\\', get_class($this));
		return self::$typeOf[$class[count($class)-1]];
	}

	/**
	 * Returns the value of this
	 * @return mixed the value of this
	 * @throws LiteralException
	 */
	protected function getValue()
	{
		throw new LiteralException('Child classes must override this method', Script\Parser::$context->node);
	}
	
	/**
	 * Adds a child object to this.
	 * @param Literal $sassLiteral the child object
	 */
	public function addChild($sassLiteral)
	{
		$this->children[]=$sassLiteral;
	}

	/**
	 * Sass\Script '+' operation.
	 * @param Literal $other value to add
	 * @return String the string values of this and other with no seperation
	 */
	public function op_plus($other)
	{
		return new String($this->toString().$other->toString());
	}

	/**
	 * Sass\Script '-' operation.
	 * @param Literal $other value to subtract
	 * @return String the string values of this and other seperated by '-'
	 */
	public function op_minus($other)
	{
		return new String($this->toString().'-'.$other->toString());
	}

	/**
	 * Sass\Script '*' operation.
	 * @param Literal $other value to multiply by
	 * @return String the string values of this and other seperated by '*'
	 */
	public function op_times($other)
	{
		return new String($this->toString().'*'.$other->toString());
	}

	/**
	 * Sass\Script '/' operation.
	 * @param Literal $other value to divide by
	 * @return String the string values of this and other seperated by '/'
	 */
	public function op_div($other)
	{
		return new String($this->toString().'/'.$other->toString());
	}

	/**
	 * Sass\Script '%' operation.
	 * @param Literal $other value to take the modulus of
	 * @return Literal result
	 * @throws LiteralException if modulo not supported for the data type
	 */
	public function op_modulo($other)
	{
		throw new LiteralException(get_class($this).' does not support Modulus.', Script\Parser::$context->node);
	}

	/**
	 * Bitwise AND the value of other and this value
	 * @param string $other value to bitwise AND with
	 * @return string result
	 * @throws LiteralException if bitwise AND not supported for the data type
	 */
	public function op_bw_and($other)
	{
		throw new LiteralException(get_class($this).' does not support Bitwise AND.', Script\Parser::$context->node);
	}

	/**
	 * Bitwise OR the value of other and this value
	 * @param Number $other value to bitwise OR with
	 * @return string result
	 * @throws LiteralException if bitwise OR not supported for the data type
	 */
	public function op_bw_or($other)
	{
		throw new LiteralException(get_class($this).' does not support Bitwise OR.', Script\Parser::$context->node);
	}

	/**
	 * Bitwise XOR the value of other and the value of this
	 * @param Number $other value to bitwise XOR with
	 * @return string result
	 * @throws LiteralException if bitwise XOR not supported for the data type
	 */
	public function op_bw_xor($other)
	{
		throw new LiteralException(get_class($this).' does not support Bitwise XOR.', Script\Parser::$context->node);
	}

	/**
	 * Bitwise NOT the value of other and the value of this
	 * @param Number value to bitwise NOT with
	 * @return string result
	 * @throws LiteralException if bitwise NOT not supported for the data type
	 */
	public function op_bw_not()
	{
		throw new LiteralException(get_class($this).' does not support Bitwise NOT.', Script\Parser::$context->node);
	}

	/**
	 * Shifts the value of this left by the number of bits given in value
	 * @param Number $other amount to shift left by
	 * @return string result
	 * @throws LiteralException if bitwise Shift Left not supported for the data type
	 */
	public function op_shiftl($other)
	{
		throw new LiteralException(get_class($this).' does not support Bitwise Shift Left.', Script\Parser::$context->node);
	}

	/**
	 * Shifts the value of this right by the number of bits given in value
	 * @param Number $other amount to shift right by
	 * @return string result
	 * @throws LiteralException if bitwise Shift Right not supported for the data type
	 */
	public function op_shiftr($other)
	{
		throw new LiteralException(get_class($this).' does not support Bitwise Shift Right.', Script\Parser::$context->node);
	}

	/**
	 * The Sass\Script and operation.
	 * @param Literal $other the value to and with this
	 * @return Literal other if this is boolean true, this if false
	 */
	public function op_and($other)
	{
		return ($this->toBoolean()? $other : $this);
	}
	
	/**
	 * The Sass\Script or operation.
	 * @param Literal $other the value to or with this
	 * @return Literal this if this is boolean true, other if false
	 */
	public function op_or($other)
	{
		return ($this->toBoolean()? $this : $other);
	}

	/**
	 * @param Literal $other the value to assign
	 * @return Literal assigned
	 */
	public function op_assign($other)
	{
		return $other;
	}
	
	/**
	 * The Sass\Script xor operation.
	 * @param Literal $other the value to xor with this
	 * @return Boolean Boolean object with the value true if this or
	 * other, but not both, are true, false if not
	 */
	public function op_xor($other)
	{
		return new Boolean($this->toBoolean() xor $other->toBoolean());
	}
	
	/**
	 * The Sass\Script not operation.
	 * @return Boolean Boolean object with the value true if the
	 * boolean of this is false or false if it is true
	 */
	public function op_not()
	{
		return new Boolean(!$this->toBoolean());
	}
	
	/**
	 * The Sass\Script > operation.
	 * @param Literal $other the value to compare to this
	 * @return Boolean Boolean object with the value true if the values
	 * of this is greater than the value of other, false if it is not
	 */
	public function op_gt($other)
	{
		return new Boolean($this->value>$other->value);
	}
	
	/**
	 * The Sass\Script >= operation.
	 * @param Literal $other the value to compare to this
	 * @return Boolean Boolean object with the value true if the values
	 * of this is greater than or equal to the value of other, false if it is not
	 */
	public function op_gte($other)
	{
		return new Boolean($this->value>=$other->value);
	}
	
	/**
	 * The Sass\Script < operation.
	 * @param Literal $other the value to compare to this
	 * @return Boolean Boolean object with the value true if the values
	 * of this is less than the value of other, false if it is not
	 */
	public function op_lt($other)
	{
		return new Boolean($this->value<$other->value);
	}
	
	/**
	 * The Sass\Script <= operation.
	 * @param Literal $other the value to compare to this
	 * @return Boolean Boolean object with the value true if the values
	 * of this is less than or equal to the value of other, false if it is not
	 */
	public function op_lte($other)
	{
		return new Boolean($this->value<=$other->value);
	}
	
	/**
	 * The Sass\Script == operation.
	 * @param Literal $other the value to compare to this
	 * @return Boolean Boolean object with the value true if this and
	 * other are equal, false if they are not
	 */
	public function op_eq($other)
	{
		return new Boolean($this==$other);
	}
	
	/**
	 * The Sass\Script != operation.
	 * @param Literal $other the value to compare to this
	 * @return Boolean Boolean object with the value true if this and
	 * other are not equal, false if they are
	 */
	public function op_neq($other)
	{
		return new Boolean(!$this->op_eq($other)->toBoolean());
	}
	
	/**
	 * The Sass\Script default operation (e.g. $a $b, "foo" "bar").
	 * @param Literal $other the value to concatenate with a space to this
	 * @return String the string values of this and other seperated by " "
	 */
	public function op_concat($other)
	{
		return new String($this->toString().' '.$other->toString());
	}

	/**
	 * Sass\Script ',' operation.
	 * @param Literal $other the value to concatenate with a comma to this
	 * @return String the string values of this and other seperated by ","
	 */
	public function op_comma($other)
	{
		return new String($this->toString().', '.$other->toString());
	}
	
	/**
	 * Asserts that the literal is the expected type 
	 * @param Literal the literal to test
	 * @param string expected type
	 * @throws Script\FunctionException if value is not the expected type
	 */
	public static function assertType($literal, $type)
	{
		if (!$literal instanceof $type) {
			throw new Script\FunctionException(($literal instanceof Literal? $literal->typeOf : 'literal')." must be a $type", Script\Parser::$context->node);
			}
	}
	
	/**
	 * Asserts that the value of a literal is within the expected range 
	 * @param Literal $literal the literal to test
	 * @param float $min the minimum value
	 * @param float $max the maximum value
	 * @param string $units the units.
	 * @throws Script\FunctionException if value is not the expected type
	 */
	 public static function assertInRange($literal, $min, $max, $units='')
	 {
	 	 if ($literal->value<$min || $literal->value>$max) {
			throw new Script\FunctionException("$literal->typeOf must be between $min$units and $max$units inclusive", Script\Parser::$context->node);
			}
	}

	/**
	 * Returns a string representation of the value.
	 * @return string string representation of the value.
	 */
	abstract public function toString();

	/**
	 * Returns a value indicating if a token of this type can be matched at
	 * the start of the subject string.
	 * @param string $subject the subject string
	 * @return mixed match at the start of the string or false if no match
	 */
	public static function isa($subject)
	{
		throw new LiteralException('Child classes must override this method');
	}
}
