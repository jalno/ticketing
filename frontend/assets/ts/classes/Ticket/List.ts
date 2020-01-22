import * as $ from "jquery";
import "../jquery.userAutoComplete";
import "select2";
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
		List.initSelect2();
		List.runSubmitFormListener();
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
	private static initSelect2() {
		$("select[name=status_select]", List.$form).select2({
			multiple: true,
			allowClear: true,
			theme: "bootstrap",
			dir: $("body").hasClass("rtl") ? "rtl" : "ltr",
		});
	}
	private static runSubmitFormListener() {
		List.$form.on('submit', function (e) {
			const status = $("select[name=status_select]", List.$form).val() as Array<string>;
			$("input[name=status]", List.$form).val(status.join(","));
		});
	}
}
