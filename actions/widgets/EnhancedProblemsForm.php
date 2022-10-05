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

namespace Modules\EnhancedProblems\Actions\Widgets;

use CWidgetField;
use CWidgetFieldCheckBox;
use CWidgetFieldHidden;
use CWidgetFieldIntegerBox;
use CWidgetFieldMsGroup;
use CWidgetFieldMsHost;
use CWidgetForm;
use CWidgetFieldSelect;
use CWidgetFieldTextBox;
use CWidgetFieldRadioButtonList;
use CWidgetFieldSeverities;
use CWidgetFieldTags;

class EnhancedProblemsForm extends CWidgetForm
{

    const WIDGET_TYPE = 'enhancedproblems';

    public function __construct($data, $templateid)
    {
        parent::__construct($data, $templateid, static::WIDGET_TYPE);
        
        $this->data = self::convertDottedKeys($this->data);

	// Severity field
	$field_severities = new CWidgetFieldSeverities('severities', _('Severity'));

	if (array_key_exists('severities', $this->data))
	{
	    $field_severities->setValue($this->data['severities']);
	}

	$this->fields[$field_severities->getName()] = $field_severities;

	// Tag evaltype (And/Or)
	$field_evaltype = (new CWidgetFieldRadioButtonList('evaltype', _('Tags'), [
	    TAG_EVAL_TYPE_AND_OR => _('And/Or'),
	    TAG_EVAL_TYPE_OR => _('Or')
	]))
	    ->setDefault(TAG_EVAL_TYPE_AND_OR)
	    ->setModern(true);

	if (array_key_exists('evaltype', $this->data)) {
	    $field_evaltype->setValue($this->data['evaltype']);
	}

	$this->fields[$field_evaltype->getName()] = $field_evaltype;

	// Tags array: tag, operator and value. No label, because it belongs to previous group.
	$field_tags = new CWidgetFieldTags('tags', '');

	if (array_key_exists('tags', $this->data)) {
	    $field_tags->setValue($this->data['tags']);
	}

	$this->fields[$field_tags->getName()] = $field_tags;

	// Host based filtering
	$host_based_filtering = (new CWidgetFieldCheckBox('host_based_filtering', _('Host based filtering')));

	if (array_key_exists('host_based_filtering', $this->data))
	{
	$host_based_filtering->setValue($this->data['host_based_filtering']);
	}

	$this->fields[$host_based_filtering->getName()] = $host_based_filtering;

	// Host groups
	$field_groups = new CWidgetFieldMsGroup('groupids', _('Host groups'));

	if (array_key_exists('groupids', $this->data)) {
	    $field_groups->setValue($this->data['groupids']);
	}

	$this->fields[$field_groups->getName()] = $field_groups;

	// Exclude host groups
	$field_exclude_groups = new CWidgetFieldMsGroup('exclude_groupids', _('Exclude host groups'));

	if (array_key_exists('exclude_groupids', $this->data)) {
	    $field_exclude_groups->setValue($this->data['exclude_groupids']);
	}

	$this->fields[$field_exclude_groups->getName()] = $field_exclude_groups;

	// Hosts field
	$field_hosts = new CWidgetFieldMsHost('hostids', _('Hosts'));
	$field_hosts->filter_preselect_host_group_field = 'groupids_';

	if (array_key_exists('hostids', $this->data)) {
	    $field_hosts->setValue($this->data['hostids']);
	}

	$this->fields[$field_hosts->getName()] = $field_hosts;

	// Problem field
	$field_problem = new CWidgetFieldTextBox('problem', _('Problem'));

	if (array_key_exists('problem', $this->data)) {
	    $field_problem->setValue($this->data['problem']);
	}

	$this->fields[$field_problem->getName()] = $field_problem;

	// Show unacknowledged only
	$field_unacknowledged = (new CWidgetFieldCheckBox('unacknowledged', _('Show unacknowledged only')))
	    ->setFlags(CWidgetField::FLAG_ACKNOWLEDGES);

	if (array_key_exists('unacknowledged', $this->data))
	{
	    $field_unacknowledged->setValue($this->data['unacknowledged']);
	}

	$this->fields[$field_unacknowledged->getName()] = $field_unacknowledged;

	// Show tags
	$field_show_tags_count = (new CWidgetFieldRadioButtonList('show_tags_count', _('Show tags'), [
	    SHOW_TAGS_NONE => _('None'),
	    SHOW_TAGS_1 => SHOW_TAGS_1,
	    SHOW_TAGS_2 => SHOW_TAGS_2,
	    SHOW_TAGS_3 => SHOW_TAGS_3
	]))
	    ->setDefault(SHOW_TAGS_NONE)
	    ->setModern(true)
	    ->setAction('var disabled = jQuery(this).filter("[value=\''.SHOW_TAGS_NONE.'\']").is(":checked");'.
		'jQuery("#tag_priority").prop("disabled", disabled);'.
		'jQuery("#tag_name_format input").prop("disabled", disabled)'
	    );

	if (array_key_exists('show_tags_count', $this->data))
	{
	    $field_show_tags_count->setValue($this->data['show_tags_count']);
	}

	$this->fields[$field_show_tags_count->getName()] = $field_show_tags_count;

	// Tag name
	$tag_format_line = (new CWidgetFieldRadioButtonList('tag_name_format', _('Tag name'), [
	    TAG_NAME_FULL => _('Full'),
	    TAG_NAME_SHORTENED => _('Shortened'),
	    TAG_NAME_NONE => _('None')
	]))
	    ->setDefault(TAG_NAME_FULL)
	    ->setModern(true);

	if (array_key_exists('tag_name_format', $this->data)) {
	    $tag_format_line->setValue($this->data['tag_name_format']);
	}
	$this->fields[$tag_format_line->getName()] = $tag_format_line;

	// Tag display priority
	$tag_priority = (new CWidgetFieldTextBox('tag_priority', _('Tag display priority')));

	if (array_key_exists('tag_priority', $this->data)) {
	    $tag_priority->setValue($this->data['tag_priority']);
	}
	$this->fields[$tag_priority->getName()] = $tag_priority;

	// Sorting 
	$sorting_fields = [_('Time'), _('Host'), _('Problem'), _('Severity'), _('Acknowledged')];
	$sorting_order = [_('ASC'), _('DESC')];
	
	// Sorting - 1st level - Column
	$first_level_sorting_column = (new CWidgetFieldSelect('first_level_sorting_column', _('Column'),
	    $sorting_fields
	));

	if (array_key_exists('first_level_sorting_column', $this->data))
	{
	    $first_level_sorting_column->setValue($this->data['first_level_sorting_column']);
	}

	$this->fields[$first_level_sorting_column->getName()] = $first_level_sorting_column;	

        // Sorting - 1st level - Order
        $first_level_sorting_order = (new CWidgetFieldRadioButtonList('first_level_sorting_order', _('Order'),
	    $sorting_order
	))
	    ->setDefault(reset($sorting_order))
	    ->setModern(true);

	if (array_key_exists('first_level_sorting_order', $this->data)) {
	    $first_level_sorting_order->setValue($this->data['first_level_sorting_order']);
	}

	$this->fields[$first_level_sorting_order->getName()] = $first_level_sorting_order;

	// Sorting - 2nd level
	$second_level_sorting_enabled = new CWidgetFieldCheckBox('second_level_sorting_enabled', _('2nd level sorting'));

	if (array_key_exists('second_level_sorting_enabled', $this->data)) {
	    $second_level_sorting_enabled->setValue($this->data['second_level_sorting_enabled']);
	}

	$this->fields[$second_level_sorting_enabled->getName()] = $second_level_sorting_enabled;

	// Sorting - 2nd level - Column
	$second_level_sorting_column = (new CWidgetFieldSelect('second_level_sorting_column', _('Column'),
	    $sorting_fields
	));

	if (array_key_exists('second_level_sorting_column', $this->data))
	{
	    $second_level_sorting_column->setValue($this->data['second_level_sorting_column']);
	}

	$this->fields[$second_level_sorting_column->getName()] = $second_level_sorting_column;	

        // Sorting - 2nd level - Order
        $second_level_sorting_order = (new CWidgetFieldRadioButtonList('second_level_sorting_order', _('Order'),
	    $sorting_order
	))
	    ->setDefault(reset($sorting_order))
	    ->setModern(true);

	if (array_key_exists('second_level_sorting_order', $this->data))
	{
	    $second_level_sorting_order->setValue($this->data['second_level_sorting_order']);
	}

	$this->fields[$second_level_sorting_order->getName()] = $second_level_sorting_order;

	// Lines to show (Maximum displayed alarm count)
	$field_lines = (new CWidgetFieldIntegerBox('show_lines', _('Maximum displayed alarm count'), 
	    ZBX_MIN_WIDGET_LINES,													// Minimum lines
	    10000																	// Maximum lines (overriding ZBX_MAX_WIDGET_LINES)
	));

	$field_lines->setFlags(CWidgetField::FLAG_LABEL_ASTERISK);
	$field_lines->setDefault(1000);												// Default value (overriding ZBX_DEFAULT_WIDGET_LINES)

	if (array_key_exists('show_lines', $this->data))
	{
	    $field_lines->setValue($this->data['show_lines']);
	}

	$this->fields[$field_lines->getName()] = $field_lines;

        // Acknowledge problem style
        $acknowledge_problem_style = (new CWidgetFieldRadioButtonList('acknowledge_problem_style', _('Acknowledge problem style'), [
	    0 => _('Monochromatic'),
	    1 => _('Color')
	]))
        ->setDefault(1)
        ->setModern(true);

	if (array_key_exists('acknowledge_problem_style', $this->data))
	{
	    $acknowledge_problem_style->setValue($this->data['acknowledge_problem_style']);
	}

	$this->fields[$acknowledge_problem_style->getName()] = $acknowledge_problem_style;

	// Show summary row
	$field_show_summary_row = new CWidgetFieldCheckBox('show_summary_row', _('Show summary row'));
	
	$field_show_summary_row->setDefault(true);

	if (array_key_exists('show_summary_row', $this->data))
	{
	    $field_show_summary_row->setValue($this->data['show_summary_row']);
	}

	$this->fields[$field_show_summary_row->getName()] = $field_show_summary_row;

	// Summary row name
	$field_summary_row_description = (new CWidgetFieldTextBox('summary_row_description', _('Description')));

	if (array_key_exists('summary_row_description', $this->data))
	{
	    $field_summary_row_description->setValue($this->data['summary_row_description']);
	}

	$this->fields[$field_summary_row_description->getName()] = $field_summary_row_description;

        // Display severities count
        $field_summary_row_display_severities_count = (new CWidgetFieldRadioButtonList('summary_row_display_severities_count', _('Display severities count'), [
	    0 => _('None'),
	    1 => _('Nonack only'),
	    2 => _('Ack only'),
	    3 => _('Nonack + Ack')
	]))
        ->setDefault(1)
        ->setModern(true);

	if (array_key_exists('summary_row_display_severities_count', $this->data))
	{
	    $field_summary_row_display_severities_count->setValue($this->data['summary_row_display_severities_count']);
	}

	$this->fields[$field_summary_row_display_severities_count->getName()] = $field_summary_row_display_severities_count;

        // Display total events count
        $field_summary_row_display_total_events_count = (new CWidgetFieldRadioButtonList('summary_row_display_total_events_count', _('Display total events count'), [
	    0 => _('None'),
	    1 => _('Nonack only'),
	    2 => _('Ack only'),
	    3 => _('Nonack + Ack')
	]))
        ->setDefault(1)
        ->setModern(true);

	if (array_key_exists('summary_row_display_total_events_count', $this->data))
	{
	    $field_summary_row_display_total_events_count->setValue($this->data['summary_row_display_total_events_count']);
	}

	$this->fields[$field_summary_row_display_total_events_count->getName()] = $field_summary_row_display_total_events_count;

	// Description max lenght
	$field_description_max_lenght = (new CWidgetFieldIntegerBox('description_max_length', _('Max length'), 25))
	    ->setDefault(50);

	if (array_key_exists('description_max_length', $this->data))
	{
	    $field_description_max_lenght->setValue($this->data['description_max_length']);
	}

	$this->fields[$field_description_max_lenght->getName()] = $field_description_max_lenght;

	// Columns order
	$sortable_rows = [
	    [
		(new CWidgetFieldCheckBox('show_time', _('Time')))->setDefault(true),
		(new CWidgetFieldIntegerBox('column_time_width', null))->setDefault(10),
		(new CWidgetFieldTextBox('column_time_label', null))->setPlaceholder(_('Time'))
	    ],
	    [
		(new CWidgetFieldCheckBox('show_host', _('Host')))->setDefault(true),
		(new CWidgetFieldIntegerBox('column_host_width', null))->setDefault(10),
		(new CWidgetFieldTextBox('column_host_label', null))->setPlaceholder(_('Host'))
	    ],
	    [
		(new CWidgetFieldCheckBox('show_problem_and_severity', _('Problem')))->setDefault(true),
		(new CWidgetFieldIntegerBox('column_problem_and_severity_width', null))->setDefault(20),
		(new CWidgetFieldTextBox('column_problem_and_severity_label', null))->setPlaceholder(_('Problem'))
	    ],
	    [
		new CWidgetFieldCheckBox('show_event_actions_icons', _('Actions icons')),
		(new CWidgetFieldIntegerBox('column_event_actions_icons_width', null))->setDefault(10),
		(new CWidgetFieldTextBox('column_event_actions_icons_label', null))->setPlaceholder(_('Event icons'))
	    ],
	    [
		new CWidgetFieldCheckBox('show_tags', _('Tags')),
		(new CWidgetFieldIntegerBox('column_tags_width', null))->setDefault(50),
		(new CWidgetFieldTextBox('column_tags_label', null))->setPlaceholder(_('Tags'))
	    ]
	];

	foreach ($sortable_rows as $row)
	{
	    [$checkbox, $width, $input] = $row;

	    if ($checkbox !== null)
	    {
		if (array_key_exists($checkbox->getName(), $this->data))
		{
		    $checkbox->setValue($this->data[$checkbox->getName()]);
		}

		$this->fields[$checkbox->getName()] = $checkbox;
	    }

	    if ($width !== null)
	    {
		if (array_key_exists($width->getName(), $this->data))
		{
		    $width->setValue($this->data[$width->getName()]);
		}

		$this->fields[$width->getName()] = $width;
	    }

	    if ($input !== null)
	    {
		if (array_key_exists($input->getName(), $this->data))
		{
		    $input->setValue($this->data[$input->getName()]);
		}

		$this->fields[$input->getName()] = $input;
	    }
	}

	$columns = [
	    'show_time', 
	    'show_host',
	    'show_problem_and_severity',
	    'show_event_actions_icons', 
	    'show_tags'
	];
	
	$columns_order = new CWidgetFieldHidden('columns_order', ZBX_WIDGET_FIELD_TYPE_STR);
	$columns_order->setValue(implode(',', $columns));

	if (array_key_exists('columns', $this->data))
	{
	    $this->data['columns_order'] = implode(',', array_intersect($this->data['columns'], $columns));
	}

	if (array_key_exists('columns_order', $this->data))
	{
	    $columns_order->setValue($this->data['columns_order']);
	}

	$this->fields[$columns_order->getName()] = $columns_order;

	// Font
	$field_font = (new CWidgetFieldSelect('field_font', _('Font'), [
	    0 => 'Arial',
	    1 => 'Georgia'
	]))
	    ->setDefault(9);

	if (array_key_exists('field_font', $this->data))
	{
	    $field_font->setValue($this->data['field_font']);
	}

	$this->fields[$field_font->getName()] = $field_font;	

	// Show fontsize
	$field_fontsize = (new CWidgetFieldIntegerBox('field_fontsize', _('Font size'), 
	    8,													// Minimum
	    50													// Maximum
	))
	    ->setFlags(CWidgetField::FLAG_LABEL_ASTERISK)
	    ->setDefault(10);

	if (array_key_exists('field_fontsize', $this->data))
	{
	    $field_fontsize->setValue($this->data['field_fontsize']);
	}

	$this->fields[$field_fontsize->getName()] = $field_fontsize;
    }
}
