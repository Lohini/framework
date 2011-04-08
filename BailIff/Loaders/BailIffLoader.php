<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Loaders;

use Nette\Loaders\AutoLoader,
	Nette\Loaders\LimitedScope;

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
		'bailiff\core' => '/Core.php',
		'bailiff\configurator' => '/Environment/Configurator.php',
		'bailiff\environment' => '/Environment/Environment.php',
		'bailiff\application\presenterfactory' => '/Application/PresenterFactory.php',
		'bailiff\components\imagear' => '/Components/ImageAR.php',
		'bailiff\components\gravatar' => '/Components/Gravatar.php',
		'bailiff\database\connection' => '/Database/Connection.php',
		'bailiff\forms\cbox3s' => '/Forms/Controls/CBox3S.php',
		'bailiff\forms\datepicker' => '/Forms/Controls/DatePicker.php',
		'bailiff\forms\pswdinput' => '/Forms/Controls/PswdInput.php',
		'bailiff\loaders\bailiffloader' => '/Loaders/BailIffLoader.php',
		'bailiff\plugins\iplugin' => '/Plugins/IPlugin.php',
		'bailiff\plugins\ipluginmanager' => '/Plugins/IPluginManager.php',
		'bailiff\plugins\plugin' => '/Plugins/Plugin.php',
		'bailiff\plugins\pluginmanager' => '/Plugins/PluginManager.php',
		'bailiff\presenters\basepresenter' => '/Presenters/BasePresenter.php',
		'bailiff\presenters\errorpresenter' => '/Presenters/ErrorPresenter.php',
		'bailiff\presenters\securedpresenter' => '/Presenters/SecuredPresenter.php',
		'bailiff\presenters\webloaderpresenter' => '/Presenters/WebLoaderPresenter.php',
		'bailiff\security\authenticators\db' => '/Security/Authenticators/DB.php',
		'bailiff\security\authenticators\ldap' => '/Security/Authenticators/LDAP.php',
		'bailiff\security\authenticators\twitter' => '/Security/Authenticators/Twitter.php',
		'bailiff\security\authconfig' => '/Security/AuthConfig.php',
		'bailiff\security\authenticator' => '/Security/Authenticator.php',
		'bailiff\security\authmodel' => '/Security/AuthModel.php',
		'bailiff\security\authorizator' => '/Security/Authorizator.php',
		'bailiff\security\iauthenticator' => '/Security/IAuthenticator.php',
		'bailiff\templates\macros' => '/Templates/Macros.php',
		'bailiff\templates\templatehelpers' => '/Templates/TemplateHelpers.php',
		'bailiff\utils\browser\browscap' => '/Utils/Browser/Browscap.php',
		'bailiff\utils\browser\browser' => '/Utils/Browser/Browser.php',
		'bailiff\utils\translator\gettext' => '/Utils/Translator/Gettext.php',
		'bailiff\utils\translator\ieditable' => '/Utils/Translator/IEditable.php',
		'bailiff\utils\translator\panel' => '/Utils/Translator/Panel.php',
		'bailiff\utils\translator\pluralforms' => '/Utils/Translator/PluralForms.php',
		'bailiff\utils\webloader\filters\prefile\ccssfilter' => '/Utils/WebLoader/Filters/PreFile/CCssFilter.php',
		'bailiff\utils\webloader\filters\prefile\lessfilter' => '/Utils/WebLoader/Filters/PreFile/LessFilter.php',
		'bailiff\utils\webloader\filters\prefile\sassfilter' => '/Utils/WebLoader/Filters/PreFile/SassFilter.php',
		'bailiff\utils\webloader\filters\prefile\xcssfilter' => '/Utils/WebLoader/Filters/PreFile/XCssFilter.php',
		'bailiff\utils\webloader\filters\cssurlsfilter' => '/Utils/WebLoader/Filters/CssUrlsFilter.php',
		'bailiff\utils\webloader\filters\prefilefilter' => '/Utils/WebLoader/Filters/PreFileFilter.php',
		'bailiff\utils\webloader\filters\variablesfilter' => '/Utils/WebLoader/Filters/VariablesFilter.php',
		'bailiff\utils\webloader\cssloader' => '/Utils/WebLoader/CssLoader.php',
		'bailiff\utils\webloader\jsloader' => '/Utils/WebLoader/JssLoader.php',
		'bailiff\utils\webloader\webloader' => '/Utils/WebLoader/WebLoader.php',
		'bailiff\utils\webloader\webloadercachestorage' => '/Utils/WebLoader/WebLoaderCacheStorage.php',
		'bailiff\utils\network' => '/Utils/Network.php'
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
