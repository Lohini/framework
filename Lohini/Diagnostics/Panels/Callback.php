<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Diagnostics\Panels;
/**
* This file is part of the Nella Framework.
*
* Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
*
* This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
* @author	Patrik Votoček
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Diagnostics\Debugger,
	Nette\Caching\Cache;

/**
 * Callback panel for nette debug bar
 */
class Callback
extends \Nette\Object
implements \Nette\Diagnostics\IBarPanel
{
	const XHR_HEADER='X-Lohini-Callback-Panel';
	/** @var \Nette\DI\IContainer */
	private $container;
	/** @var array */
	private $callbacks=array();
	/** @var bool */
	private static $registered=FALSE;

	/**
	 * @param \Nette\DI\IContainer
	 */
	public function __construct(\Nette\DI\IContainer $container)
	{
		if (static::$registered) {
			throw new \Nette\InvalidStateException('Callback panel is already registered');
			}
		$this->container=$container;
		$this->init();
		static::$registered=TRUE;
		Debugger::$bar->addPanel($this);
	}

	protected function init()
	{
		$httpRequest=$this->container->getService('httpRequest');
		if ($httpRequest->getHeader(static::XHR_HEADER)) {
			$data=(array)json_decode(file_get_contents('php://input'), TRUE);
			foreach ($data as $k => $v) {
				if (isset($this->callbacks[$k]) && isset($this->callbacks[$k]['callback']) && $v===TRUE) {
					callback($this->callbacks[$k]['callback'])->invoke();
					}
				}
			die(json_encode(array('status'=>'OK')));
			}

		$cacheStorage=$this->container->getService('cacheStorage');
		$this->addCallback('--cache', 'Invalidate cache', function() use($cacheStorage) {
			$cacheStorage->clean(array(Cache::ALL => TRUE));
			});
		$robotLoader=$this->container->getService('robotLoader');
		$this->addCallback('--robotloader', 'Rebuild robotloader cache', function() use($robotLoader) {
			$robotLoader->rebuild();
			});/*
		$webLoader=$this->container->getService('robotLoader');
		$this->addCallback('--webloader', 'Rebuild WebLoader cache', function() use($webLoader) {
			$webLoader->clean();
			});*/
	}

	/**
	 * @param string
	 * @return Callback
	*/
	public function removeCallback($id)
	{
		unset($this->callbacks[$id]);
		return $this;
	}

	/**
	 * @param string
	 * @param string
	 * @param array|\Nette\Callback|\Closure
	 * @return Callback
	 */
	public function addCallback($id, $name, $callback)
	{
		$this->callbacks[$id]=array(
			'name' => $name,
			'callback' => $callback,
			);
		return $this;
	}

	/**
	 * Renders HTML code for custom tab
	 * @return string
	 * @see \Nette\Diagnostics\IBarPanel::getTab()
	 */
	public function getTab()
	{
		return '<span title="Callbacks">
				<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAK8AAACvABQqw0mAAAABh0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzT7MfTgAAAY9JREFUOI2lkj1rVUEQhp93d49XjYiCUUFtgiBpFLyWFhKxEAsbGy0ErQQrG/EHCII/QMTGSrQ3hY1FijS5lQp2guBHCiFRSaLnnN0di3Pu9Rpy0IsDCwsz8+w776zMjP+J0JV48nrufMwrc2AUbt/CleMv5ycClHH1UZWWD4MRva4CByYDpHqjSgKEETcmHiHmItW5STuF/FfAg8HZvghHDDMpkKzYXScPgFcx9XBw4WImApITn26cejEAkJlxf7F/MOYfy8K3OJGtJlscKsCpAJqNGRknd+jO6TefA8B6WU1lMrBZ6fiE1R8Zs7hzVJHSjvJnNMb/hMSmht93IYIP5Qhw99zSx1vP+5eSxZmhzpzttmHTbcOKk+413Sav4v3J6ZsfRh5sFdefnnhr2Gz75rvHl18d3aquc43f1/BjaN9V1wn4tq6eta4LtnUCQuPWHmAv0AOKDNXstZln2/f3zgCUX8oFJx1zDagGSmA1mn2VmREk36pxw5NgzVqDhOTFLhjtOgMxmqVOE/81fgFilqPyaom5BAAAAABJRU5ErkJggg==">
				callback
				</span>';
	}

	/**
	 * Renders HTML code for custom panel
	 * @return string
	 * @see \Nette\Diagnostics\IBarPanel::getPanel()
	 */
	public function getPanel()
	{
		$callbacks=$this->callbacks;
		$absoluteUrl=$this->container->getService('httpRequest')->url->absoluteUrl;
		ob_start();
		require_once __DIR__.'/callback.latte';
		return ob_get_clean();
	}
}
