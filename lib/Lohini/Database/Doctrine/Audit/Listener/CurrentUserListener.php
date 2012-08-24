<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Audit\Listener;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\DBAL\Platforms;

/**
 */
class CurrentUserListener
extends \Nette\Object
implements \Lohini\Extension\EventDispatcher\EventSubscriber
{
	/** @var \Lohini\Security\User */
	private $user;
	/** @var \Lohini\Database\Doctrine\Audit\AuditConfiguration */
	private $config;


	/**
	 * @param \Lohini\Database\Doctrine\Audit\AuditConfiguration $config
	 * @param \Lohini\Security\User $user
	 */
	public function __construct(\Lohini\Database\Doctrine\Audit\AuditConfiguration $config, \Lohini\Security\User $user)
	{
		$this->user=$user;
		$this->config=$config;
	}

	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			\Doctrine\DBAL\Events::postConnect
			);
	}

	/**
	 * @param \Doctrine\DBAL\Event\ConnectionEventArgs $args
	 * @throws \Nette\NotSupportedException
	 */
	public function postConnect(\Doctrine\DBAL\Event\ConnectionEventArgs $args)
	{
		// set current user to configuration
		$this->config->setCurrentUser($this->user->getId());

		$conn=$args->getConnection();
		if ($conn->getDatabasePlatform() instanceof Platforms\MySqlPlatform) {
			$variableSql='SET @lohini_current_user = ?';
			}
		elseif ($conn->getDatabasePlatform() instanceof Platforms\SqlitePlatform) {
			/** @var \Doctrine\DBAL\Schema\SqliteSchemaManager $sm */
			$sm=$conn->getSchemaManager();
			if (!$sm->tablesExist('db_session_variables')) {
				$conn->exec('CREATE TEMPORARY TABLE db_session_variables (name TEXT, value TEXT)');
				}

			$variableSql="INSERT INTO db_session_variables (name, value) VALUES ('lohini_current_user', ?)";
			}
		else {
			throw new \Nette\NotSupportedException('Sorry, but your platform is not supported.');
			}

		// pass current user to database
		$conn->executeQuery(
			$variableSql,
			array($this->config->getCurrentUser())
			);
	}
}
