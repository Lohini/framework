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
		'bailiff\utils\webloader\filters\sassfilter' => '/Utils/WebLoader/Filters/SassFilter.php',
		'bailiff\utils\webloader\filters\sass\boolean' => '/Utils/WebLoader/Filters/SassFilter/script/literals/Boolean.php',
		'bailiff\utils\webloader\filters\sass\booleanexception' => '/Utils/WebLoader/Filters/SassFilter/script/literals/LiteralExceptions.php',
		'bailiff\utils\webloader\filters\sass\colour' => '/Utils/WebLoader/Filters/SassFilter/script/literals/Colour.php',
		'bailiff\utils\webloader\filters\sass\colourexception' => '/Utils/WebLoader/Filters/SassFilter/script/literals/LiteralExceptions.php',
		'bailiff\utils\webloader\filters\sass\commentnode' => '/Utils/WebLoader/Filters/SassFilter/tree/CommentNode.php',
		'bailiff\utils\webloader\filters\sass\commentnodeexception' => '/Utils/WebLoader/Filters/SassFilter/tree/NodeExceptions.php',
		'bailiff\utils\webloader\filters\sass\compactrenderer' => '/Utils/WebLoader/Filters/SassFilter/renderers/CompactRenderer.php',
		'bailiff\utils\webloader\filters\sass\compressedrenderer' => '/Utils/WebLoader/Filters/SassFilter/renderers/CompressedRenderer.php',
		'bailiff\utils\webloader\filters\sass\context' => '/Utils/WebLoader/Filters/SassFilter/tree/Context.php',
		'bailiff\utils\webloader\filters\sass\contextexception' => '/Utils/WebLoader/Filters/SassFilter/tree/NodeExceptions.php',
		'bailiff\utils\webloader\filters\sass\debugnode' => '/Utils/WebLoader/Filters/SassFilter/tree/DebugNode.php',
		'bailiff\utils\webloader\filters\sass\debugnodeexception' => '/Utils/WebLoader/Filters/SassFilter/tree/NodeExceptions.php',
		'bailiff\utils\webloader\filters\sass\directivenode' => '/Utils/WebLoader/Filters/SassFilter/tree/DirectiveNode.php',
		'bailiff\utils\webloader\filters\sass\directivenodeexception' => '/Utils/WebLoader/Filters/SassFilter/tree/NodeExceptions.php',
		'bailiff\utils\webloader\filters\sass\elsenode' => '/Utils/WebLoader/Filters/SassFilter/tree/ElseNode.php',
		'bailiff\utils\webloader\filters\sass\exception' => '/Utils/WebLoader/Filters/SassFilter/Exception.php',
		'bailiff\utils\webloader\filters\sass\expandedrenderer' => '/Utils/WebLoader/Filters/SassFilter/renderers/ExpandedRenderer.php',
		'bailiff\utils\webloader\filters\sass\extendnode' => '/Utils/WebLoader/Filters/SassFilter/tree/ExtendNode.php',
		'bailiff\utils\webloader\filters\sass\extendnodenodeexception' => '/Utils/WebLoader/Filters/SassFilter/tree/NodeExceptions.php',
		'bailiff\utils\webloader\filters\sass\fornode' => '/Utils/WebLoader/Filters/SassFilter/tree/ForNode.php',
		'bailiff\utils\webloader\filters\sass\fornodeexception' => '/Utils/WebLoader/Filters/SassFilter/tree/NodeExceptions.php',
		'bailiff\utils\webloader\filters\sass\ifnode' => '/Utils/WebLoader/Filters/SassFilter/tree/IfNode.php',
		'bailiff\utils\webloader\filters\sass\ifnodeexception' => '/Utils/WebLoader/Filters/SassFilter/tree/NodeExceptions.php',
		'bailiff\utils\webloader\filters\sass\importnode' => '/Utils/WebLoader/Filters/SassFilter/tree/ImportNode.php',
		'bailiff\utils\webloader\filters\sass\importnodeexception' => '/Utils/WebLoader/Filters/SassFilter/tree/NodeExceptions.php',
		'bailiff\utils\webloader\filters\sass\literal' => '/Utils/WebLoader/Filters/SassFilter/script/literals/Literal.php',
		'bailiff\utils\webloader\filters\sass\literalexception' => '/Utils/WebLoader/Filters/SassFilter/script/literals/LiteralExceptions.php',
		'bailiff\utils\webloader\filters\sass\mixindefinitionnode' => '/Utils/WebLoader/Filters/SassFilter/tree/MixinDefinitionNode.php',
		'bailiff\utils\webloader\filters\sass\mixindefinitionnodeexception' => '/Utils/WebLoader/Filters/SassFilter/tree/NodeExceptions.php',
		'bailiff\utils\webloader\filters\sass\mixinnode' => '/Utils/WebLoader/Filters/SassFilter/tree/MixinNode.php',
		'bailiff\utils\webloader\filters\sass\mixinnodeexception' => '/Utils/WebLoader/Filters/SassFilter/tree/NodeExceptions.php',
		'bailiff\utils\webloader\filters\sass\nestedrenderer' => '/Utils/WebLoader/Filters/SassFilter/renderers/NestedRenderer.php',
		'bailiff\utils\webloader\filters\sass\node' => '/Utils/WebLoader/Filters/SassFilter/tree/Node.php',
		'bailiff\utils\webloader\filters\sass\nodeexception' => '/Utils/WebLoader/Filters/SassFilter/tree/NodeExceptions.php',
		'bailiff\utils\webloader\filters\sass\number' => '/Utils/WebLoader/Filters/SassFilter/script/literals/Number.php',
		'bailiff\utils\webloader\filters\sass\numberexception' => '/Utils/WebLoader/Filters/SassFilter/script/literals/LiteralExceptions.php',
		'bailiff\utils\webloader\filters\sass\propertynode' => '/Utils/WebLoader/Filters/SassFilter/tree/PropertyNode.php',
		'bailiff\utils\webloader\filters\sass\propertynodeexception' => '/Utils/WebLoader/Filters/SassFilter/tree/NodeExceptions.php',
		'bailiff\utils\webloader\filters\sass\renderer' => '/Utils/WebLoader/Filters/SassFilter/renderers/Renderer.php',
		'bailiff\utils\webloader\filters\sass\rootnode' => '/Utils/WebLoader/Filters/SassFilter/tree/RootNode.php',
		'bailiff\utils\webloader\filters\sass\rulenode' => '/Utils/WebLoader/Filters/SassFilter/tree/RuleNode.php',
		'bailiff\utils\webloader\filters\sass\rulenodeexception' => '/Utils/WebLoader/Filters/SassFilter/tree/NodeExceptions.php',
		'bailiff\utils\webloader\filters\sass\scriptfunction' => '/Utils/WebLoader/Filters/SassFilter/script/ScriptFunction.php',
		'bailiff\utils\webloader\filters\sass\scriptfunctionexception' => '/Utils/WebLoader/Filters/SassFilter/script/ScriptParserExceptions.php',
		'bailiff\utils\webloader\filters\sass\scriptfunctions' => '/Utils/WebLoader/Filters/SassFilter/script/ScriptFunctions.php',
		'bailiff\utils\webloader\filters\sass\scriptlexer' => '/Utils/WebLoader/Filters/SassFilter/script/ScriptLexer.php',
		'bailiff\utils\webloader\filters\sass\scriptlexerexception' => '/Utils/WebLoader/Filters/SassFilter/script/ScriptParserExceptions.php',
		'bailiff\utils\webloader\filters\sass\scriptoperation' => '/Utils/WebLoader/Filters/SassFilter/script/ScriptOperation.php',
		'bailiff\utils\webloader\filters\sass\scriptoperationexception' => '/Utils/WebLoader/Filters/SassFilter/script/ScriptParserExceptions.php',
		'bailiff\utils\webloader\filters\sass\scriptparser' => '/Utils/WebLoader/Filters/SassFilter/script/ScriptParser.php',
		'bailiff\utils\webloader\filters\sass\scriptparserexception' => '/Utils/WebLoader/Filters/SassFilter/script/ScriptParserExceptions.php',
		'bailiff\utils\webloader\filters\sass\scriptvariable' => '/Utils/WebLoader/Filters/SassFilter/script/ScriptVariable.php',
		'bailiff\utils\webloader\filters\sass\string' => '/Utils/WebLoader/Filters/SassFilter/script/literals/String.php',
		'bailiff\utils\webloader\filters\sass\stringexception' => '/Utils/WebLoader/Filters/SassFilter/script/literals/LiteralExceptions.php',
		'bailiff\utils\webloader\filters\sass\variablenode' => '/Utils/WebLoader/Filters/SassFilter/tree/VariableNode.php',
		'bailiff\utils\webloader\filters\sass\variablenodeexception' => '/Utils/WebLoader/Filters/SassFilter/tree/NodeExceptions.php',
		'bailiff\utils\webloader\filters\sass\whilenode' => '/Utils/WebLoader/Filters/SassFilter/tree/WhileNode.php',
		'bailiff\utils\webloader\filters\sass\whilenodeexception' => '/Utils/WebLoader/Filters/SassFilter/tree/NodeExceptions.php',
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
