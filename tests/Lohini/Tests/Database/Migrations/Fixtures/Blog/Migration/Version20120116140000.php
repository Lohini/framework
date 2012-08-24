<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Migrations\Fixtures\Blog\Migration;
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
class Version20120116140000
extends \Lohini\Database\Migrations\AbstractMigration
{
	/**
	 * @param Schema $schema
	 */
	public function up(Schema $schema)
	{
		$table=$schema->createTable('articles');
		$table->addColumn('content', 'text');
		$table->addColumn('title', 'string');
	}

	/**
	 * @param Schema $schema
	 */
	public function down(Schema $schema)
	{
		$schema->dropTable('articles');
	}
}
