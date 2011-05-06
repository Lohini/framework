<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Diagnostics\Panels;

use Nette\Object,
	Nette\Diagnostics\IBarPanel,
	Nette\Environment as NEnvironment,
	Nette\Diagnostics\Debugger,
	Nette\Caching\Cache,
	Nette\InvalidStateException,
	BailIff\Environment;

/**
 * Callback panel for nette debug bar
 *
 * @author Patrik Votoček
 * @author Lopo <lopo@losys.eu> (BailIff port)
 */
class CallbackPanel
extends Object
implements IBarPanel
{
	/** @var array */
	private $items=array();
	/** @var bool */
	private static $registered=FALSE;

	/**
	 * @param array
	 */
	public function __construct(array $items=NULL)
	{
		$this->items=array(
			'--cache' => array(
				'callback' => callback(Environment::getApplication()->context->getService('Nette\Caching\ICacheStorage'), 'clean'),
				'name' => 'Invalidate cache',
				'args' => array(array(Cache::ALL => TRUE))
				),
			'--webloader' => array(
				'callback' => callback('\BailIff\WebLoader\WebLoader', 'clean'),
				'name' => 'Clean WebLoader cache',
				'args' => array()
				),
			'--robotloader' => array(
				'callback' => callback(NEnvironment::getService('Nette\Loaders\RobotLoader'), 'rebuild'),
				'name' => 'Rebuild RobotLoader Cache',
				'args' => array()
				),
			);

		if ($items) {
			$this->items=array_merge($this->items, $items);
			}

		$this->processRequest();
	}

	/**
	 * Returns panel ID
	 * @return string
	 * @see Nette\Diagnostics\IBarPanel::getId()
	 */
	public function getId()
	{
		return 'callback-panel';
	}

	/**
	 * Renders HTML code for custom tab
	 * @return string
	 * @see Nette\Diagnostics\IBarPanel::getTab()
	 */
	public function getTab()
	{
		return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAK8AAACvABQqw0mAAAABh0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzT7MfTgAAAY9JREFUOI2lkj1rVUEQhp93d49XjYiCUUFtgiBpFLyWFhKxEAsbGy0ErQQrG/EHCII/QMTGSrQ3hY1FijS5lQp2guBHCiFRSaLnnN0di3Pu9Rpy0IsDCwsz8+w776zMjP+J0JV48nrufMwrc2AUbt/CleMv5ycClHH1UZWWD4MRva4CByYDpHqjSgKEETcmHiHmItW5STuF/FfAg8HZvghHDDMpkKzYXScPgFcx9XBw4WImApITn26cejEAkJlxf7F/MOYfy8K3OJGtJlscKsCpAJqNGRknd+jO6TefA8B6WU1lMrBZ6fiE1R8Zs7hzVJHSjvJnNMb/hMSmht93IYIP5Qhw99zSx1vP+5eSxZmhzpzttmHTbcOKk+413Sav4v3J6ZsfRh5sFdefnnhr2Gz75rvHl18d3aquc43f1/BjaN9V1wn4tq6eta4LtnUCQuPWHmAv0AOKDNXstZln2/f3zgCUX8oFJx1zDagGSmA1mn2VmREk36pxw5NgzVqDhOTFLhjtOgMxmqVOE/81fgFilqPyaom5BAAAAABJRU5ErkJggg==">callback';
	}

	/**
	 * Renders HTML code for custom panel
	 * @return string
	 * @see Nette\Diagnostics\IBarPanel::getPanel()
	 */
	public function getPanel()
	{
		$items=$this->items;
		ob_start();
		require_once __DIR__.'/panel.latte';
		return ob_get_clean();
	}

	/**
	 * Handles an incomuing request and saves the data if necessary
	 */
	private function processRequest()
	{
		$request=Environment::getApplication()->getService('Nette\Web\IHttpRequest');
		if ($request->isPost() && $request->isAjax() && $request->getHeader('X-Callback-Panel')) {
			$data=json_decode(file_get_contents('php://input'), TRUE);
			if (count($data)>0) {
				foreach ($data as $key => $value) {
					if (isset($this->items[$key]) && isset($this->items[$key]['callback']) && $value===TRUE) {
						$this->items[$key]['callback']->invokeArgs($this->items[$key]['args']);
						}
					}
				}

			die(json_encode(array('status' => 'OK')));
			}
	}

	/**
	 * Register this panel
	 *
	 * @param array	items for add to pannel
	 */
	public static function register(array $items=NULL)
	{
		if (self::$registered) {
			throw new InvalidStateException('Callback panel is already registered');
			}
		Debugger::addPanel(new static($items));
		self::$registered=TRUE;
	}
}
