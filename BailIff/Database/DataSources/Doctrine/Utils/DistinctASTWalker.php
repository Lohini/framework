<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\DataSources\Doctrine\Utils;

use Doctrine\ORM\Query\AST;

/**
 * Distinct AST walker
 * used for getting distinct values of 1 column
 *
 * @author Lopo <lopo@losys.eu>
 */
class DistinctASTWalker
extends \Doctrine\ORM\Query\TreeWalkerAdapter
{
	public function walkSelectStatement(AST\SelectStatement $ast)
	{
		$column=$this->_getQuery()->getHint('distinct');

		$ast->selectClause->isDistinct=TRUE;
		list($parentName, $distinct)=explode('.', $column);
		$pathExpression=new AST\PathExpression(
					AST\PathExpression::TYPE_STATE_FIELD | AST\PathExpression::TYPE_SINGLE_VALUED_ASSOCIATION,
					$parentName,
					$distinct
					);
		$pathExpression->type=AST\PathExpression::TYPE_STATE_FIELD;
		$ast->selectClause->selectExpressions=array(
			new AST\SelectExpression(
				$pathExpression,
				NULL
				)
			);

		$ast->orderByClause=array(); //reset ORDER BY clause, it is not necessary
	}
}
