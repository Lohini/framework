<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\AbstractQuery;

/**
 * "Informed" exception knows, what connection caused it,
 * therefore it can be paired with right bluescreen panel handler.
 *
 * @todo: add more types (unique, nullNotAllowed, ...)
 */
class PDOException
extends \PDOException
{
	/** @var \Doctrine\DBAL\Connection */
	private $connection;


	/**
	 * @param \PDOException $previous
	 * @param \Doctrine\DBAL\Connection $connection
	 */
	public function __construct(\PDOException $previous, \Doctrine\DBAL\Connection $connection)
	{
		parent::__construct($previous->getMessage(), 0, $previous);
		$this->code=$previous->getCode(); // passing through constructor causes error
		$this->connection=$connection;
	}

	/**
	 * @return \Doctrine\DBAL\Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}

	/**
	 * This is just a paranoia, hopes no one actually serializes exceptions.
	 *
	 * @return array
	 */
	public function __sleep()
	{
		return array('message', 'code', 'file', 'line', 'errorInfo');
	}
}


/**
 */
class QueryException
extends \Lohini\Persistence\Exception
{
	/** @var \Doctrine\ORM\AbstractQuery */
	private $query;


	/**
	 * @param \Exception $previous
	 * @param AbstractQuery $query
	 * @param string $message
	 */
	public function __construct(\Exception $previous, AbstractQuery $query=NULL, $message='')
	{
		parent::__construct($message ?: $previous->getMessage(), 0, $previous);
		$this->query=$query;
	}

	/**
	 * @return AbstractQuery
	 */
	public function getQuery()
	{
		return $this->query;
	}
}


/**
 */
class SqlException
extends QueryException
{
	/**
	 * @param \PDOException $previous
	 * @param AbstractQuery $query
	 * @param string $message
	 */
	public function __construct(\PDOException $previous, AbstractQuery $query=NULL, $message='')
	{
		parent::__construct($previous, $query, $message);
	}
}
