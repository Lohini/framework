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

use Nette\Diagnostics\Debugger;

/**
 * When query fails, you can catch the PDOException, execute another query, and then render bluescreen.
 * In this case, the SQL showed in the bluescreen would not correspond to the query that actually caused the exception,
 * therefore i catch all the exceptions and tell the logger, what exceptions belongs to what query.
 *
 * @todo: more types of Exceptions (unique, nullNotAllowed, ...)
 */
class Connection
extends \Doctrine\DBAL\Connection
{
	/**
	 * @return bool
	 * @throws \Nette\InvalidStateException
	 */
	public function connect()
	{
		try {
			Debugger::tryError();
			parent::connect();
			if (Debugger::catchError($error)) {
				throw $error;
				}

			return TRUE;
			}
		catch (\ErrorException $e) {
			throw new \Nette\InvalidStateException('Connection to database could not be established: '.$e->getMessage(), 0, $e);
			}

		return FALSE;
	}

	/**
	 * Prepares an SQL statement.
	 *
	 * @param string $statement The SQL statement to prepare.
	 * @return Statement The prepared statement.
	 */
	public function prepare($statement)
	{
		$this->connect();
		return new Statement($statement, $this);
	}

	/**
	 * Executes an, optionally parameterized, SQL query.
	 *
	 * If the query is parameterized, a prepared statement is used.
	 * If an SQLLogger is configured, the execution is logged.
	 *
	 * @param string $query The SQL query to execute.
	 * @param array $params The parameters to bind to the query, if any.
	 * @param array $types
	 * @param \Doctrine\DBAL\Cache\QueryCacheProfile|NULL $qcp
	 * @return \Doctrine\DBAL\Driver\Statement The executed statement.
	 */
	public function executeQuery($query, array $params=array(), $types=array(), \Doctrine\DBAL\Cache\QueryCacheProfile $qcp=NULL)
	{
		try {
			return parent::executeQuery($query, $params, $types, $qcp);
			}
		catch (\PDOException $e) {
			$this->handleException($e, TRUE);
			}
	}

	/**
	 * Executes an SQL statement, returning a result set as a Statement object.
	 *
	 * @internal param string $statement
	 * @internal param int $fetchType
	 *
	 * @return \Doctrine\DBAL\Driver\Statement
	 */
	public function query()
	{
		try {
			$args=func_get_args();
			return call_user_func_array('parent::query', $args);
			}
		catch (\PDOException $e) {
			$this->handleException($e, TRUE);
			}
	}

	/**
	 * Executes an SQL INSERT/UPDATE/DELETE query with the given parameters
	 * and returns the number of affected rows.
	 *
	 * This method supports PDO binding types as well as DBAL mapping types.
	 *
	 * @param string $query The SQL query.
	 * @param array $params The query parameters.
	 * @param array $types The parameter types.
	 * @return int The number of affected rows.
	 */
	public function executeUpdate($query, array $params=array(), array $types=array())
	{
		try {
			return parent::executeUpdate($query, $params, $types);
			}
		catch (\PDOException $e) {
			$this->handleException($e, TRUE);
			}
	}

	/**
	 * Wraps given exception with informed PDOException, that can provide informations about connection
	 *
	 * @internal Lohini workaround for association queries with exceptions
	 *
	 * @param \PDOException $e
	 * @param bool $endQuery
	 * @throws \Lohini\Database\Doctrine\PDOException
	 */
	public function handleException(\PDOException $e, $endQuery=FALSE)
	{
		$exception=new PDOException($e, $this);
		if ($endQuery && $logger=$this->getConfiguration()->getSQLLogger()) {
			if ($logger instanceof Diagnostics\Panel) {
				/** @var Diagnostics\Panel $logger */
				$logger->queryFailed($exception);
				}
			}
		throw $exception;
	}
}
