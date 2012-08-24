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

/**
 * Caused exceptions delegates to Connection, that associates the exception with query in logger.
 */
class Statement
extends \Doctrine\DBAL\Statement
{
	/** @var \Doctrine\DBAL\Connection */
	private $connection;


	/**
	 * Creates a new <tt>Statement</tt> for the given SQL and <tt>Connection</tt>.
	 *
	 * @param string $sql The SQL of the statement.
	 * @param \Doctrine\DBAL\Connection The connection on which the statement should be executed.
	 */
	public function __construct($sql, \Doctrine\DBAL\Connection $conn)
	{
		parent::__construct($sql, $conn);
		$this->connection=$conn;
	}

	/**
	 * Executes the statement with the currently bound parameters.
	 *
	 * @param array $params
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public function execute($params=NULL)
	{
		try {
			return parent::execute($params);
			}
		catch (\PDOException $e) {
			$this->handleException($e, TRUE);
			}
	}

	/**
	 * Wraps given exception with informed PDOException, that can provide informations about connection
	 *
	 * @param \PDOException $e
	 * @param bool $endQuery
	 */
	private function handleException(\PDOException $e, $endQuery=FALSE)
	{
		if ($this->connection instanceof Connection) {
			$this->connection->handleException($e, $endQuery);
			}
	}
}
