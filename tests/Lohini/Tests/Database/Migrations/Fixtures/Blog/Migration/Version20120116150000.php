<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Migrations\Fixtures\Blog\Migration;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\DBAL\Schema\Schema;

/**
 */
class Version20120116150000
extends \Lohini\Database\Migrations\AbstractMigration
{
	/**
	 * @param Schema $schema
	 */
	public function up(Schema $schema)
	{
		$this->addSql("INSERT INTO articles VALUES ('trains are cool', 'trains')");
		$this->addSql("INSERT INTO articles VALUES ('car are fun', 'cars')");
	}

	/**
	 * @param Schema $schema
	 */
	public function down(Schema $schema)
	{
		$this->addSql("DELETE FROM articles WHERE title='trains'");
		$this->addSql("DELETE FROM articles WHERE title='cars'");
	}
}
