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

$form = (new CForm())
	->cleanItems()
	->setId('acknowledge_form')
	->addVar('action', 'popup.acknowledge.create')
	->addVar('eventids', $data['eventids']);

$form_list = (new CFormList())
	->addRow(new CLabel(_('Problem')), (new CDiv($data['problem_name']))->addClass(ZBX_STYLE_WORDBREAK))
	->addRow(
		new CLabel(_('Message'), 'message'),
		(new CTextArea('message', $data['message']))
			->setWidth(ZBX_TEXTAREA_BIG_WIDTH)
			->setAttribute('maxlength', DB::getFieldLength('acknowledges', 'message'))
			->setEnabled($data['allowed_add_comments'])
	);

if (array_key_exists('history', $data))
{
 	$form_list->addRow(_('History'),
 		(new CDiv(makeEventHistoryTable($data['history'], $data['users'])))
 			->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR)
 			->setAttribute('style', 'min-width: '.ZBX_TEXTAREA_BIG_WIDTH.'px;')
 	);
}

$selected_events = count($data['eventids']);

if ($data['has_unack_events'])
{
	$form_list->addRow(_('Acknowledge'),
		(new CCheckBox('acknowledge_problem', ZBX_PROBLEM_UPDATE_ACKNOWLEDGE))
			->onChange("$('#unacknowledge_problem').prop('disabled', this.checked)")
			->setEnabled($data['allowed_acknowledge'])
	);
}

if ($data['has_ack_events'])
{
	$form_list->addRow(_('Unacknowledge'),
		(new CCheckBox('unacknowledge_problem', ZBX_PROBLEM_UPDATE_UNACKNOWLEDGE))
			->onChange("$('#acknowledge_problem').prop('disabled', this.checked)")
			->setEnabled($data['allowed_acknowledge'])
	);
}

$form->addItem($form_list);

$inline_js = <<<'JAVASCRIPT'
/**
 * @param {Overlay} overlay
 */
function submitAcknowledge(overlay) {
    var $form = overlay.$dialogue.find('form'),
        url = new Curl('zabbix.php', false),
        form_data;

    $form.trimValues(['#message']);
    form_data = jQuery('#message, input:visible, input[type=hidden]', $form).serialize();
    url.setArgument('action', 'popup.acknowledge.create');

    overlay.xhr = sendAjaxData(url.getUrl(), {
        data: form_data,
        dataType: 'json',
        method: 'POST',
        beforeSend: function() {
            overlay.setLoading();
        },
        complete: function() {
            overlay.unsetLoading();
        }
    }).done(function(response) {
        overlay.$dialogue.find('.msg-bad').remove();

        if ('errors' in response) {
            jQuery(response.errors).insertBefore($form);
        }
        else {
            overlayDialogueDestroy(overlay.dialogueid);
            $.publish('acknowledge.create', [response, overlay]);
        }
    });
}
JAVASCRIPT;

$output = [
	'header' => $data['title'],
	'body' => (new CDiv([$data['errors'], $form]))->toString(),
	'buttons' => [
		[
			'title' => _('Save'),
			'class' => '',
			'keepOpen' => true,
			'isSubmit' => true,
			'action' => 'return submitAcknowledge(overlay);'
		]
	],
	'script_inline' => $inline_js
];

if ($data['user']['debug_mode'] == GROUP_DEBUG_MODE_ENABLED)
{
	CProfiler::getInstance()->stop();
	$output['debug'] = CProfiler::getInstance()->make()->toString();
}

echo json_encode($output);
