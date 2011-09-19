<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Forms\Controls;

/**
 * @author Lopo <lopo@lohini.net>
 */
class Texyla
extends \Nette\Forms\Controls\TextArea
{
	/**
	 * {@inheritdoc}
	 */
	public function getControl()
	{
		return parent::getControl()
			->addClass('texyla');
	}
}
