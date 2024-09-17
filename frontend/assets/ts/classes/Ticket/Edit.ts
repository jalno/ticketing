import $ from "jquery";
import "../jquery.ticketingUserAutoComplete";

export default class Edit {
	public static init(){
		if ($("input[name=client_name]", Edit.$form).length) {
			Edit.runUserSearch();
		}
	}
	public static initIfNeeded() {
		if (Edit.$form.length) {
			Edit.init();
		}
	}

	private static $form = $(".ticket_edit .create_form");

	private static runUserSearch() {
		$("input[name=client_name]", Edit.$form).ticketingUserAutoComplete();
	}
}
