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
 * Enhanced Problems widget form view
 */
use Modules\EnhancedProblems\Helpers\Html\CTagHtml;
use Modules\EnhancedProblems\Helpers\WidgetForm62;

$fields = $data['dialogue']['fields'];

$form = $data['form'];

$rf_rate_field = ($data['templateid'] === null) ? $fields['rf_rate'] : null;

if (version_compare(ZABBIX_VERSION, '6.2', '<'))
{
	$form_list = CWidgetHelper::createFormList($data['dialogue']['name'], $data['dialogue']['type'],
		ZBX_WIDGET_VIEW_MODE_HIDDEN_HEADER, $data['known_widget_types'], null
	);
}
else
{
	$form_grid = CWidgetHelper::createFormGrid($data['dialogue']['name'], $data['dialogue']['type'],
		$data['dialogue']['view_mode'], $data['known_widget_types'],
		$data['templateid'] === null ? $fields['rf_rate'] : null
	);
	$form_list = new WidgetForm62($form_grid);
}

$scripts = [];
$jq_templates = [];

// Widget fields "Name" and "Show header"
$form_list->addRow(_('Always show header'),
	[
		(new CCheckBox('show_header'))
			->setId('show_header')
			->setChecked($data['dialogue']['view_mode'] == ZBX_WIDGET_VIEW_MODE_NORMAL),
		(new CTextBox('name', $data['dialogue']['name']))
			->setAttribute('placeholder', _('default'))
			->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
	]
);

// Filtering fields
$filtering_fields = (new CTable())->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR);

// Severity
$filtering_fields->addRow([
	CWidgetHelper::getLabel($fields['severities']),
	CWidgetHelper::getSeverities($fields['severities'])
]);

// Separator
$filtering_fields->addRow(new CRow(
	(new CCol(
		(new CTag('hr'))->addClass('border-separator-top')
	))->setColSpan(2)
));

// Tags
$filtering_fields->addRow([
	CWidgetHelper::getLabel($fields['evaltype']), 
	CWidgetHelper::getRadioButtonList($fields['evaltype'])
]);

// Tags filter list
$filtering_fields->addRow([
	CWidgetHelper::getLabel($fields['tags']),
	CWidgetHelper::getTags($fields['tags'])
]);

$scripts[] = $fields['tags']->getJavascript();
$jq_templates['tag-row-tmpl'] = CWidgetHelper::getTagsTemplate($fields['tags']);

// Separator
$filtering_fields->addRow(new CRow(
	(new CCol(
		(new CTag('hr'))->addClass('border-separator-top')
	))->setColSpan(2)
));

// Host based filtering
$filtering_fields->addRow([
	CWidgetHelper::getLabel($fields['host_based_filtering']),
	CWidgetHelper::getCheckBox($fields['host_based_filtering'])
]);

// Host groups
$field_groupids = CWidgetHelper::getGroup(
	$fields['groupids'],
	$data['captions']['ms']['groups']['groupids'],
	$form->getName()
);

$filtering_fields->addRow(
	(new CRow([
		CWidgetHelper::getMultiselectLabel($fields['groupids']),
		$field_groupids
	]))
		->setId('js_groupids_field')
		->addClass($fields['host_based_filtering']->getValue() ? null : ZBX_STYLE_DISPLAY_NONE)
);

$scripts[] = $field_groupids->getPostJS();

// Exclude host groups
$field_exclude_groupids = CWidgetHelper::getGroup(
	$fields['exclude_groupids'],
	$data['captions']['ms']['groups']['exclude_groupids'],
	$form->getName()
);

$filtering_fields->addRow(
	(new CRow([
		CWidgetHelper::getMultiselectLabel($fields['exclude_groupids']), 
		$field_exclude_groupids
	]))
		->setId('js_exclude_groupids_field')
		->addClass($fields['host_based_filtering']->getValue() ? null : ZBX_STYLE_DISPLAY_NONE)
);

$scripts[] = $field_exclude_groupids->getPostJS();

// Hosts
$field_hostids = CWidgetHelper::getHost(
	$fields['hostids'],
	$data['captions']['ms']['hosts']['hostids'],
	$form->getName()
);

$filtering_fields->addRow(
	(new CRow([
		CWidgetHelper::getMultiselectLabel($fields['hostids']), 
		$field_hostids
	]))
		->setId('js_hostids_field')
		->addClass($fields['host_based_filtering']->getValue() ? null : ZBX_STYLE_DISPLAY_NONE)
);

$scripts[] = $field_hostids->getPostJS();

// Separator
$filtering_fields->addRow(new CRow(
	(new CCol(
		(new CTag('hr'))->addClass('border-separator-top')
	))->setColSpan(2)
));

// Problem
$filtering_fields->addRow([
	CWidgetHelper::getLabel($fields['problem']), 
	CWidgetHelper::getTextBox($fields['problem'])
]);

// Separator
$filtering_fields->addRow(new CRow(
	(new CCol(
		(new CTag('hr'))->addClass('border-separator-top')
	))->setColSpan(2)
));

// Show unacknowledged only
$filtering_fields->addRow([
	CWidgetHelper::getLabel($fields['unacknowledged']),
	CWidgetHelper::getCheckBox($fields['unacknowledged'])
]);

// Filtering section
$form_list->addRow(
	'Filtering',
	$filtering_fields,
	'enhanced-problem-filtering-fields'
);

// Show tags
$form_list->addRow(CWidgetHelper::getLabel($fields['show_tags_count']), CWidgetHelper::getRadioButtonList($fields['show_tags_count']));

// Tag name
$form_list->addRow(CWidgetHelper::getLabel($fields['tag_name_format']),
	CWidgetHelper::getRadioButtonList($fields['tag_name_format'])
		->setEnabled($fields['show_tags_count']->getValue() !== SHOW_TAGS_NONE)
);

// Tag display priority
$form_list->addRow(CWidgetHelper::getLabel($fields['tag_priority']),
	CWidgetHelper::getTextBox($fields['tag_priority'])
		->setEnabled($fields['show_tags_count']->getValue() !== SHOW_TAGS_NONE)
		->setAttribute('placeholder', _('comma-separated list'))
);

// Separator
$form_list->addRow(
	(new CTag('hr'))->addClass('border-separator-top')->addStyle('border-color: red;'),
	(new Ctag('span'))->addItem('Next sections are developed by initMAX s.r.o.')->addStyle('color: red;'),
	null,

);

// Sorting fields
$sorting_fields = (new CTable())->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR);

// Sorting - 1st level
$sorting_fields->addRow(
	[
		'1st level sorting',
		'',
		CWidgetHelper::getSelect($fields['first_level_sorting_column']),
		CWidgetHelper::getRadioButtonList($fields['first_level_sorting_order'])
	]
	,
	null,
	'first_level_sorting'
);

// Sorting - 2nd level
$sorting_fields->addRow(
	[
		CWidgetHelper::getLabel($fields['second_level_sorting_enabled']),
		CWidgetHelper::getCheckBox($fields['second_level_sorting_enabled']),
		CWidgetHelper::getSelect($fields['second_level_sorting_column'])
			->addClass($fields['second_level_sorting_enabled']->getValue() ? null : ZBX_STYLE_DISPLAY_NONE),
		CWidgetHelper::getRadioButtonList($fields['second_level_sorting_order'])
			->addClass($fields['second_level_sorting_enabled']->getValue() ? null : ZBX_STYLE_DISPLAY_NONE)
	],
	null,
	'second_level_sorting'
);

$sorting_fields->addRow(
	[
		(new CCol('Example is simplified, it is possible to add more sorting levels'))
			->addStyle('color: red')
			->setColSpan(4)
			->addClass($fields['second_level_sorting_enabled']->getValue() ? null : ZBX_STYLE_DISPLAY_NONE)
			->setId('third_level_sorting_info')
	],
	null,
	'third_level_sorting'
);

// Filtering section
$form_list->addRow(
	'Sorting',
	$sorting_fields,
	'enhanced-problem-sorting-fields'
);

// Display options fields
$display_options_fields = (new CTable())->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR);

// Refresh interval
$display_options_fields->addRow([
	CWidgetHelper::getLabel($rf_rate_field),
	CWidgetHelper::getSelect($rf_rate_field)
]);

// Show lines (Maximum displayed alarm count)
$display_options_fields->addRow([
	CWidgetHelper::getLabel($fields['show_lines']), 
	CWidgetHelper::getIntegerBox($fields['show_lines']),
	(new CSpan('We override default limit from Zabbix'))->addStyle('color: red;')
]);

// Acknowledge problem style
$display_options_fields->addRow([
	CWidgetHelper::getLabel($fields['acknowledge_problem_style']), 
	CWidgetHelper::getRadioButtonList($fields['acknowledge_problem_style'])
]);

// Font
$display_options_fields->addRow([
	CWidgetHelper::getLabel($fields['field_font']),
	CWidgetHelper::getSelect($fields['field_font'])
]);

// Font size
$display_options_fields->addRow([
	CWidgetHelper::getLabel($fields['field_fontsize']), 
	CWidgetHelper::getIntegerBox($fields['field_fontsize'])
]);

// Display options section
$form_list->addRow(
	'Display options',
	$display_options_fields,
	'enhanced-problem-display-options-fields'
);

// Summary row fields
$summary_row_fields = (new CTable())->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR);

// Summary row description
$summary_row_fields->addRow([
	(new CDiv(CWidgetHelper::getLabel($fields['summary_row_description'])))->addClass(ZBX_STYLE_TABLE_FORMS_TD_LEFT),
	CWidgetHelper::getTextBox($fields['summary_row_description'])
]);

// Summary row display severities count
$summary_row_fields->addRow([
	(new CDiv(CWidgetHelper::getLabel($fields['summary_row_display_severities_count'])))->addClass(ZBX_STYLE_TABLE_FORMS_TD_LEFT), 
	CWidgetHelper::getRadioButtonList($fields['summary_row_display_severities_count'])
]);

// Summary row display total events count
$summary_row_fields->addRow([
	(new CDiv(CWidgetHelper::getLabel($fields['summary_row_display_total_events_count'])))->addClass(ZBX_STYLE_TABLE_FORMS_TD_LEFT),
	CWidgetHelper::getRadioButtonList($fields['summary_row_display_total_events_count'])
]);

// Show summary row
$form_list->addRow(
	CWidgetHelper::getLabel($fields['show_summary_row']),
	(new CDiv([
		CWidgetHelper::getCheckBox($fields['show_summary_row']),
		(new CDiv($summary_row_fields))
			->setId('enhanced-problem-summary-row-fields')
			->addClass($fields['show_summary_row']->getValue() ? null : ZBX_STYLE_DISPLAY_NONE)
			->addStyle('padding-top: 5px')
	]))->addStyle('line-height: 24px')
);

// Columns
$columns = explode(',', $fields['columns_order']->getValue());
$columns_table = (new CTable())->setId('columns_list');
$columns_table->setHeader((new CRowHeader(['', 'Name', '', 'Width', 'Custom Label']))->addClass('border-separator-bottom'));

// Available elements for each row fo "Columns selection and order" table.
$table_row_elements = [
	'show_time' => ['show_time', 'column_time_width',  'column_time_label'], 
	'show_host' => ['show_host', 'column_host_width',  'column_host_label'],
	'show_problem_and_severity' => ['show_problem_and_severity', 'column_problem_and_severity_width', 'column_problem_and_severity_label'],
	'show_event_actions_icons' => ['show_event_actions_icons', 'column_event_actions_icons_width', 'column_event_actions_icons_label'],
	'show_tags' => ['show_tags', 'column_tags_width', 'column_tags_label']
];

foreach ($columns as $column)
{
	if (!array_key_exists($column, $table_row_elements)) {
		continue;
	}

	$row = [];
	[$checkbox_name, $width_name, $label_name] = $table_row_elements[$column];
	$row[] = (new CCol(new CDiv('⁝⁝')))->addClass(ZBX_STYLE_CURSOR_POINTER)->addClass(ZBX_STYLE_TD_DRAG_ICON);
	$row[] = [
		new CVar('columns[]', $checkbox_name),
		CWidgetHelper::getCheckBox($fields[$checkbox_name])
	];
	$row[] = (new CDiv(CWidgetHelper::getLabel($fields[$checkbox_name], null, null)))->addClass('text');
	$row[] = [CWidgetHelper::getIntegerBox($fields[$width_name])->addClass('js_column_width'), ' %'];
	$row[] = CWidgetHelper::getTextBox($fields[$label_name]->setWidth(ZBX_TEXTAREA_SMALL_WIDTH));
	
	$columns_table->addRow((new CRow($row))->addClass('sortable'));
}

$columns_table->addRow([
	'', '', (new CCol('Percentage total:'))->addStyle('text-align: right'), (new CCol(''))->setId('js_width_summary'), ''
], 'border-separator-top');

$form_list->addRow(new CLabel(_('Columns and order')),
	(new CDiv($columns_table))->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR)
);

$td_left_column_class = ZBX_STYLE_TABLE_FORMS_TD_LEFT;
$ui_theme = CWebUser::$data['theme'];

if ($ui_theme === THEME_DEFAULT) {
	$ui_theme = CSettingsHelper::get(CSettingsHelper::DEFAULT_THEME);
}

$form->addClass('theme_ui_' . $ui_theme);
$form->addItem((new CTagHtml('style', true))->addItem(<<<CSS
	.table-forms-separator {
		width: 100%;
	}

	.theme_ui_dark-theme, :root {
		--color-muted: #383838;
	}

	.theme_ui_blue-theme {
		--color-muted: #ebeef0;
	}

	.border-separator-top {
		border: 0 none;
		border-top: 1px solid var(--color-muted);
	}
	.border-separator-bottom {
		border: 0 none;
		border-bottom: 1px solid var(--color-muted);
	}
	.icon-help-hint {
		display: inline;
	}

	.icon-help-hint::after {
		display: inline-block;
		top: 0;
		left: 0;
		width: 14px;
		text-align: center;
		line-height: 14px;
	}

	#enhanced-problem-summary-row-fields .{$td_left_column_class} {
		padding: 0;
	}

	#enhanced-problem-filtering-fields .{$td_left_column_class} {
		padding: 0;
	}

	#enhanced-problem-sorting-fields .{$td_left_column_class} {
		padding: 0;
	}

	/* Hide default "Show header" and "Name" fields. */
	.dashboard-grid-widget-enhancedproblems .table-forms-row-with-second-field .table-forms-second-column,
	label[for="name"], label[for="name"]+.form-field {
		display: none;
	}

	ul.table-forms > li:nth-child(2),
	.form-field.form-field-show-header {
		display: none;
	}
CSS
));

$form->addItem($form_list);

$scripts[] = (new CView('widgets/enhancedproblems.form.view.js', $data))->getOutput();

// Add inline scripts
$form->addItem(new CScriptTag($scripts));
$form->addItem(
	(new CScriptTag('
		widget_configuration_form.init('.json_encode([
			'form_id' => $form->getId(),
			'columns_table' => $columns_table->getId()
		]).');
	'))->setOnDocumentReady()
);

// Add javascript templates
foreach ($jq_templates as $id => $jq_template)
{
	$form->addItem((new CScriptTemplate($id))->addItem($jq_template));
}

if (version_compare(ZABBIX_VERSION, '6.2', '>='))
{
	return [
		'form' => $form,
		'scripts' => $scripts
	];
}
