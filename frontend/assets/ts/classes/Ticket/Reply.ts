import "@jalno/translator";
import "bootstrap-inputmsg";
import * as $ from "jquery";
import "jquery.growl";
import { webuilder } from "webuilder";
import Ticket from "../Ticket";
import IFormAjaxError from "../IFormAjaxError"

export default class Reply {
	public static initIfNeeded() {
		Reply.$form = $("#ticket-reply");
		if (Reply.$form.length) {
			Reply.init();
		}
	}
	public static init() {
		Reply.runSubmitFormListener();
		Ticket.runEnableDisableNotificationListener(Reply.$form);
		Ticket.runChangeFileInputListener(Reply.$form);
		Ticket.runTextareaAutosize(Reply.$form);
	}

	private static $form: JQuery;
	private static runSubmitFormListener() {
		const $progressBar = $("#progressBar", Reply.$form);
		const $removeFileIcons = $(".remove-file-icon", Reply.$form);
		Reply.$form.on("submit", function(e) {
			e.preventDefault();
			$(".has-error", Reply.$form).removeClass("has-error").children(".help-block").remove();
			$(this).formAjax({
				data: Ticket.appendFilesToFormData(new FormData(this as HTMLFormElement)),
				cache: false,
				contentType: false,
				processData: false,
				beforeSend: () => {
					$(".progress-bar-fill", Reply.$form).width('0%');
					$(".progress-bar-text", Reply.$form).html('0%');
					$removeFileIcons.removeClass("text-danger").addClass("text-info").html('<i class="fa fa-spinner fa-lg"></i>');
				},
				xhr: () => {
					const xhr = new XMLHttpRequest();
					xhr.upload.addEventListener('progress', (evt) => {
						if (evt.lengthComputable) {
							$progressBar.show();
							const percentComplete = ((evt.loaded / evt.total) * 100);
							$(".progress-bar-fill", Reply.$form).width(percentComplete + "%");
							$(".progress-bar-text", Reply.$form).html(percentComplete + "%");
						}
					}, false);
					return xhr;
				},
				success: (data: webuilder.AjaxResponse) => {
					$.growl.notice({
						title: t("ticketing.request.response.successful"),
						message: t("ticketing.request.response.successful.message.sent"),
					});
					window.location.href = data.redirect;
				},
				error: (error: IFormAjaxError) => {
					$removeFileIcons.addClass("text-danger").removeClass("text-info").html('<i class="fa fa-times-circle fa-lg"></i>');
					$progressBar.hide();
					const params: growl.Options = {
						title: t("ticketing.request.response.error"),
						message: t("ticketing.request.response.error.message"),
					};
					if (error.error === "data_duplicate" || error.error === "data_validation") {
						const $input = $(`[name="${error.input}"]`);
						params.message = t(`ticketing.request.response.error.message.${error.error}`);

						const fileRegex = /^file\[([0-9])\]/;
						if (error.error == "data_validation" || fileRegex.test(error.input)) {
							params.message = t("ticketing.request.response.error.message.data_validation.file");
							const index = error.input.match(fileRegex)[1];
							const $file = $("#attachmentsContent .upload-file-container", Reply.$form).eq(parseInt(index, 10));
							$file.addClass("has-error").append(`<span class="help-block text-center">${params.message}</span>`);
							return;
						}
						if ($input.length) {
							$input.inputMsg(params);
							return;
						}
					} else if (error.message) {
						params.message = error.message;
					} else if (error.code) {
						params.message = t(`error.${error.code}`);
					}
					$.growl.error(params);
				},
			});
		});
	}
}
