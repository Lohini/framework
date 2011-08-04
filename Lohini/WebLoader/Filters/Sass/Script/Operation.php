<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Script;
/**
 * SassScriptOperation class file.
 * @author Chris Yates <chris.l.yates@gmail.com>
 * @copyright Copyright (c) 2010 PBM Web Development
 * @license http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Script Operation class.
 * The operation to perform.
 */
class Operation
{
	const MATCH='/^(\(|\)|\+|-|\*|\/|%|<=|>=|<|>|==|!=|=|#{|}|,|and\b|or\b|xor\b|not\b)/';

	/**
	 * @var array map symbols to tokens.
	 * A token is function, associativity, precedence, number of operands
	 */
	public static $operators=array(
		'*' => array('times', 'l', 8, 2),
		'/' => array('div', 'l', 8, 2),
		'%' => array('modulo', 'l', 8, 2),
		'+' => array('plus', 'l', 7, 2),
		'-' => array('minus', 'l', 7, 2),
		'<<' => array('shiftl', 'l', 6, 2),
		'>>' => array('shiftr', 'l', 6, 2),
		'<=' => array('lte', 'l', 5, 2),
		'>=' => array('gte', 'l', 5, 2),
		'<' => array('lt', 'l', 5, 2),
		'>' => array('gt', 'l', 5, 2),
		'==' => array('eq', 'l', 4, 2),
		'!=' => array('neq', 'l', 4, 2),
		'and' => array('and', 'l', 3, 2),
		'or' => array('or', 'l', 3, 2),
		'xor' => array('xor', 'l', 3, 2),
		'not' => array('not', 'l', 3, 1),
		'=' => array('assign', 'l', 2, 2),
		')' => array('rparen', 'l', 1),
		'(' => array('lparen', 'l', 0),
		',' => array('comma', 'l', 0, 2),
		'#{' => array('begin_interpolation'),
		'}' => array('end_interpolation'),
		);
	/**
	 * @var array operators with meaning in uquoted strings;
	 * selectors, property names and values
	 */
	public static $inStrOperators=array(',', '#{');
	/** @var array default operator token. */
	public static $defaultOperator=array('concat', 'l', 0, 2);
	/** @var string operator for this operation */
	private $operator;
	/** @var string associativity of the operator; left or right */
	private $associativity;
	/** @var int precedence of the operator */
	private $precedence;
	/** @var int number of operands required by the operator */
	private $operandCount;


	/**
	 * @param mixed $operation string: operator symbol; array: operator token
	 */
	public function __construct($operation)
	{
		if (is_string($operation)) {
			$operation=self::$operators[$operation];
			}
		$this->operator=$operation[0];
		if (isset($operation[1])) {
			$this->associativity=$operation[1];
			$this->precedence=$operation[2];
			$this->operandCount= isset($operation[3])? $operation[3] : NULL;
			}
	}

	/**
	 * Getter function for properties
	 * @param string $name name of property
	 * @return mixed value of the property
	 * @throws OperationException if the property does not exist
	 */
	public function __get($name)
	{
		if (property_exists($this, $name)) {
			return $this->$name;
			}
		else {
			throw new OperationException("Unknown property: $name", Parser::$context->node);
			}
	}

	/**
	 * Performs this operation.
	 * @param array $operands operands for the operation. The operands are Literals
	 * @return Literals\Literal the result of the operation
	 * @throws OperationException if the oprand count is incorrect or
	 * the operation is undefined
	 */
	public function perform($operands)
	{
		if (count($operands)!==$this->operandCount) {
			throw new OperationException('Incorrect operand count for '.get_class($operands[0])."; expected $this->operandCount, received ".count($operands), Parser::$context->node);
			}
		if (count($operands)>1 && is_null($operands[1])) {
			$operation='op_unary_'.$this->operator;
			}
		else {
			$operation='op_'.$this->operator;
			if ($this->associativity=='l') {
				$operands=array_reverse($operands);
				}
			}
		if (method_exists($operands[0], $operation)) {
			$op=clone $operands[0];
			return $op->$operation(!empty($operands[1])? $operands[1] : NULL);
			}
		throw new OperationException("Undefined operation '$operation' for ".get_class($operands[0]), Parser::$context->node);
	}

	/**
	 * Returns a value indicating if a token of this type can be matched at
	 * the start of the subject string.
	 * @param string the subject string
	 * @return mixed match at the start of the string or false if no match
	 */
	public static function isa($subject)
	{
		return preg_match(self::MATCH, $subject, $matches)? trim($matches[0]) : FALSE;
	}
}
