<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\DataSources\Utils;

class WildcardHelper
{
	/**
	 * Formats given value for LIKE statement
	 *
	 * @param string $value
	 * @param string $replacement
	 * @return string
	 */
	public static function formatLikeStatementWildcards($value, $replacement='%')
	{
		// Escape wildcard character used in PDO
		$value=str_replace($replacement, '\\' . $replacement, $value);

		// Replace asterisks
		$value=\Nette\Utils\Strings::replace($value, '~(?!\\\\)(.?)\\*~', '\\1'.$replacement);

		// Replace escaped asterisks
		return str_replace('\\*', '*', $value);
	}
}
