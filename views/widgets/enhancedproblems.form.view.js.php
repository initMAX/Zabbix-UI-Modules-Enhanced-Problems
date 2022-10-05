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
 * @var array $data
 */
?>

window.widget_configuration_form = new class {

	init(options) {
		this._form = document.getElementById(options.form_id);
		this._columns_list = document.getElementById(options.columns_table);

		// Remove default 'show header' checkbox column to do not interfere with custom one.
		this._form.querySelector('.form-field.form-field-show-header')?.remove();

		// Columns selection and order sortable init.
		this.initSortable(this._columns_list);
		jQuery(this._columns_list).on('change', '[type="checkbox"]', e => {
			if (e.target.checked) {
				jQuery(e.target).closest('tr').find('[type="text"]').removeAttr('readonly');
			}
			else {
				jQuery(e.target).closest('tr').find('[type="text"]').attr('readonly', true);
			}

			this.updateWidthSum();
		});
		jQuery(this._columns_list).find('[type="checkbox"]').trigger('change');
		jQuery(this._columns_list).on('change blur', '.js_column_width', e => {
			this.updateWidthSum();
		});
		this.updateWidthSum();

		// Summary Row
		jQuery('[name="show_summary_row"]', this._form).change(e => this.toggleSummaryRowFields(e.target.checked));

		// Host based filtering
		jQuery('[name="host_based_filtering"]', this._form).change(e => this.toggleHostBasedFilteringsFields(e.target.checked));

		// Second level sorting
		jQuery('[name="second_level_sorting_enabled"]', this._form).change(e => this.toggleSecondLevelSortingFields(e.target.checked));
	}

	updateWidthSum() {
		let sum = 0;

		jQuery('[type="checkbox"]:checked', this._columns_list)
				.closest('tr')
				.find('.js_column_width')
				.map((i, elm) => {
					sum += parseInt(elm.value, 10);
				});

		jQuery('#js_width_summary').text(sum + ' %');
	}

	toggleSummaryRowFields(visible) {
		jQuery('#enhanced-problem-summary-row-fields', this._form).toggleClass('display-none', !visible);
	}

	toggleHostBasedFilteringsFields(visible) {
		jQuery('#js_groupids_field', this._form).toggleClass('display-none', !visible);
		jQuery('#js_exclude_groupids_field', this._form).toggleClass('display-none', !visible);
		jQuery('#js_hostids_field', this._form).toggleClass('display-none', !visible);
	}

	toggleSecondLevelSortingFields(visible) {
		jQuery('#second_level_sorting_column', this._form).toggleClass('display-none', !visible);
		jQuery('#second_level_sorting_order', this._form).toggleClass('display-none', !visible);
		
		jQuery('#third_level_sorting_info', this._form).toggleClass('display-none', !visible);
	}

	initSortable(element) {
		$(element).sortable({
			items: 'tbody tr.sortable',
			axis: 'y',
			containment: 'parent',
			cursor: 'grabbing',
			handle: '.td-drag-icon > div',
			tolerance: 'pointer',
			opacity: 0.6,
			helper: function(e, ui) {
				for (let td of ui.find('>td')) {
					let $td = $(td);
					$td.attr('width', $td.width())
				}

				// when dragging element on safari, it jumps out of the table
				if (SF) {
					// move back draggable element to proper position
					ui.css('left', (ui.offset().left - 2) + 'px');
				}

				return ui;
			},
			stop: function(e, ui) {
				ui.item.find('>td').removeAttr('width');
				ui.item.removeAttr('style');
			},
			start: function(e, ui) {
				$(ui.placeholder).height($(ui.helper).height());
			}
		});
	}
}();
