<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\Loaders;

/**
 * BailIff auto loader is responsible for loading BailIff classes and interfaces.
 *
 * @author Lopo <lopo@losys.eu>
 */
class BailIffLoader
extends \Nette\Loaders\AutoLoader
{
	/** @var BailIffLoader */
	private static $instance;
	/** @var array */
	public $list=array(
		'bailiff\application\application' => '/Application/Application.php',
		'bailiff\application\presenterfactory' => '/Application/PresenterFactory.php',
		'bailiff\application\ui\control' => '/Application/UI/Control.php',
		'bailiff\application\ui\form' => '/Application/UI/Form.php',
		'bailiff\application\ui\presenter' => '/Application/UI/Presenter.php',
		'bailiff\components\gravatar' => '/Components/Gravatar.php',
		'bailiff\configurator' => '/common/Configurator.php',
		'bailiff\core' => '/common/Core.php',
		'bailiff\database\connection' => '/Database/Connection.php',
		'bailiff\diagnostics\panels\callback' => '/Diagnostics/Panels/Callback.php',
		'bailiff\diagnostics\panels\user' => '/Diagnostics/Panels/User.php',
		'bailiff\di\container' => '/DI/Container.php',
		'bailiff\environment' => '/common/Environment.php',
		'bailiff\forms\controls\cbox3s' => '/Forms/Controls/CBox3S.php',
		'bailiff\forms\controls\datepicker' => '/Forms/Controls/DatePicker.php',
		'bailiff\forms\controls\pswdinput' => '/Forms/Controls/PswdInput.php',
		'bailiff\forms\controls\resetbutton' => '/Forms/Controls/ResetButton.php',
		'bailiff\forms\rendering\formrenderer' => '/Forms/Rendering/FormRenderer.php',
		'bailiff\loaders\bailiffloader' => '/Loaders/BailIffLoader.php',
		'bailiff\localization\ieditable' => '/Localization/IEditable.php',
		'bailiff\localization\languages' => '/Localization/Languages.php',
		'bailiff\localization\panel' => '/Localization/Panel.php',
		'bailiff\localization\pluralforms' => '/Localization/PluralForms.php',
		'bailiff\localization\translator' => '/Localization/Translator.php',
		'bailiff\presenters\basepresenter' => '/Presenters/BasePresenter.php',
		'bailiff\presenters\errorpresenter' => '/Presenters/ErrorPresenter.php',
		'bailiff\presenters\webloaderpresenter' => '/Presenters/WebLoaderPresenter.php',
		'bailiff\templating\helpers' => '/Templating/Helpers.php',
		'bailiff\templating\itemplatefactory' => '/Templating/ITemplateFactory.php',
		'bailiff\templating\templatefactory' => '/Templating/TemplateFactory.php',
		'bailiff\utils\browser\browscap' => '/Utils/Browser/Browscap.php',
		'bailiff\utils\browser\browscapexception' => '/Utils/Browser/Browscap.php',
		'bailiff\utils\browser\browser' => '/Utils/Browser/Browser.php',
		'bailiff\utils\network' => '/Utils/Network.php',
		'bailiff\utils\webloader\cssloader' => '/Utils/WebLoader/CssLoader.php',
		'bailiff\utils\webloader\filters\cssurlsfilter' => '/Utils/WebLoader/Filters/CssUrlsFilter.php',
		'bailiff\utils\webloader\filters\prefilefilter' => '/Utils/WebLoader/Filters/PreFileFilter.php',
		'bailiff\utils\webloader\filters\ccssfilter' => '/Utils/WebLoader/Filters/CCssFilter.php',
		'bailiff\utils\webloader\filters\lessfilter' => '/Utils/WebLoader/Filters/LessFilter.php',
		'bailiff\utils\webloader\filters\xcssfilter' => '/Utils/WebLoader/Filters/XCssFilter.php',
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
			self::$instance=new static;
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
			\Nette\Utils\LimitedScope::load(BAILIFF_DIR.$this->list[$type]);
			self::$count++;
			}
	}
}
