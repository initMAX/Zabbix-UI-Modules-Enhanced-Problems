<?php
/*
** initMAX
** Copyright (C) 2021-2022 initMAX s.r.o.
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 3 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

/**
 * @var CView $this
 */
$output = [];
$scripts = [];
$root = Zbase::getRootDir();
$widget_view_file = $root . '/include/classes/widgets/views/widget.'.$data['dialogue']['type'].'.form.view.php';

// is_file do not use include path therefor have to add absolute paths.
CView::registerDirectory($root.'/local/app/views');
CView::registerDirectory($root.'/app/views');
CView::registerDirectory($root.'/include/views');
$this->directory = $root.'/app/views';

if (version_compare(ZABBIX_VERSION, '6.2', '>='))
{
	$output['doc_url'] = CDocHelper::getUrl(CDocHelper::MONITORING_DASHBOARD_WIDGET_EDIT);
}

if (file_exists($widget_view_file))
{
	chdir(dirname($widget_view_file));
	$widget_view = include $widget_view_file;
}
else
{
	$form = CWidgetHelper::createForm();
	$data['form'] = $form;
	$widget_view = ['form' => $form];

	// Use CView as wrapper for module widget. Widget view should use $data['form'].
	(new CView('widgets/'.$data['dialogue']['type'].'.form.view', $data))->getOutput();
}

$form = $widget_view['form']
	->addClass('dashboard-grid-widget-'.$data['dialogue']['type']);

// Submit button is needed to enable submit event on Enter on inputs.
$form->addItem((new CInput('submit', 'dashboard_widget_config_submit'))->addStyle('display: none;'));

$output = [
	'header' => $data['unique_id'] !== null ? _s('Edit widget') : _s('Add widget'),
	'body' => '',
	'buttons' => [
		[
			'title' => $data['unique_id'] !== null ? _s('Apply') : _s('Add'),
			'class' => 'dialogue-widget-save',
			'keepOpen' => true,
			'isSubmit' => true,
			'action' => 'ZABBIX.Dashboard.applyWidgetProperties();'
		]
	],
	'data' => [
		'original_properties' => [
			'type' => $data['dialogue']['type'],
			'unique_id' => $data['unique_id'],
			'dashboard_page_unique_id' => $data['dashboard_page_unique_id']
		]
	]
] + $output;

$widget_view += ['scripts' => []];

if (version_compare(ZABBIX_VERSION, '6.2', '>='))
{
	$output['doc_url'] = CDocHelper::getUrl(CDocHelper::MONITORING_DASHBOARD_WIDGET_EDIT);

	// Adding 6.2 specific dashboard initialization javascript.
	$widget_view['scripts'][] = $this->readJsFile('monitoring.dashboard.widget.edit.js.php');
	$widget_view['scripts'][] = 'widget_form.init();';
}

if (($messages = getMessages()) !== null)
{
	$output['body'] .= $messages->toString();
}

$output['body'] .= $form->toString();

if (array_key_exists('jq_templates', $widget_view))
{
	foreach ($widget_view['jq_templates'] as $id => $jq_template)
	{
		$output['body'] .= '<script type="text/x-jquery-tmpl" id="'.$id.'">'.$jq_template.'</script>';
	}
}

$output['body'] .= get_js(implode("\n", $widget_view['scripts']));

if ($data['user']['debug_mode'] == GROUP_DEBUG_MODE_ENABLED)
{
	CProfiler::getInstance()->stop();
	$output['debug'] = CProfiler::getInstance()->make()->toString();
}

echo json_encode($output);
