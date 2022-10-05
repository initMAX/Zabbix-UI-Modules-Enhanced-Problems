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

use CControllerWidget;
use CControllerResponseData;
use CRoleHelper;
use CArrayHelper;
use CScreenProblem;
use CSettingsHelper;

class EnhancedProblemsView extends CControllerWidget
{

	public function __construct()
	{
		parent::__construct();

		$this->setType(EnhancedProblemsForm::WIDGET_TYPE);
		$this->setValidationRules([
			'name' => 'string',
			'fields' => 'json',
			'initial_load' => 'in 0,1'
		]);
	}

	protected function doAction()
	{
		$fields = $this->getForm()->getFieldsData();

		$data = CScreenProblem::getData([
			'show' => TRIGGERS_OPTION_IN_PROBLEM,
			'groupids' => $fields['groupids'],
			'exclude_groupids' => $fields['exclude_groupids'],
			'hostids' => $fields['hostids'],
			'name' => $fields['problem'],
			'severities' => $fields['severities'],
			'evaltype' => $fields['evaltype'],
			'tags' => $fields['tags'],
			'show_suppressed' => ZBX_PROBLEM_SUPPRESSED_FALSE,
			'unacknowledged' => $fields['unacknowledged'],
			'show_opdata' => OPERATIONAL_DATA_SHOW_NONE
		]);

		// Add more informations to data
		$data = CScreenProblem::makeData($data, [
			'show' => TRIGGERS_OPTION_IN_PROBLEM,
			'details' => 0,
			'show_opdata' => OPERATIONAL_DATA_SHOW_NONE
		]);

		// Count severities
		$severity_nonack_count = [
			ZBX_SEVERITY_OK => 0,
			TRIGGER_SEVERITY_NOT_CLASSIFIED => 0,
			TRIGGER_SEVERITY_INFORMATION => 0,
			TRIGGER_SEVERITY_WARNING => 0,
			TRIGGER_SEVERITY_AVERAGE => 0,
			TRIGGER_SEVERITY_HIGH => 0,
			TRIGGER_SEVERITY_DISASTER => 0
		];

		$severity_ack_count = [
			ZBX_SEVERITY_OK => 0,
			TRIGGER_SEVERITY_NOT_CLASSIFIED => 0,
			TRIGGER_SEVERITY_INFORMATION => 0,
			TRIGGER_SEVERITY_WARNING => 0,
			TRIGGER_SEVERITY_AVERAGE => 0,
			TRIGGER_SEVERITY_HIGH => 0,
			TRIGGER_SEVERITY_DISASTER => 0
		];

		foreach($data['problems'] as $problem)
		{
			if($problem['acknowledged'] == 0)
			{
				$severity_nonack_count[$problem['severity']]++;
			}
			else
			{
				$severity_ack_count[$problem['severity']]++;	
			}

		}

		unset($problem);

		$severity_nonack_total = array_sum($severity_nonack_count);
		$severity_ack_total = array_sum($severity_ack_count);

		// Sort data
		$data = $this->sortData($data, $fields);

		// Check if count of problems is above allowed line count limit
		if (count($data['problems']) > $fields['show_lines'])
		{
			$info = _n('%1$d of %3$d%2$s problem is shown', '%1$d of %3$d%2$s problems are shown',
				min($fields['show_lines'], count($data['problems'])),
				(count($data['problems']) > CSettingsHelper::get(CSettingsHelper::SEARCH_LIMIT)) ? '+' : '',
				min(CSettingsHelper::get(CSettingsHelper::SEARCH_LIMIT), count($data['problems']))
			);
		}
		else
		{
			$info = '';
		}

		// Stripe problems above the lines count limit
		$data['problems'] = array_slice($data['problems'], 0, $fields['show_lines'], true);

		// Add tags
		if ($fields['show_tags_count'])
		{
			$data['tags'] = makeTags($data['problems'], true, 'eventid', $fields['show_tags_count'], $fields['tags'], null,
				$fields['tag_name_format'], $fields['tag_priority']
			);
		}

		if ($data['problems']) {
			$data['triggers_hosts'] = getTriggersHostsList($data['triggers']);
		}
	
		$this->setResponse(new CControllerResponseData([
			'name' => $this->getInput('name', $this->getDefaultName()),
			'initial_load' => (bool) $this->getInput('initial_load', 0),
			'fields' => [
				'show_lines' => $fields['show_lines'],
				'show_tags_count' => $fields['show_tags_count'],
				'tags' => $fields['tags'],
				'tag_name_format' => $fields['tag_name_format'],
				'tag_priority' => $fields['tag_priority'],
				'show_summary_row' => $fields['show_summary_row'],
				'summary_row_description' => $fields['summary_row_description'],
				'summary_row_display_severities_count' => $fields['summary_row_display_severities_count'],
				'summary_row_display_total_events_count' => $fields['summary_row_display_total_events_count'],
				'columns_order' => $fields['columns_order'],
				'show_time' => $fields['show_time'],
				'show_host' => $fields['show_host'],
				'show_problem_and_severity' => $fields['show_problem_and_severity'],
				'show_event_actions_icons' => $fields['show_event_actions_icons'],
				'show_tags' => $fields['show_tags'],
				'column_time_label' => $fields['column_time_label'],
				'column_host_label' => $fields['column_host_label'],
				'column_problem_and_severity_label' => $fields['column_problem_and_severity_label'],
				'column_event_actions_icons_label' => $fields['column_event_actions_icons_label'],
				'column_tags_label' => $fields['column_tags_label'],
				'column_time_width' => $fields['column_time_width'],
				'column_host_width' => $fields['column_host_width'],
				'column_problem_and_severity_width' => $fields['column_problem_and_severity_width'],
				'column_event_actions_icons_width' => $fields['column_event_actions_icons_width'],
				'column_tags_width' => $fields['column_tags_width'],
				'acknowledge_problem_style' => $fields['acknowledge_problem_style'],
				'field_font' => $fields['field_font'],
				'field_fontsize' => $fields['field_fontsize']
			],
			'data' => $data,
			'severity_nonack_count' => $severity_nonack_count,
			'severity_ack_count' => $severity_ack_count,
			'severity_nonack_total' => $severity_nonack_total,
			'severity_ack_total' => $severity_ack_total,
			'info' => $info,
			'sorting_fields' => $data['sorting_fields'],
			'user' => [
				'debug_mode' => $this->getDebugMode()
			],
			'config' => [
				'problem_ack_style' => CSettingsHelper::get(CSettingsHelper::PROBLEM_ACK_STYLE),
				'problem_unack_style' => CSettingsHelper::get(CSettingsHelper::PROBLEM_UNACK_STYLE),
				'blink_period' => CSettingsHelper::get(CSettingsHelper::BLINK_PERIOD)
			],
			'allowed_ui_problems' => $this->checkAccess(CRoleHelper::UI_MONITORING_PROBLEMS),
			'allowed_add_comments' => $this->checkAccess(CRoleHelper::ACTIONS_ADD_PROBLEM_COMMENTS),
			'allowed_change_severity' => $this->checkAccess(CRoleHelper::ACTIONS_CHANGE_SEVERITY),
			'allowed_acknowledge' => $this->checkAccess(CRoleHelper::ACTIONS_ACKNOWLEDGE_PROBLEMS),
			'allowed_close' => $this->checkAccess(CRoleHelper::ACTIONS_CLOSE_PROBLEMS)
		]));
	}

	protected function sortData(array $data, array $fields): array 
	{
		if (!$data['problems'])
		{
			return $data;
		}

		$index_fieldname = ['clock', 'host', 'name', 'severity', 'acknowledged'];
		$sorting_columns = [
			['first_level_sorting_enabled', 'first_level_sorting_column', 'first_level_sorting_order'],
			['second_level_sorting_enabled', 'second_level_sorting_column', 'second_level_sorting_order']
		];
		$sort_fields = [];

		foreach ($sorting_columns as $sorting_column)
		{
			[$column_checkbox, $column_field, $column_order] = $sorting_column;

			if ($column_checkbox === 'first_level_sorting_enabled' || $fields[$column_checkbox])
			{
				$field = $index_fieldname[$fields[$column_field]];

				if (!in_array($field, array_column($sort_fields, 'field')))
				{
					$sort_fields[] = [
						'field' => $index_fieldname[$fields[$column_field]],
						'order' => $fields[$column_order] ? ZBX_SORT_DOWN : ZBX_SORT_UP
					];
				}
			}
		}

		if (in_array('acknowledged', array_column($sort_fields, 'field'))) 
		{
			foreach ($data['problems'] as &$problem)
			{
				$problem['acknowledged'] = $problem['acknowledges'] ? 1 : 0;
			}
			
			unset($problem);
		}

		$last_problem = end($data['problems']);

		$data['problems'] = array_slice($data['problems'], 0, CSettingsHelper::get(CSettingsHelper::SEARCH_LIMIT),
			true
		);

		$data['sorting_fields'] = array_column($sort_fields, 'order', 'field');

		if (array_key_exists('host', $data['sorting_fields']))
		{
			$triggers_hosts_list = [];

			foreach (getTriggersHostsList($data['triggers']) as $triggerid => $trigger_hosts)
			{
				$triggers_hosts_list[$triggerid] = implode(', ', array_column($trigger_hosts, 'name'));
			}

			foreach ($data['problems'] as &$problem)
			{
				$problem['host'] = $triggers_hosts_list[$problem['objectid']];
			}

			unset($problem);
		}

		if (array_key_exists('name', $data['sorting_fields']))
		{
			$sort_fields[] = ['field' => 'objectid', 'order' => $data['sorting_fields']['name']];
		}

		if (array_key_exists('clock', $data['sorting_fields']))
		{
			$sort_fields[] = ['field' => 'ns', 'order' => $data['sorting_fields']['clock']];
		}
		else
		{
			$sort_fields[] = ['field' => 'clock', 'order' => ZBX_SORT_DOWN];
			$sort_fields[] = ['field' => 'ns', 'order' => ZBX_SORT_DOWN];
		}

		if (array_key_exists('r_clock', $data['sorting_fields']))
		{
			$sort_fields[] = ['field' => 'r_ns', 'order' => $data['sorting_fields']['r_clock']];
		}

		CArrayHelper::sort($data['problems'], $sort_fields);

		$data['problems'][$last_problem['eventid']] = $last_problem;

		return $data;
	}

}
