<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Browser\History;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Extension\Curl;

/**
 */
class EagerHistory
extends \Nette\Object
implements \Countable
{
	/** @var \SplObjectStorage */
	protected $history;
	/** @var object */
	protected $lastPage;
	/** @var int */
	protected $totalTime=0;


	/**
	 */
	public function __construct()
	{
		$this->history=new \SplObjectStorage;
	}

	/**
	 */
	public function clean()
	{
		$this->history=new \SplObjectStorage;
	}

	/**
	 * @return \SplObjectStorage|\Lohini\Extension\Browser\WebPage[]
	 */
	public function getPages()
	{
		return $this->history;
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->history);
	}

	/**
	 * @return int
	 */
	public function getRequestsTotalTime()
	{
		return $this->totalTime;
	}

	/**
	 * @param \Lohini\Extension\Browser\WebPage|\stdClass $content
	 * @param Curl\Request|NULL $request
	 * @param Curl\Response|NULL $response
	 */
	public function push($content, Curl\Request $request=NULL, Curl\Response $response=NULL)
	{
		$this->history[$content]=array(
			$request? clone $request : NULL,
			$response? clone $response : NULL,
			);
		$this->lastPage= $content instanceof \Lohini\Extension\Browser\WebPage ? $content : NULL;

		if ($response) {
			$this->totalTime+=$response->info['total_time'];
			}
	}

	/**
	 * @return \Lohini\Extension\Browser\WebPage|NULL
	 */
	public function getLast()
	{
		return $this->lastPage;
	}

	/**
	 * @return array
	 */
	public function __sleep()
	{
		return array('history');
	}
}
