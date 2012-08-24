<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Audit;
/**
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * @method string getPrefix()
 * @method string getSuffix()
 * @method string getTableName()
 * @method string getCurrentUser()
 * @method setCurrentUser(string $username)
 */
class AuditConfiguration
extends \Nette\Object
{
	const REVISION_ID='_revision';
	const REVISION_PREVIOUS='_revision_previous';

	/** @var string */
	public $prefix;
	/** @var string */
	public $suffix;
	/** @var string */
	public $tableName;
	/** @var string */
	public $currentUser;


	/**
	 * @param $name
	 * @param $args
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		return \Nette\ObjectMixin::callProperty($this, $name, $args);
	}
}
