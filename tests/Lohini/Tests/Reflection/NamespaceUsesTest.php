<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Reflection;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */
use Lohini,
	Lohini\Reflection\NamespaceUses,
	Nette;

/**
 */
class NamespaceUsesTest
extends Lohini\Testing\TestCase
{
	public function testParsing()
	{
		$parser=new NamespaceUses($this->getReflection());
		$this->assertEquals(array(
			'Lohini',
			'Lohini\Reflection\NamespaceUses',
			'Nette'
		), $parser->parse());
	}
}
