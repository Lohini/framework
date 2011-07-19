<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Loaders;

/**
 * Lohini auto loader is responsible for loading Lohini classes and interfaces.
 *
 * @author Lopo <lopo@lohini.net>
 */
class LohiniLoader
extends \Nette\Loaders\AutoLoader
{
	/** @var LohiniLoader */
	private static $instance;
	/** @var array */
	public $list=array(
		'lohini\application\application' => '/Application/Application.php',
		'lohini\application\invalidpresenterexception' => '/Application/InvalidPresenterException.php',
		'lohini\application\presenterfactory' => '/Application/PresenterFactory.php',
		'lohini\application\ui\control' => '/Application/UI/Control.php',
		'lohini\application\ui\form' => '/Application/UI/Form.php',
		'lohini\application\ui\presenter' => '/Application/UI/Presenter.php',
		'lohini\components\datagrid\datagrid' => '/Components/DataGrid/DataGrid.php',
		'lohini\components\gravatar' => '/Components/Gravatar.php',
		'lohini\configurator' => '/common/Configurator.php',
		'lohini\core' => '/common/Core.php',
		'lohini\database\connection' => '/Database/Connection.php',
		'lohini\database\doctrine\mapping\tableprefix' => '/Database/Doctrine/Mapping/TablePrefix.php',
		'lohini\database\doctrine\orm\diagnostics\panel' => '/Database/Doctrine/ORM/Diagnostics/Panel.php',
		'lohini\database\models\services\identities' => '/Database/Models/Services/Identities.php',
		'lohini\diagnostics\panels\callback' => '/Diagnostics/Panels/Callback.php',
		'lohini\diagnostics\panels\user' => '/Diagnostics/Panels/User.php',
		'lohini\di\container' => '/DI/Container.php',
		'lohini\di\containerhelper' => '/DI/ContainerHelper.php',
		'lohini\environment' => '/common/Environment.php',
		'lohini\forms\controls\cbox3s' => '/Forms/Controls/CBox3S.php',
		'lohini\forms\controls\datepicker' => '/Forms/Controls/DatePicker.php',
		'lohini\forms\controls\pswdinput' => '/Forms/Controls/PswdInput.php',
		'lohini\forms\controls\resetbutton' => '/Forms/Controls/ResetButton.php',
		'lohini\forms\rendering\formrenderer' => '/Forms/Rendering/FormRenderer.php',
		'lohini\freezableobject' => '/common/FreezableObject.php',
		'lohini\latte\macros\lohinimacros' => '/Latte/Macros/LohiniMacros.php',
		'lohini\loaders\lohiniloader' => '/Loaders/LohiniLoader.php',
		'lohini\loaders\doctrineloader' => '/Loaders/DoctrineLoader.php',
		'lohini\loaders\symfonyloader' => '/Loaders/SymfonyLoader.php',
		'lohini\localization\dictionary' => '/Localization/Dictionary.php',
		'lohini\localization\extractor' => '/Localization/Extractor.php',
		'lohini\localization\filters\latte' => '/Localization/Filters/Latte.php',
		'lohini\localization\filters\lattemacros' => '/Localization/Filters/LatteMacros.php',
		'lohini\localization\filters\nella' => '/Localization/Filters/Nella.php',
		'lohini\localization\ifilter' => '/Localization/IFilter.php',
		'lohini\localization\istorage' => '/Localization/IStorage.php',
		'lohini\localization\itranslator' => '/Localization/ITranslator.php',
		'lohini\localization\languageentity' => '/Localization/LanguageEntity.php',
		'lohini\localization\languages' => '/Localization/Languages.php',
		'lohini\localization\panel' => '/Localization/Panel.php',
		'lohini\localization\pluralforms' => '/Localization/PluralForms.php',
		'lohini\localization\storages\gettext' => '/Localization/Storages/Gettext.php',
		'lohini\localization\translator' => '/Localization/Translator.php',
		'lohini\presenters\basepresenter' => '/Presenters/BasePresenter.php',
		'lohini\presenters\errorpresenter' => '/Presenters/ErrorPresenter.php',
		'lohini\presenters\webloaderpresenter' => '/Presenters/WebLoaderPresenter.php',
		'lohini\templating\helpers' => '/Templating/Helpers.php',
		'lohini\templating\itemplatefactory' => '/Templating/ITemplateFactory.php',
		'lohini\templating\templatefactory' => '/Templating/TemplateFactory.php',
		'lohini\types\password' => '/Types/Password.php',
		'lohini\utils\browser\browscap' => '/Utils/Browser/Browscap.php',
		'lohini\utils\browser\browscapexception' => '/Utils/Browser/Browscap.php',
		'lohini\utils\browser\browser' => '/Utils/Browser/Browser.php',
		'lohini\utils\network' => '/Utils/Network.php',
		'lohini\webloader\cssloader' => '/Utils/WebLoader/CssLoader.php',
		'lohini\webloader\filters\cssurlsfilter' => '/Utils/WebLoader/Filters/CssUrlsFilter.php',
		'lohini\webloader\filters\prefilefilter' => '/Utils/WebLoader/Filters/PreFileFilter.php',
		'lohini\webloader\filters\ccssfilter' => '/Utils/WebLoader/Filters/PreFile/CCssFilter.php',
		'lohini\webloader\filters\lessfilter' => '/Utils/WebLoader/Filters/PreFile/LessFilter.php',
		'lohini\webloader\filters\sassfilter' => '/Utils/WebLoader/Filters/PreFile/SassFilter.php',
		'lohini\webloader\filters\sass\boolean' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/literals/Boolean.php',
		'lohini\webloader\filters\sass\booleanexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/literals/LiteralExceptions.php',
		'lohini\webloader\filters\sass\colour' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/literals/Colour.php',
		'lohini\webloader\filters\sass\colourexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/literals/LiteralExceptions.php',
		'lohini\webloader\filters\sass\commentnode' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/CommentNode.php',
		'lohini\webloader\filters\sass\commentnodeexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/NodeExceptions.php',
		'lohini\webloader\filters\sass\compactrenderer' => '/Utils/WebLoader/Filters/PreFile/SassFilter/renderers/CompactRenderer.php',
		'lohini\webloader\filters\sass\compressedrenderer' => '/Utils/WebLoader/Filters/PreFile/SassFilter/renderers/CompressedRenderer.php',
		'lohini\webloader\filters\sass\context' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/Context.php',
		'lohini\webloader\filters\sass\contextexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/NodeExceptions.php',
		'lohini\webloader\filters\sass\debugnode' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/DebugNode.php',
		'lohini\webloader\filters\sass\debugnodeexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/NodeExceptions.php',
		'lohini\webloader\filters\sass\directivenode' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/DirectiveNode.php',
		'lohini\webloader\filters\sass\directivenodeexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/NodeExceptions.php',
		'lohini\webloader\filters\sass\elsenode' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/ElseNode.php',
		'lohini\webloader\filters\sass\exception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/Exception.php',
		'lohini\webloader\filters\sass\expandedrenderer' => '/Utils/WebLoader/Filters/PreFile/SassFilter/renderers/ExpandedRenderer.php',
		'lohini\webloader\filters\sass\extendnode' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/ExtendNode.php',
		'lohini\webloader\filters\sass\extendnodenodeexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/NodeExceptions.php',
		'lohini\webloader\filters\sass\fornode' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/ForNode.php',
		'lohini\webloader\filters\sass\fornodeexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/NodeExceptions.php',
		'lohini\webloader\filters\sass\ifnode' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/IfNode.php',
		'lohini\webloader\filters\sass\ifnodeexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/NodeExceptions.php',
		'lohini\webloader\filters\sass\importnode' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/ImportNode.php',
		'lohini\webloader\filters\sass\importnodeexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/NodeExceptions.php',
		'lohini\webloader\filters\sass\literal' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/literals/Literal.php',
		'lohini\webloader\filters\sass\literalexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/literals/LiteralExceptions.php',
		'lohini\webloader\filters\sass\mixindefinitionnode' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/MixinDefinitionNode.php',
		'lohini\webloader\filters\sass\mixindefinitionnodeexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/NodeExceptions.php',
		'lohini\webloader\filters\sass\mixinnode' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/MixinNode.php',
		'lohini\webloader\filters\sass\mixinnodeexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/NodeExceptions.php',
		'lohini\webloader\filters\sass\nestedrenderer' => '/Utils/WebLoader/Filters/PreFile/SassFilter/renderers/NestedRenderer.php',
		'lohini\webloader\filters\sass\node' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/Node.php',
		'lohini\webloader\filters\sass\nodeexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/NodeExceptions.php',
		'lohini\webloader\filters\sass\number' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/literals/Number.php',
		'lohini\webloader\filters\sass\numberexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/literals/LiteralExceptions.php',
		'lohini\webloader\filters\sass\propertynode' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/PropertyNode.php',
		'lohini\webloader\filters\sass\propertynodeexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/NodeExceptions.php',
		'lohini\webloader\filters\sass\renderer' => '/Utils/WebLoader/Filters/PreFile/SassFilter/renderers/Renderer.php',
		'lohini\webloader\filters\sass\rootnode' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/RootNode.php',
		'lohini\webloader\filters\sass\rulenode' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/RuleNode.php',
		'lohini\webloader\filters\sass\rulenodeexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/NodeExceptions.php',
		'lohini\webloader\filters\sass\scriptfunction' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/ScriptFunction.php',
		'lohini\webloader\filters\sass\scriptfunctionexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/ScriptParserExceptions.php',
		'lohini\webloader\filters\sass\scriptfunctions' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/ScriptFunctions.php',
		'lohini\webloader\filters\sass\scriptlexer' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/ScriptLexer.php',
		'lohini\webloader\filters\sass\scriptlexerexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/ScriptParserExceptions.php',
		'lohini\webloader\filters\sass\scriptoperation' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/ScriptOperation.php',
		'lohini\webloader\filters\sass\scriptoperationexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/ScriptParserExceptions.php',
		'lohini\webloader\filters\sass\scriptparser' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/ScriptParser.php',
		'lohini\webloader\filters\sass\scriptparserexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/ScriptParserExceptions.php',
		'lohini\webloader\filters\sass\scriptvariable' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/ScriptVariable.php',
		'lohini\webloader\filters\sass\string' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/literals/String.php',
		'lohini\webloader\filters\sass\stringexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/script/literals/LiteralExceptions.php',
		'lohini\webloader\filters\sass\variablenode' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/VariableNode.php',
		'lohini\webloader\filters\sass\variablenodeexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/NodeExceptions.php',
		'lohini\webloader\filters\sass\whilenode' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/WhileNode.php',
		'lohini\webloader\filters\sass\whilenodeexception' => '/Utils/WebLoader/Filters/PreFile/SassFilter/tree/NodeExceptions.php',
		'lohini\webloader\filters\xcssfilter' => '/Utils/WebLoader/Filters/PreFile/XCssFilter.php',
		'lohini\webloader\jsloader' => '/Utils/WebLoader/JsLoader.php',
		'lohini\webloader\webloader' => '/Utils/WebLoader/WebLoader.php',
		'lohini\webloader\webloadercachestorage' => '/Utils/WebLoader/WebLoaderCacheStorage.php'
		);


	/**
	 * Returns singleton instance with lazy instantiation
	 * @return LohiniLoader
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
			\Nette\Utils\LimitedScope::load(LOHINI_DIR.$this->list[$type]);
			self::$count++;
			}
	}
}
