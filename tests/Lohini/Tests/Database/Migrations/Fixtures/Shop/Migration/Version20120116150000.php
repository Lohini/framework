<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Migrations\Fixtures\Shop\Migration;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class Version20120116150000
extends \Lohini\Database\Migrations\AbstractMigration
{
	/**
	 * @param \Doctrine\DBAL\Schema\Schema $schema
	 */
	public function up(\Doctrine\DBAL\Schema\Schema $schema)
	{
		$this->addSql("INSERT INTO goods VALUES ('train')");
		$this->addSql("INSERT INTO goods VALUES ('car')");
	}
}
