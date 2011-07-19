<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\Doctrine;
/**
 * @author Filip Procházka
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

class QueryException
extends \Exception
{
	/** @var \Doctrine\ORM\Query */
	private $query;


	/**
	 * @param string $message
	 * @param \Doctrine\ORM\Query $query
	 * @param \Exception $previous
	 */
	public function __construct($message='', \Doctrine\ORM\Query $query, \Exception $previous=NULL)
	{
		parent::__construct($message, NULL, $previous);
		$this->query=$query;
	}

	/**
	 * @return \Doctrine\ORM\Query
	 */
	public function getQuery()
	{
		return $this->query;
	}
}
