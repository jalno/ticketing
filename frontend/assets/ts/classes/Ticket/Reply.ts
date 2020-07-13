import "@jalno/translator";
import "bootstrap-inputmsg";
import * as $ from "jquery";
import "jquery.growl";
import { webuilder } from "webuilder";

export default class Reply {
	public static init() {
		Reply.runSubmitFormListener();
	}
	public static initIfNeeded() {
		if (Reply.$form.length) {
			Reply.init();
		}
	}

	private static $form = $("#ticket-reply");

	private static runSubmitFormListener() {
		const $selectSendType = $(".select-send-type", Reply.$form);
		if ($selectSendType.length) {
			$("a.send-type").on("click", function(e) {
				e.preventDefault();
				const $this = $(this);
				const $sendTypeInput = $("input[name=send_without_notification]", Reply.$form);
				if ($this.hasClass("with-notification")) {
					$sendTypeInput.val("");
				} else if ($this.hasClass("without-notification")) {
					$sendTypeInput.val("true");
				}
				Reply.$form.submit();
			});
		}
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
