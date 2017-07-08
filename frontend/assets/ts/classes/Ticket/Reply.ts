import * as $ from "jquery";
import { webuilder } from "webuilder";
import "jquery.growl";
import "bootstrap-inputmsg";
export default class Reply{
	private static $form = $('#ticket-reply');
	private static runSubmitFormListener(){
		Reply.$form.on('submit', function(e){
			e.preventDefault();
			$(this).formAjax({
				data: new FormData(this as HTMLFormElement),
				contentType: false,
				processData: false,
				success: (data: webuilder.AjaxResponse) => {
					$.growl.notice({
						title:"موفق",
						message:"ارسال شد ."
					});
					window.location.href = data.redirect;
				},
				error: function(error:webuilder.AjaxError){
					if(error.error == 'data_duplicate' || error.error == 'data_validation'){
						let $input = $('[name='+error.input+']');
						let $params = {
							title: 'خطا',
							message:''
						};
						if(error.error == 'data_validation'){
							$params.message = 'داده وارد شده معتبر نیست';
						}
						if($input.length){
							$input.inputMsg($params);
						}else{
							$.growl.error($params);
						}
					}else{
						$.growl.error({
							title:"خطا",
							message:'درخواست شما توسط سرور قبول نشد'
						});
					}
				}
			});
		});
	}
	public static init(){
		Reply.runSubmitFormListener();
		if($('input[type=file]', Reply.$form).prop('disabled')){
			$('.btn-file2', Reply.$form).addClass('disabled');
		}
	}
	public static initIfNeeded(){
		if(Reply.$form.length){
			Reply.init();
		}
	}
}