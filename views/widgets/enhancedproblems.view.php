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

use Modules\EnhancedProblems\Helpers\Html\CTagHtml;
use Modules\EnhancedProblems\Helpers\Functions\CColor;

/**
 * @var CView $this
 */

// Widget instance unique id
$widget_instance_id = uniqid('ep');

// Predefined list of fonts
$font_map = [
	'Arial, sans-serif',
	'Georgia, serif'
];

// Indicator of sort field
$sort_div_asc = (new CSpan())->addClass(ZBX_STYLE_ARROW_UP);
$sort_div_desc = (new CSpan())->addClass(ZBX_STYLE_ARROW_DOWN);

// Allowed actions
$allowed['ui_problems'] = $data['allowed_ui_problems'];
$allowed['add_comments'] = $data['allowed_add_comments'];
$allowed['change_severity'] = $data['allowed_change_severity'];
$allowed['acknowledge'] = $data['allowed_acknowledge'];

// Summary row
$show_summary_row = $data['fields']['show_summary_row'];
$summary_row_description = $data['fields']['summary_row_description'];
$summary_row_display_severities_count = $data['fields']['summary_row_display_severities_count'];
$summary_row_display_total_events_count = $data['fields']['summary_row_display_total_events_count'];

// Columns
$show_time = $data['fields']['show_time'];
$show_host = $data['fields']['show_host'];
$show_problem_and_severity = $data['fields']['show_problem_and_severity'];
$show_event_actions_icons = $data['fields']['show_event_actions_icons'];
$show_tags = $data['fields']['show_tags'];

// Columns name
$column_time_label = $data['fields']['column_time_label'] ?: _('Time');
$column_host_label = $data['fields']['column_host_label'] ?: _('Host');
$column_problem_and_severity_label = $data['fields']['column_problem_and_severity_label'] ?: _('Problem');
$column_event_actions_icons_label = $data['fields']['column_event_actions_icons_label'] ?: _('Actions');
$column_tags_label = $data['fields']['column_tags_label'] ?: _('Tags');

// Columns width
$column_time_width = $data['fields']['column_time_width'];
$column_host_width = $data['fields']['column_host_width'];
$column_problem_and_severity_width = $data['fields']['column_problem_and_severity_width'];
$column_event_actions_icons_width = $data['fields']['column_event_actions_icons_width'];
$column_tags_width = $data['fields']['column_tags_width'];

// Display options
$acknowledge_problem_style = $data['fields']['acknowledge_problem_style'];

// Other
$columns_order = $data['fields']['columns_order'];
$font = $data['fields']['field_font'];
$fontsize = $data['fields']['field_fontsize'];

// Cascading styles
$css = new CTagHtml('style', true);
$css->addItem('.problem-icon-list-item-'. $widget_instance_id .' { color: black !important; }'. PHP_EOL);
$css->addItem('.enhanced-problems-table-'. $widget_instance_id .' { font-size: '. $fontsize .'px !important; font-family: '. $font_map[$font] .' !important; table-layout: fixed; white-space: nowrap; text-overflow: ellipsis; }'. PHP_EOL);
$css->addItem('.column-time-'. $widget_instance_id .' { width: '. $column_time_width .'%; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }'. PHP_EOL);
$css->addItem('.column-host-'. $widget_instance_id .' { width: '. $column_host_width .'%; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }'. PHP_EOL);
$css->addItem('.column-problem-and-severity-'. $widget_instance_id .' { width: '. $column_problem_and_severity_width .'%; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }'. PHP_EOL);
$css->addItem('.column-event-actions-icons-'. $widget_instance_id .' { width: '. $column_event_actions_icons_width .'%; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }'. PHP_EOL);
$css->addItem('.column-tags-'. $widget_instance_id .' { width: '. $column_tags_width .'%; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }'. PHP_EOL);
$css->addItem('tr.js-row-selected td { background-color: gray !important; user-select: none; }'. PHP_EOL);
$css->addItem('tr.js-row-selected:hover td { background-color: gray !important; }'. PHP_EOL);
$css->addItem('table.js-problems-table tr {	user-select: none; }'. PHP_EOL);

$severityNotClassifiedName = CSeverityHelper::getStyle(TRIGGER_SEVERITY_NOT_CLASSIFIED);
$severityInformationName = CSeverityHelper::getStyle(TRIGGER_SEVERITY_INFORMATION);
$severityWarningName = CSeverityHelper::getStyle(TRIGGER_SEVERITY_WARNING);
$severityAverageName = CSeverityHelper::getStyle(TRIGGER_SEVERITY_AVERAGE);
$severityHighName = CSeverityHelper::getStyle(TRIGGER_SEVERITY_HIGH);
$severityDisasterName = CSeverityHelper::getStyle(TRIGGER_SEVERITY_DISASTER);

$severityNotClassifiedColor = new CColor(CSettingsHelper::get(CSettingsHelper::SEVERITY_COLOR_0));
$severityInformationColor = new CColor(CSettingsHelper::get(CSettingsHelper::SEVERITY_COLOR_1));
$severityWarningColor = new CColor(CSettingsHelper::get(CSettingsHelper::SEVERITY_COLOR_2));
$severityAverageColor = new CColor(CSettingsHelper::get(CSettingsHelper::SEVERITY_COLOR_3));
$severityHighColor = new CColor(CSettingsHelper::get(CSettingsHelper::SEVERITY_COLOR_4));
$severityDisasterColor = new CColor(CSettingsHelper::get(CSettingsHelper::SEVERITY_COLOR_5)); 

$severityNotClassifiedAcknowledgedTextColor = $acknowledge_problem_style ? new CColor(CSettingsHelper::get(CSettingsHelper::SEVERITY_COLOR_0)) : 'black';
$severityInformationAcknowledgedTextColor = $acknowledge_problem_style ? new CColor(CSettingsHelper::get(CSettingsHelper::SEVERITY_COLOR_1)) : 'black';
$severityWarningAcknowledgedTextColor = $acknowledge_problem_style ? new CColor(CSettingsHelper::get(CSettingsHelper::SEVERITY_COLOR_2)) : 'black';
$severityAverageAcknowledgedTextColor = $acknowledge_problem_style ? new CColor(CSettingsHelper::get(CSettingsHelper::SEVERITY_COLOR_3)) : 'black';
$severityHighAcknowledgedTextColor = $acknowledge_problem_style ? new CColor(CSettingsHelper::get(CSettingsHelper::SEVERITY_COLOR_4)) : 'black';
$severityDisasterAcknowledgedTextColor = $acknowledge_problem_style ? new CColor(CSettingsHelper::get(CSettingsHelper::SEVERITY_COLOR_5)) : 'black'; 

$severityNotClassifiedBackgroundHoverColor = '#'. $severityNotClassifiedColor->darken(15);
$severityInformationBackgroundHoverColor = '#'. $severityInformationColor->darken(15);
$severityWarningBackgroundHoverColor = '#'. $severityWarningColor->darken(15);
$severityAverageBackgroundHoverColor = '#'. $severityAverageColor->darken(15);
$severityHighBackgroundHoverColor = '#'. $severityHighColor->darken(15);
$severityDisasterBackgroundHoverColor = '#'. $severityDisasterColor->darken(15);

$severityNotClassifiedAcknowledgedBackgroundColor = $acknowledge_problem_style ? 'none' : 'gainsboro';
$severityInformationAcknowledgedBackgroundColor = $acknowledge_problem_style ? 'none' : 'gainsboro';
$severityWarningAcknowledgedBackgroundColor = $acknowledge_problem_style ? 'none' : 'gainsboro';
$severityAverageAcknowledgedBackgroundColor = $acknowledge_problem_style ? 'none' : 'gainsboro';
$severityHighAcknowledgedBackgroundColor = $acknowledge_problem_style ? 'none' : 'gainsboro';
$severityDisasterAcknowledgedBackgroundColor = $acknowledge_problem_style ? 'none' : 'gainsboro';

$severityNotClassifiedAcknowledgedBackgroundHoverColor = $acknowledge_problem_style ? '#'. $severityNotClassifiedColor->lighten(15) : 'silver';
$severityInformationAcknowledgedBackgroundHoverColor = $acknowledge_problem_style ? '#'. $severityInformationColor->lighten(15) : 'silver';
$severityWarningAcknowledgedBackgroundHoverColor = $acknowledge_problem_style ? '#'. $severityWarningColor->lighten(15) : 'silver';
$severityAverageAcknowledgedBackgroundHoverColor = $acknowledge_problem_style ? '#'. $severityAverageColor->lighten(15) : 'silver';
$severityHighAcknowledgedBackgroundHoverColor = $acknowledge_problem_style ? '#'. $severityHighColor->lighten(15) : 'silver';
$severityDisasterAcknowledgedBackgroundHoverColor = $acknowledge_problem_style ? '#'. $severityDisasterColor->lighten(15) : 'silver';

// Styles for Non-Acknowledged rows
$css->addItem('.'. $severityNotClassifiedName .'-'. $widget_instance_id .' td { background-color: '. $severityNotClassifiedColor .' !important; color: black !important; }'. PHP_EOL);
$css->addItem('.'. $severityInformationName .'-'. $widget_instance_id .' td { background-color: '. $severityInformationColor .' !important; color: black !important; }'. PHP_EOL);
$css->addItem('.'. $severityWarningName .'-'. $widget_instance_id .' td { background-color: '. $severityWarningColor .' !important; color: black !important; }'. PHP_EOL);
$css->addItem('.'. $severityAverageName .'-'. $widget_instance_id .' td { background-color: '. $severityAverageColor .' !important; color: black !important; }'. PHP_EOL);
$css->addItem('.'. $severityHighName .'-'. $widget_instance_id .' td { background-color: '. $severityHighColor .' !important; color: black !important; }'. PHP_EOL);
$css->addItem('.'. $severityDisasterName .'-'. $widget_instance_id .' td { background-color: '. $severityDisasterColor .' !important; color: black !important; }'. PHP_EOL);

// Styles for Acknowledged rows
$css->addItem('.'. $severityNotClassifiedName .'-acknowledged-'. $widget_instance_id .' td { color: '. $severityNotClassifiedAcknowledgedTextColor .' !important; background-color: '. $severityNotClassifiedAcknowledgedBackgroundColor .' !important; }'. PHP_EOL);
$css->addItem('.'. $severityInformationName .'-acknowledged-'. $widget_instance_id .' td { color: '. $severityInformationAcknowledgedTextColor .' !important; background-color: '. $severityInformationAcknowledgedBackgroundColor .' !important; }'. PHP_EOL);
$css->addItem('.'. $severityWarningName .'-acknowledged-'. $widget_instance_id .' td { color: '. $severityWarningAcknowledgedTextColor .' !important; background-color: '. $severityWarningAcknowledgedBackgroundColor .' !important; }'. PHP_EOL);
$css->addItem('.'. $severityAverageName .'-acknowledged-'. $widget_instance_id .' td { color: '. $severityAverageAcknowledgedTextColor .' !important; background-color: '. $severityAverageAcknowledgedBackgroundColor .' !important; }'. PHP_EOL);
$css->addItem('.'. $severityHighName .'-acknowledged-'. $widget_instance_id .' td { color: '. $severityHighAcknowledgedTextColor .' !important; background-color: '. $severityHighAcknowledgedBackgroundColor .' !important; }'. PHP_EOL);
$css->addItem('.'. $severityDisasterName .'-acknowledged-'. $widget_instance_id .' td { color: '. $severityDisasterAcknowledgedTextColor .' !important; background-color: '. $severityDisasterAcknowledgedBackgroundColor .' !important;}'. PHP_EOL);

// Style for Non-Acknowledged rows on hover
$css->addItem('.'. $severityNotClassifiedName .'-'. $widget_instance_id .':hover td { background-color: '. $severityNotClassifiedBackgroundHoverColor .' !important; }'. PHP_EOL);
$css->addItem('.'. $severityInformationName .'-'. $widget_instance_id .':hover td { background-color: '. $severityInformationBackgroundHoverColor .' !important; }'. PHP_EOL);
$css->addItem('.'. $severityWarningName .'-'. $widget_instance_id .':hover td { background-color: '. $severityWarningBackgroundHoverColor .' !important; }'. PHP_EOL);
$css->addItem('.'. $severityAverageName .'-'. $widget_instance_id .':hover td { background-color: '. $severityAverageBackgroundHoverColor .' !important; }'. PHP_EOL);
$css->addItem('.'. $severityHighName .'-'. $widget_instance_id .':hover td { background-color: '. $severityHighBackgroundHoverColor .' !important; }'. PHP_EOL);
$css->addItem('.'. $severityDisasterName .'-'. $widget_instance_id .':hover td 	{ background-color: '. $severityDisasterBackgroundHoverColor .' !important; }'. PHP_EOL);

// Style for Acknowledged rows on hover
$css->addItem('.'. $severityNotClassifiedName .'-acknowledged-'. $widget_instance_id .':hover td { background-color: '. $severityNotClassifiedAcknowledgedBackgroundHoverColor .' !important; color: black !important; }'. PHP_EOL);
$css->addItem('.'. $severityInformationName .'-acknowledged-'. $widget_instance_id .':hover td { background-color: '. $severityInformationAcknowledgedBackgroundHoverColor .' !important; color: black !important; }'. PHP_EOL);
$css->addItem('.'. $severityWarningName .'-acknowledged-'. $widget_instance_id .':hover td { background-color: '. $severityWarningAcknowledgedBackgroundHoverColor .' !important; color: black !important; }'. PHP_EOL);
$css->addItem('.'. $severityAverageName .'-acknowledged-'. $widget_instance_id .':hover td { background-color: '. $severityAverageAcknowledgedBackgroundHoverColor .' !important; color: black !important; }'. PHP_EOL);
$css->addItem('.'. $severityHighName .'-acknowledged-'. $widget_instance_id .':hover td { background-color: '. $severityHighAcknowledgedBackgroundHoverColor .' !important; color: black !important; }'. PHP_EOL);
$css->addItem('.'. $severityDisasterName .'-acknowledged-'. $widget_instance_id .':hover td { background-color: '. $severityDisasterAcknowledgedBackgroundHoverColor .' !important; color: black !important; }'. PHP_EOL);

// Table
$table = new CTableInfo();

// Add classes to table
$table->addClass('enhanced-problems-table-'. $widget_instance_id);
$table->addClass('js-problems-table');

// Table header
$header = [];

foreach(explode(',', $columns_order) as $column)
{
	switch($column)
	{	
		case 'show_time':
			if($show_time)
			{
				$header_time = [$column_time_label];

				if (array_key_exists('clock', $data['sorting_fields'])) {

					$header_time[] = $data['sorting_fields']['clock'] === ZBX_SORT_UP ? clone $sort_div_asc : clone $sort_div_desc;
				}

				$header_time = new CColHeader($header_time);

			
				$header[] = $header_time;
			}
	
			break;
	
		case 'show_host':
			if($show_host)
			{
				$column = [$column_host_label];

				if (array_key_exists('host', $data['sorting_fields'])) {
					$column[] =  $data['sorting_fields']['host'] === ZBX_SORT_UP ? clone $sort_div_asc : clone $sort_div_desc;
				}

				$column = new CColHeader($column);

				$column->addClass('column-host-'. $widget_instance_id);

				$header[] = $column;
			}

			break;

		case 'show_problem_and_severity':
			if($show_problem_and_severity)
			{
				$column = [$column_problem_and_severity_label];

				if (array_key_exists('name', $data['sorting_fields'])) 
				{
					$column[] = $data['sorting_fields']['name'] === ZBX_SORT_UP ? clone $sort_div_asc : clone $sort_div_desc;
				}
			
				if (array_key_exists('severity', $data['sorting_fields'])) 
				{
					$column[] = ' &bullet; ';
					$column[] = $data['sorting_fields']['severity'] === ZBX_SORT_UP ? clone $sort_div_asc : clone $sort_div_desc;
				}

				$column = new CColHeader($column);

				$column->addClass('column-problem-and-severity-'. $widget_instance_id);

				$header[] = $column;
			}

			break;		

		case 'show_event_actions_icons':
			if($show_event_actions_icons)
			{
				$column = new CColHeader($column_event_actions_icons_label);

				$column->addClass('column-event-actions-icons-'. $widget_instance_id);

				$header[] = $column;
			}

			break;			

		case 'show_tags':
			if($show_tags)
			{
				$column = new CColHeader($column_tags_label);

				$column->addClass('column-tags-'. $widget_instance_id);

				$header[] = $column;
			}

			break;
	}

	$table->setHeader($header);
}

// Table row
foreach ($data['data']['problems'] as $eventid => $problem)
{
	// Table Row
	$row = [];

	foreach(explode(',', $columns_order) as $column)
	{
		switch($column)
		{
			case 'show_time':
				if($show_time)
				{
					$time_value = new CCol();

					$time_value->addItem(zbx_date2str(DATE_TIME_FORMAT_SECONDS, $problem['clock']));
					$time_value->addClass(ZBX_STYLE_NOWRAP);			
					$time_value->addClass('column-time-'. $widget_instance_id);
					
					$row[] = $time_value;
				}

				break;

			case 'show_host':
				if($show_host)
				{
					$objectId = $problem['objectid'];
					$hostList = $data['data']['triggers_hosts'][$objectId];

					$hostNames = "";

					foreach($hostList as $hostName)
					{
						$hostNames .= $hostName['name'] . ' ';
					}

					$host_value = new CCol();
					$host_value->addItem($hostNames);
					$host_value->addClass('column-host-'. $widget_instance_id);
				
					$row[] = $host_value;
				}

				break;

			case 'show_problem_and_severity':
				if($show_problem_and_severity)
				{
					$problem_and_severity = new CCol($problem['name']);
										
					$is_acknowledged = ($problem['acknowledged'] == EVENT_ACKNOWLEDGED);
					
					if (($is_acknowledged && $data['config']['problem_ack_style'])
					|| (!$is_acknowledged && $data['config']['problem_unack_style']))
					{
						// blinking
						$duration = time() - $problem['clock'];
						$blink_period = timeUnitToSeconds($data['config']['blink_period']);
						
						if ($blink_period != 0 && $duration < $blink_period) {
							$problem_and_severity
							->addClass('blink')
							->setAttribute('data-time-to-blink', $blink_period - $duration)
							->setAttribute('data-toggle-class', ZBX_STYLE_BLINK_HIDDEN);
						}
					}
					
					$problem_and_severity->addClass('column-problem-and-severity-'. $widget_instance_id);
					
					$row[] = $problem_and_severity;
				}
				
				break;

			case 'show_event_actions_icons':
				if($show_event_actions_icons)
				{
					$event_actions_icons = makeEventActionsIcons($problem['eventid'], $data['data']['actions'], $data['data']['users']);
					
					// Event actions icons exists
					if($event_actions_icons)
					{
						$event_actions_icons->addClass('column-event-actions-icons-'. $widget_instance_id);
					}
					
					// Event actions icons does not exists - place an empty column
					if(!$event_actions_icons)
					{
						$event_actions_icons = new CCol();
					}

					$row[] = $event_actions_icons;
				}

				break;		

			case 'show_tags':
				if($show_tags)
				{
					$tags_value = new CCol();
					
					if(array_key_exists('tags', $data['data']))
					{
						$tags_value->addItem($data['data']['tags'][$problem['eventid']]);
						$tags_value->addClass('column-tags-'. $widget_instance_id);
					}

					$row[] = $tags_value;
				}
				
				break;	
		}
	}

	$table->addRow(
		(new CRow($row))
			->addClass($problem['acknowledged'] == EVENT_ACKNOWLEDGED ? CSeverityHelper::getStyle((int) $problem['severity']) . '-acknowledged-' . $widget_instance_id : CSeverityHelper::getStyle((int) $problem['severity']) .'-'. $widget_instance_id)
			->setAttribute('data-uniqueid', $problem['eventid'])
	);
}

// Table footer
if ($data['info'] !== '')
{
	$table->setFooter([
		(new CCol($data['info']))
			->setColSpan($table->getNumCols())
			->addClass(ZBX_STYLE_LIST_TABLE_FOOTER)
	]);
}

// Output
$output['name'] = $data['name'];
$output['body'] = '';

// Cascading styles
$output['body'] .= $css->toString();

// Summary table
if($show_summary_row)
{	
	$summary = new CTableInfo();

	// Summary Description column
	$summary_description_col = new CCol($summary_row_description);
	
	// Summary Severities count column
	$summary_severities_count_col = new CCol();

	if($summary_row_display_severities_count)
	{
		if($summary_row_display_severities_count == 1) // Severity nonack only
		{
			$severity_ok = $data['severity_nonack_count'][ZBX_SEVERITY_OK];
			$severity_not_classified = $data['severity_nonack_count'][TRIGGER_SEVERITY_NOT_CLASSIFIED];
			$severity_information = $data['severity_nonack_count'][TRIGGER_SEVERITY_INFORMATION];
			$severity_warning = $data['severity_nonack_count'][TRIGGER_SEVERITY_WARNING];
			$severity_average = $data['severity_nonack_count'][TRIGGER_SEVERITY_AVERAGE];
			$severity_high = $data['severity_nonack_count'][TRIGGER_SEVERITY_HIGH];
			$severity_disaster = $data['severity_nonack_count'][TRIGGER_SEVERITY_DISASTER];
		}

		if($summary_row_display_severities_count == 2) // Severity ack only
		{
			$severity_ok = $data['severity_ack_count'][ZBX_SEVERITY_OK];
			$severity_not_classified = $data['severity_ack_count'][TRIGGER_SEVERITY_NOT_CLASSIFIED];
			$severity_information = $data['severity_ack_count'][TRIGGER_SEVERITY_INFORMATION];
			$severity_warning = $data['severity_ack_count'][TRIGGER_SEVERITY_WARNING];
			$severity_average = $data['severity_ack_count'][TRIGGER_SEVERITY_AVERAGE];
			$severity_high = $data['severity_ack_count'][TRIGGER_SEVERITY_HIGH];
			$severity_disaster = $data['severity_ack_count'][TRIGGER_SEVERITY_DISASTER];
		}

		if($summary_row_display_severities_count == 3) // Severity nonack + ack
		{
			$severity_ok = $data['severity_ack_count'][ZBX_SEVERITY_OK] + $data['severity_nonack_count'][ZBX_SEVERITY_OK];
			$severity_not_classified = $data['severity_ack_count'][TRIGGER_SEVERITY_NOT_CLASSIFIED] + $data['severity_nonack_count'][TRIGGER_SEVERITY_NOT_CLASSIFIED];
			$severity_information = $data['severity_ack_count'][TRIGGER_SEVERITY_INFORMATION] + $data['severity_nonack_count'][TRIGGER_SEVERITY_INFORMATION];
			$severity_warning = $data['severity_ack_count'][TRIGGER_SEVERITY_WARNING] + $data['severity_nonack_count'][TRIGGER_SEVERITY_WARNING];
			$severity_average = $data['severity_ack_count'][TRIGGER_SEVERITY_AVERAGE] + $data['severity_nonack_count'][TRIGGER_SEVERITY_AVERAGE];
			$severity_high = $data['severity_ack_count'][TRIGGER_SEVERITY_HIGH] + $data['severity_nonack_count'][TRIGGER_SEVERITY_HIGH];
			$severity_disaster = $data['severity_ack_count'][TRIGGER_SEVERITY_DISASTER] + $data['severity_nonack_count'][TRIGGER_SEVERITY_DISASTER];
		}

		$severity_ok_span = new CSpan($severity_ok);
		$severity_not_classified_span = new CSpan($severity_not_classified);
		$severity_information_span = new CSpan($severity_information);
		$severity_warning_span = new CSpan($severity_warning);
		$severity_average_span = new CSpan($severity_average);
		$severity_high_span = new CSpan($severity_high);
		$severity_disaster_span = new CSpan($severity_disaster);

		$severity_ok_span->addClass(CSeverityHelper::getStyle(ZBX_SEVERITY_OK));
		$severity_not_classified_span->addClass(CSeverityHelper::getStyle(TRIGGER_SEVERITY_NOT_CLASSIFIED));
		$severity_information_span->addClass(CSeverityHelper::getStyle(TRIGGER_SEVERITY_INFORMATION));
		$severity_warning_span->addClass(CSeverityHelper::getStyle(TRIGGER_SEVERITY_WARNING));
		$severity_average_span->addClass(CSeverityHelper::getStyle(TRIGGER_SEVERITY_AVERAGE));
		$severity_high_span->addClass(CSeverityHelper::getStyle(TRIGGER_SEVERITY_HIGH));
		$severity_disaster_span->addClass(CSeverityHelper::getStyle(TRIGGER_SEVERITY_DISASTER));

		$severity_ok_span->addClass('problem-icon-list-item');
		$severity_not_classified_span->addClass('problem-icon-list-item');
		$severity_information_span->addClass('problem-icon-list-item');
		$severity_warning_span->addClass('problem-icon-list-item');
		$severity_average_span->addClass('problem-icon-list-item');
		$severity_high_span->addClass('problem-icon-list-item');
		$severity_disaster_span->addClass('problem-icon-list-item');

		$severity_ok_span->addClass('problem-icon-list-item-'. $widget_instance_id);
		$severity_not_classified_span->addClass('problem-icon-list-item-'. $widget_instance_id);
		$severity_information_span->addClass('problem-icon-list-item-'. $widget_instance_id);
		$severity_warning_span->addClass('problem-icon-list-item-'. $widget_instance_id);
		$severity_average_span->addClass('problem-icon-list-item-'. $widget_instance_id);
		$severity_high_span->addClass('problem-icon-list-item-'. $widget_instance_id);
		$severity_disaster_span->addClass('problem-icon-list-item-'. $widget_instance_id);
		
		// Display only non zero values
		$severities_count_list = [];
		
		if($severity_disaster != 0) $severities_count_list[] = $severity_disaster_span;
		if($severity_high != 0) $severities_count_list[] = $severity_high_span;
		if($severity_average != 0) $severities_count_list[] = $severity_average_span;
		if($severity_warning != 0) $severities_count_list[] = $severity_warning_span;
		if($severity_information != 0) $severities_count_list[] = $severity_information_span;
		if($severity_not_classified != 0) $severities_count_list[] = $severity_not_classified_span;
		if($severity_ok != 0) $severities_count_list[] = $severity_ok_span;	
	
		$summary_severities_count_col = new CCol((new CSpan($severities_count_list))->addClass('problem-icon-list'));
		$summary_severities_count_col->addClass('right');
	}

	// Summary Total count column
	$summary_total_count_col = new CCol();
	if($summary_row_display_total_events_count)
	{
		if($summary_row_display_total_events_count == 1) // Severity nonack only
		{
			$severity_total = $data['severity_nonack_total'];
		}

		if($summary_row_display_total_events_count == 2) // Severity ack only
		{
			$severity_total = $data['severity_ack_total'];
		}

		if($summary_row_display_total_events_count == 3) // Severity nonack + ack
		{
			$severity_total = $data['severity_ack_total'] + $data['severity_nonack_total'];
		}

		$severity_total_span = new CSpan('Total count:&nbsp;'. $severity_total);

		$severity_total_span->addStyle('background: black; color: white !important;');

		$severity_total_span->addClass('problem-icon-list-item');

		$severity_total_span->addClass('problem-icon-list-item-'. $widget_instance_id);
			
		$summary_total_count_col = new CCol((new CSpan($severity_total_span))->addClass('problem-icon-list'));
		$summary_total_count_col->addClass('right');		
	}

	// Compose Summary table
	$summary->addRow(new CRow([
		$summary_description_col, 
		$summary_severities_count_col,
		$summary_total_count_col
	]));

	$output['body'] .= $summary->toString();
}

// Main table
$output['body'] .= $table->toString();

if (($messages = getMessages()) !== null)
{
	$output['messages'] = $messages->toString();
}

if ($data['user']['debug_mode'] == GROUP_DEBUG_MODE_ENABLED)
{
	CProfiler::getInstance()->stop();
	$output['debug'] = CProfiler::getInstance()->make()->toString();
}

echo json_encode($output);
