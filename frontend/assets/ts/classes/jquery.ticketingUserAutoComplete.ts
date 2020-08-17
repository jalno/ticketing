// tslint:disable-next-line: no-reference
/// <reference path="jquery.ticketingUserAutoComplete.d.ts"/>

import * as $ from "jquery";
import "jquery-ui/ui/widgets/autocomplete.js";
import { Router , webuilder } from "webuilder";

export interface IUser {
	id: number;
	name: string;
	lastname: string;
	email: string;
	cellphone: string;
}
interface ISearchResponse extends webuilder.AjaxResponse {
	items: IUser[];
}

$.fn.ticketingUserAutoComplete = function($target?: JQuery, onSelect?: (event, ui: {item: IUser}) => void) {
	function getTraget($element: JQuery): JQuery {
		if (!$target) {
			const $form = $element.parents("form");
			let name = $element.attr("name");
			name = name.substr(0, name.length - 5);
			$target = $(`input[name="${name}"]`, $form);
		}
		return $target;
	}
	function select(event, ui: {item: IUser}): boolean {
		$(this).val(ui.item.name + (ui.item.lastname ? " " + ui.item.lastname : ""));
		getTraget($(this)).val(ui.item.id).data("user", ui.item).trigger("change");
		return false;
	}
	function unselect() {
		if ($(this).val() === "") {
			getTraget($(this)).val("").trigger("change");
		}
	}
	$(this).autocomplete({
		source: (request: {term: string}, response) => {
			$.ajax({
				url: Router.url("userpanel/users"),
				dataType: "json",
				data: {
					ajax: 1,
					word: request.term,
				},
				success: (data: ISearchResponse) => {
					if (data.status) {
						response(data.items);
					}
				},
			});
		},
		select: (onSelect ? (event, ui) => {onSelect(event, ui); return false; } : select),
		change: unselect,
		close: unselect,
		focus: select,
		create: function() {
			$(this).data("ui-autocomplete")._renderItem = (ul, item: IUser ) => {
				return $("<li>")
					.append(`<strong>${item.name + (item.lastname ? " " + item.lastname : "")}</strong><small class=ltr>${item.email}</small><small class="ltr">${item.cellphone}</small>`)
					.appendTo(ul);
			};
		},
	});
};
