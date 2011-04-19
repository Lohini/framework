<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Utils\Translator;

use Nette\Diagnostics\IPanel,
	Nette\Environment as NEnvironment,
	Nette\Diagnostics\Debugger,
	BailIff\Environment;

class Panel
implements IPanel
{
	const XHR_HEADER='X-Translation-Client';
	const SESSION_NAMESPACE='BailIffTranslator-Panel';
	const LANGUAGE_KEY='X-BailIffTranslator-Lang';
	const FILE_KEY='X-BailIffTranslator-File';
	/* Layout constants */
	const LAYOUT_HORIZONTAL=1;
	const LAYOUT_VERTICAL=2;

	/** @var int TranslationPanel layout */
	protected $layout=self::LAYOUT_VERTICAL;
	/** @var int Height of the editor */
	protected $height=410;


	/**
	 * Constructor
	 * @param int $layout
	 * @param int $height
	 * @throws InvalidArgumentException
	 */
	public function __construct($layout=NULL, $height=NULL)
	{
		if ($height!==NULL) {
			if (!is_numeric($height)) {
				throw new \InvalidArgumentException('Panel height has to be a numeric value.');
				}
			$this->height=$height;
			}
		if ($layout!==NULL) {
			$this->layout=$layout;
			if ($height===NULL) {
				$this->height=500;
				}
			}
		$this->processRequest();
	}

	/**
	 * Returns panel ID.
	 * @return string
	 */
	public function getId()
	{
		return 'translation-panel';
	}

	/**
	 * Returns the code for the panel tab.
	 * @return string
	 */
	public function getTab()
	{
		ob_start();
		require __DIR__.'/tab.latte';
		return ob_get_clean();
	}

	/**
	 * Returns the code for the panel itself.
	 * @return string
	 */
	public function getPanel()
	{
		$translator=NEnvironment::getService('Nette\Localization\ITranslator');
		$files=array_keys($translator->getFiles());
		$strings=$translator->getStrings();

		$requests=Environment::getApplication()->requests;
		$presenterName=$requests[count($requests)-1]->presenterName;
		$module=strtolower(str_replace(':', '.', ltrim(substr($presenterName, 0, -(strlen(strrchr($presenterName, ':')))), ':')));
		$activeFile= (in_array($module, $files))? $module : $files[0];

		if (NEnvironment::getSession()->isStarted()) {
			$session=NEnvironment::getSession(self::SESSION_NAMESPACE);
			$untranslatedStack= isset($session['stack'])? $session['stack'] : array();
			foreach ($strings as $string => $data) {
				if (!$data) {
					$untranslatedStack[$string]=FALSE;
					}
				}
			$session['stack']=$untranslatedStack;

			foreach ($untranslatedStack as $string => $value) {
				if (!isset($strings[$string])) {
					$strings[$string]=FALSE;
					}
				}
			}

		ob_start();
		require __DIR__.'/panel.latte';
		return ob_get_clean();
	}

	/**
	 * Handles an incoming request and saves the data if necessary.
	 */
	private function processRequest()
	{
		// Try starting the session
		try {
			$session=NEnvironment::getSession(self::SESSION_NAMESPACE);
			}
		catch (\InvalidStateException $e) {
			$session=FALSE;
			}
		$request=NEnvironment::getHttpRequest();
		if ($request->isPost() && $request->isAjax() && $request->getHeader(self::XHR_HEADER)) {
			$data=json_decode(file_get_contents('php://input'));
			$translator=NEnvironment::getService('Nette\Localization\ITranslator');
			if ($data) {
				if ($session) {
					$stack= isset($session['stack'])? $session['stack'] : array();
					}

				$translator->lang=$data->{self::LANGUAGE_KEY};
				$file=$data->{self::FILE_KEY};
				unset($data->{self::LANGUAGE_KEY}, $data->{self::FILE_KEY});
				foreach ($data as $string => $value) {
					$translator->setTranslation($string, $value, $file);
					if ($session && isset($stack[$string])) {
						unset($stack[$string]);
						}
					}
				$translator->save($file);

				if ($session) {
					$session['stack']=$stack;
					}
				}
			exit;
		}
	}

	/**
	 * Returns an odrdinal number suffix.
	 * @param string $count
	 * @return string
	 */
	protected function ordinalSuffix($count)
	{
		switch (substr($count, -1)) {
			case '1':
				return 'st';
			case '2':
				return 'nd';
			case '3':
				return 'rd';
			default:
				return 'th';
			}
	}

	/**
	 * Registers this panel
	 * @param IEditable $translator
	 * @param int $layout
	 * @param int $height
	 */
	public static function register(IEditable $translator=NULL, $layout=NULL, $height=NULL)
	{
		Debugger::addPanel(new static($layout, $height));
	}
}
