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

use Doctrine\DBAL\Schema\Schema;

/**
 */
class Version20120116160000
extends \Lohini\Database\Migrations\AbstractMigration
{
	/**
	 * @param Schema $schema
	 */
	public function up(Schema $schema)
	{
		$this->addSql("UPDATE goods SET name='chuchu' WHERE name='train'");
	}

	/**
	 * @param Schema $schema
	 */
	public function down(Schema $schema)
	{
		$this->addSql("UPDATE goods SET name='train' WHERE name='chuchu'");
	}
}