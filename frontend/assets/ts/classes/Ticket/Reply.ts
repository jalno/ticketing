import "@jalno/translator";
import "bootstrap-inputmsg";
import * as $ from "jquery";
import "jquery.growl";
import { webuilder } from "webuilder";

export default class Reply {
	public static init() {
		Reply.runSubmitFormListener();
		if ($("input[type=file]", Reply.$form).prop("disabled")) {
			$(".btn-file2", Reply.$form).addClass("disabled");
		}
	}
	public static initIfNeeded() {
		if (Reply.$form.length) {
			Reply.init();
		}
	}

	private static $form = $("#ticket-reply");

	private static runSubmitFormListener() {
		Reply.$form.on("submit", function(e) {
			e.preventDefault();
			$(this).formAjax({
				data: new FormData(this as HTMLFormElement),
				contentType: false,
				processData: false,
				success: (data: webuilder.AjaxResponse) => {
					$.growl.notice({
						title: t("ticketing.request.response.successful"),
						message: t("ticketing.request.response.successful.message.sent"),
					});
					window.location.href = data.redirect;
				},
				error: (error: webuilder.AjaxError) => {
					if (error.error === "data_duplicate" || error.error === "data_validation") {
						const $input = $(`[name="${error.input}"]`);
						const params = {
							title: t("ticketing.request.response.error"),
							message: "",
						};
						if (error.error === "data_validation") {
							params.message = t("ticketing.request.response.error.message.data_validation");
							if (error.input && new RegExp(/^file\[[0-9]\]/).test(error.input)) {
								params.message = t("ticketing.request.response.error.message.data_validation.file");
							}
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
