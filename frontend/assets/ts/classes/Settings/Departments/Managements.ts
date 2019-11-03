import "@jalno/translator";
import * as $ from "jquery";
import "ion-rangeslider";
import { AjaxRequest, Router , webuilder } from "webuilder";
import "jquery.growl";
import "bootstrap-inputmsg";
export default class Managements{
	private static $form = $('#settings-departmetns-management');
	private static disableListener($tr:JQuery, disable:boolean) {
		const slider = $(".slider", $tr).data("ionRangeSlider");
		slider.update({disable: disable});
		$("input[name*=start]").val(slider.result.from);
		$("input[name*=end]").val(slider.result.to);
	}
	private static runjQRangeSlider(){
		$(".slider", Managements.$form).each(function(){
			const day: number = $(this).data('day');
			const startWorkTime = $("input[name='day[" + day + "][worktime][start]']");
			const endWorkTime = $("input[name='day[" + day + "][worktime][end]']");
			const from = parseInt(startWorkTime.val() as string);
			const to = parseInt(endWorkTime.val() as string);
			const disabled = !$("input[name='day[" + day + "][enable]']").prop('checked');
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
				prefix: t("ticketing.ion_range_slider.prefix.hour"),
				postfix: t("ticketing.ion_range_slider.postfix.hour")
			});
			Managements.disableListener($(this).parents('tr'), disabled);
		});
	}
	private static EnabledjQRangeSlider(){
		$(".panel-day-works input[type=checkbox]", Managements.$form).on('change', function(){
			const tr = $(this).parents('tr');
			const disabled = (!$(this).prop('checked'));
			Managements.disableListener(tr, disabled);
		});
	}
	private static runSubmitFormListener(){
		Managements.$form.on('submit', function(e){
			e.preventDefault();
			$(this).formAjax({
				data: new FormData(this as HTMLFormElement),
				contentType: false,
				processData: false,
				success: (data: webuilder.AjaxResponse) => {
					$.growl.notice({
						title: t("ticketing.request.response.successful"),
						message: t("ticketing.request.response.successful.message.department"),
					});
					if(data.redirect){
						window.location.href = data.redirect;
					}
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
		Managements.runjQRangeSlider();
		Managements.EnabledjQRangeSlider();
		Managements.runSubmitFormListener();
		Managements.AllUserSelectListener();
	}
	public static initIfNeeded(){
		if(Managements.$form.length){
			Managements.init();
		}
	}
	protected static AllUserSelectListener() {
		const $panel = $(".panel.panel-users", Managements.$form);
		$('input[name="allUsers"]', $panel).on("change", function() {
			const $users = $(".panel-body input", $panel);
			$users.prop("disabled", $(this).prop("checked"))
		});
	}
}