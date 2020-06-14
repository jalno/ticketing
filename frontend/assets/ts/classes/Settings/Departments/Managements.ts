import "@jalno/translator";
import "bootstrap-inputmsg";
import "ion-rangeslider";
import * as $ from "jquery";
import "jquery.growl";
import { AjaxRequest, Router , webuilder } from "webuilder";

export default class Managements {
	public static initIfNeeded() {
		if (Managements.$form.length) {
			Managements.init();
		}
	}
	public static init() {
		Managements.runjQRangeSlider();
		Managements.EnabledjQRangeSlider();
		Managements.runSubmitFormListener();
		Managements.AllUserSelectListener();
	}
	protected static AllUserSelectListener() {
		const $panel = $(".panel.panel-users", Managements.$form);
		$('input[name="allUsers"]', $panel).on("change", function() {
			const $users = $(".panel-body input", $panel);
			$users.prop("disabled", $(this).prop("checked"));
		});
	}

	private static $form = $("#settings-departmetns-management");

	private static disableListener($tr: JQuery, disable: boolean) {
		const slider = $(".slider", $tr).data("ionRangeSlider");
		slider.update({disable: disable});
		$("input[name*=start]").val(slider.result.from);
		$("input[name*=end]").val(slider.result.to);
	}
	private static runjQRangeSlider() {
		$(".slider", Managements.$form).each(function() {
			const day: number = $(this).data("day");
			const startWorkTime = $("input[name='day[" + day + "][worktime][start]']");
			const endWorkTime = $("input[name='day[" + day + "][worktime][end]']");
			const from = parseInt(startWorkTime.val() as string, 10);
			const to = parseInt(endWorkTime.val() as string, 10);
			const disabled = !$("input[name='day[" + day + "][enable]']").prop("checked");
			function valuesChangingListener(obj: IonRangeSliderEvent) {
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
				postfix: t("ticketing.ion_range_slider.postfix.hour"),
			});
			Managements.disableListener($(this).parents("tr"), disabled);
		});
	}
	private static EnabledjQRangeSlider() {
		$(".panel-day-works input[type=checkbox]", Managements.$form).on("change", function() {
			const tr = $(this).parents("tr");
			const disabled = (!$(this).prop("checked"));
			Managements.disableListener(tr, disabled);
		});
	}
	private static runSubmitFormListener() {
		Managements.$form.on("submit", function(e) {
			e.preventDefault();
			($(this) as any).formAjax({
				data: new FormData(this as HTMLFormElement),
				contentType: false,
				processData: false,
				success: (data: webuilder.AjaxResponse) => {
					$.growl.notice({
						title: t("ticketing.request.response.successful"),
						message: t("ticketing.request.response.successful.message.department"),
					});
					if (data.redirect) {
						window.location.href = data.redirect;
					}
				},
				error: (error: webuilder.AjaxError) => {
					if (error.error === "data_duplicate" || error.error === "data_validation") {
						const $input = $(`name=["${error.input}"]`);
						const params = {
							title: t("ticketing.request.response.error"),
							message: "",
						};
						if (error.error === "data_validation") {
							params.message = t("ticketing.request.response.error.message.data_validation");
						}
						if ($input.length) {
							$input.inputMsg(params);
						} else {
							$.growl.error(params);
						}
					} else {
						$.growl.error({
							title: t("ticketing.request.response.error"),
							message: t("ticketing.request.response.error.message"),
						});
					}
				},
			});
		});
	}
}
