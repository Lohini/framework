<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Application;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Application\Request;

/**
 * @todo Secure user sessions on identity id? (one user should not see flashes of other)
 */
class RequestManager
extends \Nette\Object
{
	const SESSION_SECTION='Nette.Application/requests';

	/** @var Application */
	private $application;
	/** @var \Nette\Http\SessionSection */
	private $session;


	/**
	 * @param Application $application
	 * @param \Nette\Http\Session $session
	 */
	public function __construct(Application $application, \Nette\Http\Session $session)
	{
		$this->application=$application;
		$this->session=$session->getSection(self::SESSION_SECTION);
	}

	/**
	 * Stores current request to session.
	 *
	 * @param mixed $expiration
	 * @return string
	 */
	public function storeCurrentRequest($expiration='+ 10 minutes')
	{
		return $this->storeRequest(end($this->application->getRequests()), $expiration);
	}

	/**
	 * Stores current request to session.
	 *
	 * @param mixed $expiration
	 * @return string
	 * @throws \Nette\InvalidStateException
	 */
	public function storePreviousRequest($expiration='+ 10 minutes')
	{
		if (count($this->application->getRequests())<2) {
			throw new \Nette\InvalidStateException('Only one request was server during application life cycle');
			}

		return $this->storeRequest(current(array_slice($this->application->getRequests(), -2, 1)), $expiration);
	}

	/**
	 * Stores request to session.
	 *
	 * @param Request $request
	 * @param mixed $expiration
	 * @return string
	 */
	public function storeRequest(Request $request, $expiration='+ 10 minutes')
	{
		do {
			$key=\Nette\Utils\Strings::random(5);
			}
		while (isset($this->session[$key]));

		$this->session[$key]=$request;
		$this->session->setExpiration($expiration, $key);
		return $key;
	}

	/**
	 * Restores current request to session.
	 *
	 * @param string $key
	 * @param string $backlinkKeyName
	 */
	public function restoreRequest($key, $backlinkKeyName='backlink')
	{
		$presenter=$this->application->getPresenter();

		if (isset($this->session[$key])) {
			$request=clone $this->session[$key];
			unset($this->session[$key]);
			$request->setFlag(Request::RESTORED, TRUE);

			$params=$request->params;
			if (is_string($backlinkKeyName)) {
				unset($params[$backlinkKeyName]);
				}

			if ($presenter instanceof \Nette\Application\UI\Presenter && $presenter->hasFlashSession()) {
				$params[$presenter::FLASH_KEY]=$presenter->getParam($presenter::FLASH_KEY);
				}

			$request->params=$params;
			$presenter->sendResponse(new \Nette\Application\Responses\ForwardResponse($request));
			}
	}
}
