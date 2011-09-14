<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Plugins;

/**
 * Secured presenter for plugins
 *
 * @author Lopo <lopo@lohini.net>
 *
 * @User loggedIn
 */
class SecuredPresenter
extends BasePresenter
{
	/**
	 * @User loggedIn
	 */
	protected function startup()
	{
		parent::startup();
		if (!$this->user->isLoggedIn()) {
			$this->flashMessage('Not logged in', 'warning');
			$this->redirect($this->loginLink);
			}
	}
}
