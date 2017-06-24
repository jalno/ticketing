import * as $ from "jquery";
import "ion-rangeslider";
import { AjaxRequest, Router , webuilder } from "webuilder";
import "jquery.growl";
import "bootstrap-inputmsg";
export default class Managements{
	private static $form = $('#settings-departmetns-management');
	private static disableListener(tr:JQuery, disable:boolean){
		let slider = $(".slider", tr).data("ionRangeSlider");
		slider.update({disable: disable});
	}
	private static runjQRangeSlider(){
		$(".slider", Managements.$form).each(function(){
			let day:number = $(this).data('day');
			let startWorkTime:JQuery = $("input[name='day[" + day + "][worktime][start]']");
			let endWorkTime:JQuery = $("input[name='day[" + day + "][worktime][end]']");
			let from = parseInt(startWorkTime.val() as string);
			let to = parseInt(endWorkTime.val() as string);
			let disabled = !$("input[name='day[" + day + "][enable]']").prop('checked');
			function valuesChangingListener(obj: IonRangeSliderEvent){
				startWorkTime.val(obj.from);
				endWorkTime.val(obj.to);
			}
			$(this).ionRangeSlider({
				type: "double",
				grid: true,
				min: 0,
				max: 23,
				from: from,
				to: to,
				min_interval: 1,
				onChange: valuesChangingListener,
				prefix: 'ساعت '
			});
			Managements.disableListener($(this).parents('tr'), disabled);
		});
	}
	private static EnabledjQRangeSlider(){
		$("input[type=checkbox]", Managements.$form).on('change', function(){
			const tr = $(this).parents('tr');
			const disabled = (!$(this).prop('checked'));
			Managements.disableListener(tr, disabled);
		});
	}
	private static runSubmitFormListener = function(){
		Managements.$form.on('submit', function(e){
			e.preventDefault();
			$(this).formAjax({
				data: new FormData(this as HTMLFormElement),
				contentType: false,
				processData: false,
				success: (data: webuilder.AjaxResponse) => {
					$.growl.notice({
						title:"موفق",
						message:"اطلاعات دپارتمان ذخیره شد ."
					});
					if(data.redirect){
						window.location.href = data.redirect;
					}
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
		Managements.runjQRangeSlider();
		Managements.EnabledjQRangeSlider();
		Managements.runSubmitFormListener();
	}
	public static initIfNeeded(){
		if(Managements.$form.length){
			Managements.init();
		}
	}
}