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

const WIDGET_ENHANCED_PROBLEMS_CLICK = 'widget.enhanced-problems.click';

class CWidgetEnhancedProblems extends CWidget {

	_registerEvents() {
		super._registerEvents();

		// console.log(this);
		this.initTableRowSelction();

		this._events = {
			...this._events,

			acknowledgeCreated: (e, response) => {
				for (let i = overlays_stack.length - 1; i >= 0; i--) {
					const overlay = overlays_stack.getById(overlays_stack.stack[i]);

					if (overlay.type === 'hintbox') {
						const element = overlay.element instanceof jQuery ? overlay.element[0] : overlay.element;

						if (this._content_body.contains(element)) {
							hintBox.deleteHint(overlay.element);
						}
					}
				}

				clearMessages();

				addMessage(makeMessageBox('good', [], response.message, true, false));

				if (this._state === WIDGET_STATE_ACTIVE) {
					this._startUpdating();
				}
			},

			eventSelected: (e, data) => {
				if (!(e instanceof PointerEvent) && data.widgetid === this._widgetid) {
					// TODO: handle data.eventids adding/removing, this._last_selectedid
				}
				else if (!this._range_selection) {
					this._last_selectedid = null;
					this.clearSelectedRows();
				}

				this.highlightSelectedRows();
			},

			tableRowClick: e => {
				let row = e.target.closest('tr');

				if (row === null) {
					return;
				}

				$.publish(WIDGET_ENHANCED_PROBLEMS_CLICK, {widgetid: this._widgetid});

				let problemid = row.getAttribute('data-uniqueid');
				let is_selected = this._selectedRows.indexOf(problemid) != -1;

				if (!e.shiftKey && !e.ctrlKey) {
					this.clearSelectedRows();
				}
				else if (e.ctrlKey && is_selected && problemid === this._last_selectedid) {
					let problemids = [];

					for (const jsrow of row.closest('table').querySelectorAll('tr.js-row-selected')) {
						problemids.push(jsrow.getAttribute('data-uniqueid'));
					}

					let i = problemids.indexOf(problemid);

					if (i > 0) {
						this._last_selectedid = problemids[i - 1];
					}
					else if (problemids.length > 1) {
						this._last_selectedid = problemids[i + 1];
					}
				}

				if (e.shiftKey && this._last_selectedid !== null) {
					this.selectRowsRange(this._last_selectedid, problemid);
				}
				else if (is_selected) {
					this.removeRow(row);
				}
				else {
					this._last_selectedid = problemid;
					this.addRow(row);
				}

				this.highlightSelectedRows();
			},

			contextMenuOpen: e => {
				let row = e.target.closest('tr');

				if (row === null) {
					return;
				}

				let problemid = row.getAttribute('data-uniqueid');

				if (problemid === null) {
					return;
				}

				let is_selected = this._selectedRows.indexOf(problemid) != -1;

				if (!is_selected) {
					this._events.tableRowClick(e);
				}

				e.preventDefault();
				e.stopPropagation();

				jQuery(e.target).menuPopup(this.getContextMenu(), e, {
					position: {
						of: jQuery(e.target),
						my: 'left top',
						at: 'left+'+e.layerX+' top+'+e.layerY,
						collision: 'fit'
					}
				});

				// Change z-index of .menu-popup-overlay to allow multiple right button click
				let popup_overlay = document.querySelector('.menu-popup-overlay');

				if ('style' in popup_overlay) {
					popup_overlay.style.zIndex = 0;
				}
			},

			mouseMove: e => {
				let row = e.target.closest('tr');

				if (row === null || !this._range_selection) {
					return;
				}

				let currentid = row.getAttribute('data-uniqueid');

				if (currentid === null) {
					return;
				}

				if ((e.buttons&0x01) == 0) {
					this._range_selection = false;
					this._range_startid = currentid;
					return;
				}

				if (this._range_startid === null) {
					this._range_startid = currentid;
				}

				this.clearSelectedRows();
				this.selectRowsRange(this._range_startid, currentid);
				this.highlightSelectedRows();
			},

			mouseDown: e => {
				if (!this._range_selection && (e.buttons&0x01) != 0) {
					this._range_startid = e.target.closest('tr')?.getAttribute('data-uniqueid');
					this._range_selection = true;
				}
			}
		}
	}

	_activateEvents() {
		super._activateEvents();

		$.subscribe('acknowledge.create', this._events.acknowledgeCreated);
		$.subscribe(WIDGET_ENHANCED_PROBLEMS_CLICK, this._events.eventSelected);
		document.body.addEventListener('click', this._events.eventSelected);
		this._target.addEventListener('click', this._events.tableRowClick);
		this._target.addEventListener('contextmenu', this._events.contextMenuOpen);
		this._target.addEventListener('mousemove', this._events.mouseMove);
		this._target.addEventListener('mousedown', this._events.mouseDown);
	}

	_deactivateEvents() {
		super._deactivateEvents();

		$.unsubscribe('acknowledge.create', this._events.acknowledgeCreated);
		$.unsubscribe(WIDGET_ENHANCED_PROBLEMS_CLICK, this._events.eventSelected);
		document.body.removeEventListener('click', this._events.eventSelected);
		this._target.removeEventListener('click', this._events.tableRowClick);
		this._target.removeEventListener('contextmenu', this._events.contextMenuOpen);
		this._target.removeEventListener('mousemove', this._events.mouseMove);
		this._target.removeEventListener('mousedown', this._events.mouseDown);
	}

	_processUpdateResponse(response) {
		super._processUpdateResponse(response);
		this.highlightSelectedRows();
	}

	initTableRowSelction() {
		this._selectedRows = [];
		this._last_selectedid = null;
		this._range_selection = false;
		this._range_startid = null;
	}

	clearSelectedRows() {
		this._selectedRows = [];
	}

	addRow(row) {
		let problemid = row.getAttribute('data-uniqueid');

		this._selectedRows.push(problemid);
	}

	removeRow(row) {
		let problemid = row.getAttribute('data-uniqueid');

		this._selectedRows.splice(this._selectedRows.indexOf(problemid), 1);
	}

	selectRowsRange(range_start, range_end) {
		let row_problemid;
		let mark = false;

		for (const jsrow of this._target.querySelectorAll('table.js-problems-table tr')) {
			row_problemid = jsrow.getAttribute('data-uniqueid');

			if (row_problemid === range_start || row_problemid === range_end) {
				mark = !mark;

				if (mark === false && this._selectedRows.indexOf(row_problemid) === -1) {
					this._selectedRows.push(row_problemid);
				}
			}

			if (mark && this._selectedRows.indexOf(row_problemid) === -1) {
				this._selectedRows.push(row_problemid);
			}

			if (range_start === range_end) {
				mark = false;
			}
		}
	}

	highlightSelectedRows() {
		let table = this._target.querySelector('table.js-problems-table');
		let problemid;

		for (const jsrow of table.querySelectorAll('tr')) {
			problemid = jsrow.getAttribute('data-uniqueid');
			jsrow.classList.toggle('js-row-selected', problemid !== null && this._selectedRows.indexOf(problemid) != -1);
		}
	}

	getContextMenu() {
		let menu = [
			{
				label: t('Actions'),
				items: [
					{
						label: t('Acknowledge'),
						disabled: this._selectedRows.length === 0,
						clickCallback: () => {
							this.acknowledgePopUp({eventids: this._selectedRows}, this);
						}
					},
					{
						label: t('Unacknowledge'),
						disabled: this._selectedRows.length === 0,
						clickCallback: () => {
							this.unacknowledgePopUp({eventids: this._selectedRows}, this);
						}
					},
					{
						label: t('Create ticket in service desk'),
						disabled: this._selectedRows.length === 0,
						clickCallback: () => {
							this.journalPopUp({eventids: this._selectedRows}, this);
						}
					}
				]
			}
		];

		return menu;
	}

	acknowledgePopUp(parameters, trigger_element) {
		var url = new Curl('zabbix.php', false);
		var form_data;

		url.setArgument('action', 'popup.acknowledge.create');
		url.setArgument('eventids', parameters.eventids);
		url.setArgument('acknowledge_problem', 2);
		
		sendAjaxData(url.getUrl(), {
			data: form_data,
			dataType: 'json',
			method: 'POST'
		});

		// Refresh widget content...
		this._startUpdating();

		// Clear selected rows
		this.clearSelectedRows();
	}

	unacknowledgePopUp(parameters, trigger_element) {
		var url = new Curl('zabbix.php', false);
		var form_data;

		url.setArgument('action', 'popup.acknowledge.create');
		url.setArgument('eventids', parameters.eventids);
		url.setArgument('unacknowledge_problem', 16);
		
		sendAjaxData(url.getUrl(), {
			data: form_data,
			dataType: 'json',
			method: 'POST'
		});

		// Refresh widget content...
		this._startUpdating();

		// Clear selected rows
		this.clearSelectedRows();
	}

	journalPopUp(parameters, trigger_element) {
		var overlay = PopUp('widget.acknowledge.popup', parameters, {trigger_element});

		return overlay;
	}

	issuePopUp(parameters, trigger_element) {
		var overlay = PopUp('widget.enhancedproblems.issue.create', parameters, {trigger_element});

		return overlay;
	}
}
