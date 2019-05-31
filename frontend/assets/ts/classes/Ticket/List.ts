import * as $ from "jquery";
import "../jquery.userAutoComplete";
export default class List{
	private static $form = $("#tickets-search");
	private static runUserSearch(){
		$("input[name=client_name]", List.$form).userAutoComplete();
	}
	public static init(){
		if($("input[name=client_name]", List.$form).length){
			List.runUserSearch();
		}
		List.openAdvancedSearchListener();
	}
	public static initIfNeeded(){
		if(List.$form.length){
			List.init();
		}
	}
	private static openAdvancedSearchListener() {
		const $fields = $(".more-field", List.$form);
		$(".btn.advanced-search", List.$form).on("click", (e) => {
			e.preventDefault();
			$fields.slideToggle();
		});
	}
}