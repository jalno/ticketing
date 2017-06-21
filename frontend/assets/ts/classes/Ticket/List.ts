import * as $ from "jquery";
import "../jquery.userAutoComplete";
export default class List{
	private static $form = $('#ticketSearch');
	private static runUserSearch(){
		$('input[name=client_name]', List.$form).userAutoComplete();
	}
	public static init(){
		if($('input[name=client_name]', List.$form).length){
			List.runUserSearch();
		}
	}
	public static initIfNeeded(){
		if(List.$form.length){
			List.init();
		}
	}
}