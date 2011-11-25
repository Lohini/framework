<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Components\DataGrid\Renderers;
/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Utils\Html,
	Lohini\Components\DataGrid\Columns\ActionColumn,
	Lohini\Components\DataGrid\Columns\Column;

/**
 * Converts a data grid into the HTML output
 */
class Conventional
extends \Nette\Object
implements IRenderer
{
	/** @var array of HTML tags */
	public $wrappers=array(
		'datagrid' => array(
			'container' => 'table class="datagrid ui-widget"',
			),
		'form' => array(
			'.class' => 'datagrid',
			),
		'error' => array(
			'container' => 'ul class="error ui-state-error"',
			'item' => 'li',
			),
		'row.header' => array(
			'container' => 'tr class=header',
			'cell' => array(
				'container' => 'th', // .checker, .action
				'.class' => 'ui-widget-header',
				'.active' => 'active ui-state-active',
				),
			),
		'row.filter' => array(
			'container' => 'tr class=filters',
			'cell' => array(
				'container' => 'td', // .action
				'.class' => 'ui-widget-header'
				),
			'control' => array(
				'.input' => 'text',
				'.select' => 'select',
				'.submit' => 'button ui-button',
				),
			),
		'row.content' => array(
			'container' => 'tr', // .even, .selected
			'.even' => 'even ui-state-highlight',
			'cell' => array(
				'container' => 'td', // .checker, .action
				'.class' => 'ui-widget-content'
				),
			),
		'row.footer' => array(
			'container' => 'tr class=footer',
			'cell' => array(
				'container' => 'td class="ui-widget-header"',
				),
			),
		'paginator' => array(
			'container' => 'span class=paginator',
			'button' => array(
				'first' => 'span class="paginator-first"',
				'prev' => 'span class="paginator-prev"',
				'next' => 'span class="paginator-next"',
				'last' => 'span class="paginator-last"',
				),
			'controls' => array(
				'container' => 'span class=paginator-controls',
				),
			),
		'operations' => array(
			'container' => 'span class=operations',
			),
		'info' => array(
			'container' => 'span class=grid-info',
			),
		);
	/** @var string */
	public $footerFormat='%operations% %paginator% %info%';
	/** @var string */
	public $paginatorFormat='%label% %input% of %count%';
	/** @var string */
	public $infoFormat='Items %from% - %to% of %count% | Display: %selectbox% | %reset%';
	/** @var string template file */
	public $file;
	/** @var \Lohini\Components\DataGrid\DataGrid */
	protected $dataGrid;
	/** @var array of function(\Nette\Utils\Html $row, \DibiRow $data) */
	public $onRowRender;
	/** @var array of function(\Nette\Utils\Html $cell, string $column, mixed $value) */
	public $onCellRender;
	/** @var array  of function(\Nette\Utils\Html $action, \DibiRow $data) */
	public $onActionRender;


	public function __construct()
	{
		$this->file=__DIR__.'/conventional.latte';
	}

	/**
	 * Provides complete datagrid rendering
	 * @param \Lohini\Components\DataGrid $dataGrid
	 * @param string $mode
	 * @return string
	 * @throws \Nette\InvalidArgumentException
	 */
	public function render(\Lohini\Components\DataGrid\DataGrid $dataGrid, $mode=NULL)
	{
		if (!$dataGrid->dataSource instanceof \Lohini\Database\DataSources\IDataSource) {
			throw new \Nette\InvalidStateException('Data source is not instance of IDataSource. '.gettype($this->dataSource).' given.');
			}

		if ($this->dataGrid!==$dataGrid) {
			$this->dataGrid=$dataGrid;
			}

		if ($mode!==NULL) {
			return call_user_func_array(array($this, 'render'.$mode), array());
			}

		$template=$this->dataGrid->getTemplate();
		$template->setFile($this->file);
		return $template->__toString(TRUE);
	}

	/**
	 * Renders datagrid form begin
	 * @return string
	 */
	public function renderBegin()
	{
		$form=$this->dataGrid->getForm(TRUE);
		foreach ($form->getControls() as $control) {
			$control->setOption('rendered', FALSE);
			}
		$form->getElementPrototype()->addClass($this->getValue('form .class'));
		return $form->getElementPrototype()->startTag();
	}

	/**
	 * Renders datagrid form end
	 * @return string
	 */
	public function renderEnd()
	{
		$form=$this->dataGrid->getForm(TRUE);
		$basePath=rtrim($form->getPresenter(FALSE)->getContext()->getService('httpRequest')->getUrl()->getBasePath(), '/');
		$tc=$form->getComponent('_token_', FALSE);
		$token= $tc ? (string)$tc->getControl() : '';
		return $token.$form->getElementPrototype()->endTag();
	}

	/**
	 * Renders validation errors
	 * @return string
	 */
	public function renderErrors()
	{
		$form=$this->dataGrid->getForm(TRUE);

		$errors=$form->getErrors();
		if (count($errors)) {
			$ul=$this->getWrapper('error container');
			$li=$this->getWrapper('error item');

			foreach ($errors as $error) {
				$item=clone $li;
				if ($error instanceof Html) {
					$item->add($error);
					}
				else {
					$item->setText($error);
					}
				$ul->add($item);
				}
			return "\n".$ul->render(0);
			}
	}

	/**
	 * Renders data grid body
	 * @return string
	 */
	public function renderBody()
	{
		$container=$this->getWrapper('datagrid container');

// headers
		$header=Html::el($container->getName()=='table'? 'thead' : NULL);
		$header->add($this->generateHeaderRow());

		if ($this->dataGrid->hasFilters()) {
			$header->add($this->generateFilterRow());
			}

// footer
		$footer=Html::el($container->getName()=='table'? 'tfoot' : NULL);
		$footer->add($this->generateFooterRow());

// body
		$body=Html::el($container->getName()=='table'? 'tbody' : NULL);

		if ($this->dataGrid->paginator->itemCount) {
			$iterator=new \Nette\Iterators\CachingIterator($this->dataGrid->getRows());
			foreach ($iterator as $data) {
				$row=$this->generateContentRow($data);
				$row->addClass($iterator->isEven()? $this->getValue('row.content .even') : NULL);
				$body->add($row);
				}
			}
		else {
			$size=count($this->dataGrid->getColumns());
			$row=$this->getWrapper('row.content container');
			$cell=$this->getWrapper('row.content cell container');
			$cell->colspan=$size;
			$cell->style='text-align:center';
			$cell->add(Html::el('div')->setText($this->dataGrid->translate('No data were found')));
			$row->add($cell);
			$body->add($row);
			}

		if ($container->getName()=='table') {
			$container->add($header);
			$container->add($footer);
			$container->add($body);
			}
		else {
			$container->add($header);
			$container->add($body);
			$container->add($footer);
			}

		return $container->render(0);
	}

	/**
	 * Renders data grid paginator
	 * @return string
	 */
	public function renderPaginator()
	{
		$paginator=$this->dataGrid->paginator;
		if ($paginator->pageCount<=1) {
			return '';
			}

		$container=$this->getWrapper('paginator container');
		$translator=$this->dataGrid->getTranslator();

		$a=Html::el('a');
		$a->addClass(\Lohini\Components\DataGrid\Action::$ajaxClass);

// to-first button
		$first=$this->getWrapper('paginator button first');
		$title=$this->dataGrid->translate('First');
		$link=clone $a->href($this->dataGrid->link('page', 1));
		if ($first instanceof Html) {
			if ($paginator->isFirst()) {
				$first->addClass('inactive');
				}
			else {
				$first=$link->add($first);
				}
			$first->title($title);
			}
		else {
			$first=$link->setText($title);
			}
		$container->add($first);

// previous button
		$prev=$this->getWrapper('paginator button prev');
		$title=$this->dataGrid->translate('Previous');
		$link=clone $a->href($this->dataGrid->link('page', $paginator->page-1));
		if ($prev instanceof Html) {
			if ($paginator->isFirst()) {
				$prev->addClass('inactive');
				}
			else {
				$prev=$link->add($prev);
				}
			$prev->title($title);
			}
		else {
			$prev=$link->setText($title);
			}
		$container->add($prev);

// page input
		$controls=$this->getWrapper('paginator controls container');
		$form=$this->dataGrid->getForm(TRUE);
		$format=$this->dataGrid->translate($this->paginatorFormat);
		$html=str_replace(
				array('%label%', '%input%', '%count%'),
				array($form['page']->label, $form['page']->control, $paginator->pageCount),
				$format
				);
		$controls->add(Html::el()->setHtml($html));
		$container->add($controls);

// next button
		$next=$this->getWrapper('paginator button next');
		$title=$this->dataGrid->translate('Next');
		$link=clone $a->href($this->dataGrid->link('page', $paginator->page+1));
		if ($next instanceof Html) {
			if ($paginator->isLast()) {
				$next->addClass('inactive');
				}
			else {
				$next=$link->add($next);
				}
			$next->title($title);
			}
		else {
			$next=$link->setText($title);
			}
		$container->add($next);

// to-last button
		$last=$this->getWrapper('paginator button last');
		$title=$this->dataGrid->translate('Last');
		$link=clone $a->href($this->dataGrid->link('page', $paginator->pageCount));
		if ($last instanceof Html) {
			if ($paginator->isLast()) {
				$last->addClass('inactive');
				}
			else {
				$last=$link->add($last);
				}
			$last->title($title);
			}
		else {
			$last=$link->setText($title);
			}
		$container->add($last);

// page change submit
		$control=$form['pageSubmit']->control;
		$control->title=$control->value;
		$control->addClass('ui-button');
		$container->add($control);

		unset($first, $prev, $next, $last, $button, $paginator, $link, $a, $form);
		return $container->render();
	}

	/**
	 * Renders data grid operation controls
	 * @return string
	 */
	public function renderOperations()
	{
		if (!$this->dataGrid->hasOperations()) {
			return '';
			}

		$container=$this->getWrapper('operations container');
		$form=$this->dataGrid->getForm(TRUE);
		$container->add($form['operations']->label);
		$container->add($form['operations']->control);
		$container->add($form['operationSubmit']->control->title($form['operationSubmit']->control->value));

		return $container->render();
	}

	/**
	 * Renders info about data grid
	 * @return string
	 */
	public function renderInfo()
	{
		$container=$this->getWrapper('info container');
		$paginator=$this->dataGrid->paginator;
		$form=$this->dataGrid->getForm(TRUE);

		$stateSubmit=$form['resetSubmit']->control;
		$stateSubmit->title($stateSubmit->value);

		$this->infoFormat=$this->dataGrid->translate($this->infoFormat);
		$html=str_replace(
				array(
					'%from%',
					'%to%',
					'%count%',
					'%selectbox%',
					'%reset%'
					),
				array(
					$paginator->itemCount!=0? $paginator->offset+1 : $paginator->offset,
					$paginator->offset+$paginator->length,
					$paginator->itemCount,
					$form['items']->control.$form['itemsSubmit']->control->title($form['itemsSubmit']->control->value),
					$this->dataGrid->rememberState? $stateSubmit : '',
					),
				$this->infoFormat
				);

		$container->setHtml(trim($html, ' | '));
		return $container->render();
	}

	/**
	 * Generates datagrid headrer
	 * @return Html
	 */
	protected function generateHeaderRow()
	{
		$row=$this->getWrapper('row.header container');

// checker
		if ($this->dataGrid->hasOperations()) {
			$cell=$this->getWrapper('row.header cell container');
			$cell->addClass('checker');

			if ($this->dataGrid->hasFilters()) {
				$cell->rowspan(2);
				}
			$row->add($cell);
		}

// headers
		foreach ($this->dataGrid->getColumns() as $column) {
			$value=$text=$column->caption;

			if ($column->isOrderable()) {
				$i=1;
				parse_str($this->dataGrid->order, $list);
				foreach ($list as $field => $dir) {
					$list[$field]=array($dir, $i++);
					}

				if (isset($list[$column->getName()])) {
					$a= $list[$column->getName()][0]==='a';
					$d= $list[$column->getName()][0]==='d';
					}
				else {
					$a= $d= FALSE;
					}

				if (count($list)>1 && isset($list[$column->getName()])) {
					$text.=Html::el('span')->setHtml($list[$column->getName()][1]);
					}

				$up= clone $down= Html::el('a')->addClass(Column::$ajaxClass);
				$up->addClass($a? 'active' : '')->href($column->getOrderLink('a'))
						->add(Html::el('span')
							->class('up ui-icon ui-icon-triangle-1-s')
							->style('float: right;')
							);
				$down->addClass($d? 'active' : '')->href($column->getOrderLink('d'))
						->add(Html::el('span')
							->class('down ui-icon ui-icon-triangle-1-n')
							->style('float: right;')
							);
				$positioner=Html::el('span')->class('positioner')->add($up)->add($down);
				$active= $a || $d;

				$value=(string)Html::el('a')->href($column->getOrderLink())
						->addClass(Column::$ajaxClass)->setHtml($text).$positioner;
				}
			else {
				$value=(string)Html::el('p')->setText($value);
				}

			$cell=$this->getWrapper('row.header cell container')->setHtml($value);
			$cell->attrs=$column->getHeaderPrototype()->attrs;
			$cell->addClass($this->getValue('row.header cell .class'));
			$cell->addClass(isset($active) && $active==TRUE ? $this->getValue('row.header cell .active') : NULL);
			if ($column instanceof ActionColumn) {
				$cell->addClass('actions');
				}
			$row->add($cell);
			}
		return $row;
	}

	/**
	 * Generates datagrid filter
	 * @return Html
	 */
	protected function generateFilterRow() {
		$row=$this->getWrapper('row.filter container');
		$form=$this->dataGrid->getForm(TRUE);

		$submitControl=$form['filterSubmit']->control;
		$submitControl->addClass($this->getValue('row.filter control .submit'));
		$submitControl->title=$submitControl->value;
		$submitControl->style='padding: .1em .1em;';

		foreach ($this->dataGrid->getColumns() as $column) {
			$cell=$this->getWrapper('row.filter cell container');

// TODO: set on filters too?
			$cell->attrs=$column->getCellPrototype()->attrs;
			$cell->addClass($this->getValue('row.filter cell .class'));

			if ($column instanceof ActionColumn) {
				$value=(string)$submitControl;
				$cell->addClass('actions');
				}
			else {
				if ($column->hasFilter()) {
					$filter=$column->getFilter();
					$class= $filter instanceof \Lohini\Components\DataGrid\Filters\SelectboxFilter
						? $this->getValue('row.filter control .select')
						: $this->getValue('row.filter control .input');
					$control=$filter->getFormControl()->control;
					$control->addClass($class);
					$value=(string)$control;
					}
				else {
					$value='';
					}
				}

			$cell->setHtml($value);
			$row->add($cell);
			}

		if (!$this->dataGrid->hasActions()) {
			$submitControl->addStyle('display: none');
			$row->add($submitControl);
			}

		return $row;
	}

	/**
	 * Generates datagrid row content.
	 * @param \Traversable|array $data
	 * @return Html
	 * @throws \Nette\InvalidArgumentException
	 */
	protected function generateContentRow($data)
	{
		$form=$this->dataGrid->getForm(TRUE);
		$row=$this->getWrapper('row.content container');

		if ($this->dataGrid->hasOperations() || $this->dataGrid->hasActions()) {
			$primary=$this->dataGrid->keyName;
			if (!isset($data[$primary])) {
				throw new \Nette\InvalidArgumentException("Invalid name of key for group operations or actions. Column '$primary' does not exist in data source.");
				}
			}

// checker
		if ($this->dataGrid->hasOperations()) {
			$value=$form['checker'][$data[$primary]]->getControl();
			$cell=$this->getWrapper('row.content cell container')->setHtml((string)$value);
			$cell->addClass('checker');
			$row->add($cell);
			}

// content
		foreach ($this->dataGrid->getColumns() as $column) {
			$cell=$this->getWrapper('row.content cell container');
			$cell->attrs=$column->getCellPrototype()->attrs;
			$cell->addClass($this->getValue('row.content cell .class'));

			if ($column instanceof ActionColumn) {
				$value='';
				foreach ($this->dataGrid->getActions() as $action) {
					if (!is_callable($action->ifDisableCallback) || !callback($action->ifDisableCallback)->invokeArgs(array($data))) {
						$html=$action->getHtml();
						$html->title($this->dataGrid->translate($html->title));
						$action->generateLink(array($primary => $data[$primary]));
						$this->onActionRender($html, $data);
						$value.=$html->render().' ';
						}
					else {
						$value.=Html::el('span')->setText($this->dataGrid->translate($action->getHtml()->title))->render().' ';
						}
					}
				$cell->addClass('actions');
				}
			else {
				if (!array_key_exists($column->getName(), $data)) {
					throw new \Nette\InvalidArgumentException("Non-existing column '".$column->getName()."' in datagrid '".$this->dataGrid->getName()."'");
					}
				$value=$column->formatContent($data[$column->getName()], $data);
				}

			$cell->setHtml((string)$value);
			$this->onCellRender($cell, $column->getName(), !($column instanceof ActionColumn)? $data[$column->getName()] : $data);
			$row->add($cell);
			}
		unset($form, $primary, $cell, $value, $action);
		$this->onRowRender($row, $data);
		return $row;
	}

	/**
	 * Generates datagrid footer
	 * @return Html
	 */
	protected function generateFooterRow()
	{
		$form=$this->dataGrid->getForm(TRUE);
		$paginator=$this->dataGrid->paginator;
		$row=$this->getWrapper('row.footer container');

		$count=count($this->dataGrid->getColumns());
		if ($this->dataGrid->hasOperations()) {
			$count++;
			}

		$cell=$this->getWrapper('row.footer cell container');
		$cell->colspan($count);

		$this->footerFormat=$this->dataGrid->translate($this->footerFormat);
		$html=str_replace(
				array(
					'%operations%',
					'%paginator%',
					'%info%',
					),
				array(
					$this->renderOperations(),
					$this->renderPaginator(),
					$this->renderInfo(),
					),
				$this->footerFormat
				);
		$cell->setHtml($html);
		$row->add($cell);

		return $row;
	}

	/**
	 * @param string $name
	 * @return Html
	 */
	protected function getWrapper($name)
	{
		$data=$this->getValue($name);
		return $data instanceof Html ? clone $data : Html::el($data);
	}

	/**
	 * @param string $name
	 * @return string
	 */
	protected function getValue($name)
	{
		$name=explode(' ', $name);
		if (count($name)==3) {
			$data=&$this->wrappers[$name[0]][$name[1]][$name[2]];
			}
		else {
			$data=&$this->wrappers[$name[0]][$name[1]];
			}
		return $data;
	}

	/**
	 * @return \Lohini\Components\DataGrid\DataGrid
	 */
	public function getDataGrid()
	{
		return $this->dataGrid;
	}
}
