import "@jalno/translator";
import * as $ from "jquery";
import {webuilder} from "webuilder";
export default class Close{
	private static $form = $('.ticket-close .form-horizontal');
	private static $ticket = Close.$form.data('ticket');
	private static runSubmitFormListener(){
		Close.$form.on('submit', function(e){
			e.preventDefault();
			$(this).formAjax({
				success: (data: webuilder.AjaxResponse) => {
					$.growl.notice({
						title: t("ticketing.request.response.successful"),
						message: t("ticketing.request.response.successful.message"),
					});
					window.location.href = data.redirect;
				},
				error: function(error:webuilder.AjaxError){
					if(error.error == 'data_duplicate' || error.error == 'data_validation'){
						let $input = $('[name='+error.input+']');
						let $params = {
							title: t("ticketing.request.response.error"),
							message:''
						};
						if(error.error == 'data_validation'){
							$params.message = t("ticketing.request.response.error.message.data_validation");
						}
						if($input.length){
							$input.inputMsg($params);
						}else{
							$.growl.error($params);
						}
					}else{
						$.growl.error({
							title: t("ticketing.request.response.error"),
							message: t("ticketing.request.response.error.message"),
						});
					}
				}
			});
		});
	}
	public static init(){
		Close.runSubmitFormListener();
	}
	public static initIfNeeded(){
		if(Close.$form.length){
			Close.init();
		}
	}
}