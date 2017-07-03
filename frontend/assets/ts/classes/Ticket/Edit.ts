import * as $ from "jquery";
import "../jquery.userAutoComplete";
export default class Edit{
	private static $form = $('.ticket_edit .create_form');
	private static runUserSearch(){
		$('input[name=client_name]', Edit.$form).userAutoComplete();
	}
	public static init(){
		if($('input[name=client_name]', Edit.$form).length){
			Edit.runUserSearch();
		}
	}
	public static initIfNeeded(){
		if(Edit.$form.length){
			Edit.init();
		}
	}
}