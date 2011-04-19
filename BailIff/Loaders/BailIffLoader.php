<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Loaders;

use Nette\Loaders\AutoLoader,
	Nette\Utils\LimitedScope;

/**
 * BailIff auto loader is responsible for loading BailIff classes and interfaces.
 *
 * @author Lopo <lopo@losys.eu>
 */
class BailIffLoader
extends AutoLoader
{
	/** @var BailIffLoader */
	private static $instance;
	/** @var array */
	public $list=array(
		'bailiff\application\presenterfactory' => '/Application/PresenterFactory.php',
		'bailiff\components\gravatar' => '/Components/Gravatar.php',
		'bailiff\core' => '/common/Core.php',
		'bailiff\database\connection' => '/Database/Connection.php',
		'bailiff\di\configurator' => '/DI/Configurator.php',
		'bailiff\environment' => '/common/Environment.php',
		'bailiff\forms\cbox3s' => '/Forms/Controls/CBox3S.php',
		'bailiff\forms\datepicker' => '/Forms/Controls/DatePicker.php',
		'bailiff\forms\pswdinput' => '/Forms/Controls/PswdInput.php',
		'bailiff\loaders\bailiffloader' => '/Loaders/BailIffLoader.php',
		'bailiff\presenters\basepresenter' => '/Presenters/BasePresenter.php',
		'bailiff\presenters\webloaderpresenter' => '/Presenters/WebLoaderPresenter.php',
		'bailiff\templating\templatehelpers' => '/Templating/TemplateHelpers.php',
		'bailiff\utils\browser\browscap' => '/Utils/Browser/Browscap.php',
		'bailiff\utils\browser\browser' => '/Utils/Browser/Browser.php',
		'bailiff\utils\network' => '/Utils/Network.php',
		'bailiff\utils\translator\gettext' => '/Utils/Translator/Gettext.php',
		'bailiff\utils\translator\ieditable' => '/Utils/Translator/IEditable.php',
		'bailiff\utils\translator\panel' => '/Utils/Translator/Panel.php',
		'bailiff\utils\translator\pluralforms' => '/Utils/Translator/PluralForms.php',
		'bailiff\utils\webloader\cssloader' => '/Utils/WebLoader/CssLoader.php',
		'bailiff\utils\webloader\filters\cssurlsfilter' => '/Utils/WebLoader/Filters/CssUrlsFilter.php',
		'bailiff\utils\webloader\filters\prefilefilter' => '/Utils/WebLoader/Filters/PreFileFilter.php',
		'bailiff\utils\webloader\filters\prefile\ccssfilter' => '/Utils/WebLoader/Filters/PreFile/CCssFilter.php',
		'bailiff\utils\webloader\filters\prefile\lessfilter' => '/Utils/WebLoader/Filters/PreFile/LessFilter.php',
		'bailiff\utils\webloader\filters\prefile\xcssfilter' => '/Utils/WebLoader/Filters/PreFile/XCssFilter.php',
		'bailiff\utils\webloader\jsloader' => '/Utils/WebLoader/JssLoader.php',
		'bailiff\utils\webloader\webloader' => '/Utils/WebLoader/WebLoader.php',
		'bailiff\utils\webloader\webloadercachestorage' => '/Utils/WebLoader/WebLoaderCacheStorage.php'
		);


	/**
	 * Returns singleton instance with lazy instantiation
	 * @return BailIffLoader
	 */
	public static function getInstance()
	{
		if (self::$instance===NULL) {
			self::$instance=new self;
			}
		return self::$instance;
	}

	/**
	 * Handles autoloading of classes or interfaces
	 * @param string $type
	 * @see AutoLoader::tryLoad()
	 */
	public function tryLoad($type)
	{
		$type=ltrim(strtolower($type), '\\');
		if (isset($this->list[$type])) {
			LimitedScope::load(BAILIFF_DIR.$this->list[$type]);
			self::$count++;
			}
	}
}
