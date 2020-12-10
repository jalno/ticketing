import * as autosize from "autosize";
import "@jalno/translator";
import "bootstrap/js/modal";
import * as $ from "jquery";
import "jquery.growl";
import { AjaxRequest, Router , webuilder } from "webuilder";
import "./jquery.ticketOperatorAutoComplete";
import Add from "./Ticket/Add";
import Close from "./Ticket/Close";
import Edit from "./Ticket/Edit";
import List from "./Ticket/List";
import Reply from "./Ticket/Reply";

export default class Ticket {

	protected static finalFilesForUpload: File[] = [];

	public static init() {
		if ($("#ticket-close").length) {
			Ticket.closeTicketListener();
		}
		if ($("#ticket-inProgress").length) {
			Ticket.inProgressTicketListener();
		}
		if ($("#settings").length) {
			Ticket.editListener();
		}
	}
	public static initIfNeeded() {
		List.initIfNeeded();
		Add.initIfNeeded();
		Reply.initIfNeeded();
		Edit.initIfNeeded();
		Close.initIfNeeded();
		Ticket.init();
	}
	public static runEnableDisableNotificationListener($form: JQuery) {
		const $notificationBehavior = $(".btn-group-notification-behavior", $form);
		if (!$notificationBehavior.length) {
			return;
		}
		const $behaviorInput = $("input[name=send_notification]", $form);
		const $sendBtnIcon = $(".btn-send i", $notificationBehavior);
		$("a.notification-behavior", $notificationBehavior).on("click", function(e) {
			e.preventDefault();
			$behaviorInput.val($(this).hasClass("with-notification") ? 1 : 0);
			$sendBtnIcon.removeClass().addClass($(this).hasClass("with-notification") ? "fa fa-bell" : "fa fa-bell-slash");
		});
	}
	public static runTextareaAutosize($form: JQuery) {
		autosize($("textarea", $form));
	}
	public static runChangeFileInputListener($form: JQuery) {
		const $uploadFileInput = $("#uploadFiles", $form);
		const $attachmentsContent = $("#attachmentsContent", $form);
		$uploadFileInput.on("change", (e) => {
			const input = <HTMLInputElement>$(e.currentTarget)[0];
			const files = input.files;
			$.each(files, (_index: number, file: File) => {
				$attachmentsContent.append(`<div class="upload-file-container d-inline-block bg-light-gray py-2 px-3 rounded mt-2 ml-3 my-4">
					<span class="upload-file-name">${file.name}</span>
					<span class="text-danger mr-2 cursor-pointer remove-file-icon">
						<i class="fa fa-times-circle fa-lg"></i>
					</span>
				</div>`);
				Ticket.finalFilesForUpload.push(file);
			});
			$uploadFileInput.val(null);
			$(".remove-file-icon", $attachmentsContent).on("click", function() {
				$(this).parent().remove();
				const nameOfFileToRemove = $(this).siblings().text();
				for (let i=0; i < Ticket.finalFilesForUpload.length; i++) {
					if (nameOfFileToRemove == Ticket.finalFilesForUpload[i].name) {
						Ticket.finalFilesForUpload.splice(i, 1);
						break;
					}
				}
			});
		});
	}
	public static appendFilesToFormData(formData: FormData): FormData {
		for (const file of Ticket.finalFilesForUpload) {
			formData.append('file[]', file);
		}
		return formData;
	}
	private static closeTicketListener() {
		$("#ticket-close").on("click", function(e) {
			e.preventDefault();
			$("i", $(this)).attr("class", "fa fa-spinner fa-pulse");
			AjaxRequest({
				url: Router.url("userpanel/ticketing/close/" + $(this).data("ticket") + "?ajax=1"),
				data: {},
				type: "post",
				success: (data: webuilder.AjaxResponse) => {
					$.growl.notice({
						title: t("ticketing.request.response.successful"),
						message: t("ticketing.request.response.successful.message"),
					});
					$(this).remove();
				},
				error: function(error: webuilder.AjaxError) {
					$("i", $(this)).attr("class", "fa fa-times");
					$.growl.error({
						title: t("ticketing.request.response.error"),
						message: t("ticketing.request.response.error.message"),
					});
				},
			});
		});
	}
	private static inProgressTicketListener() {
		$("#ticket-inProgress").on("click", function(e) {
			e.preventDefault();
			$("i", $(this)).attr("class", "fa fa-spinner fa-pulse");
			AjaxRequest({
				url: Router.url("userpanel/ticketing/inprogress/" + $(this).data("ticket") + "?ajax=1"),
				data: {},
				type: "post",
				success: (data: webuilder.AjaxResponse) => {
					$.growl.notice({
						title: t("ticketing.request.response.successful"),
						message: t("ticketing.request.response.successful.message"),
					});
					$(this).remove();
				},
				error: function(error: webuilder.AjaxError) {
					$("i", $(this)).attr("class", "fa fa-tasks");
					$.growl.error({
						title: t("ticketing.request.response.error"),
						message: t("ticketing.request.response.error.message"),
					});
				},
			});
		});
	}
	private static editListener() {
		$("#ticket-edit").hover(function() {
			$("i", $(this)).addClass("fa-spin");
		}, function() {
			$("i", $(this)).removeClass("fa-spin");
		});
		$("#ticket-edit").on("click", function(e) {
			e.preventDefault();
			$("i", $(this)).addClass("fa-spin");
			$("#settings").modal("show");
			$("#settings").on("hidden.bs.modal", () => {
				$("i", $(this)).removeClass("fa-spin");
			});
		});
		const $editForm = $("#settings #editForm");
		$editForm.on("submit", function(e) {
			e.preventDefault();
			const form = this as HTMLFormElement;
			$(form).formAjax({
				success: (data: webuilder.AjaxResponse) => {
					$.growl.notice({
						title: t("ticketing.request.response.successful"),
						message: t("ticketing.request.response.successful.message.ticket.edit"),
					});
					$(this).parents(".modal").modal("hide");
					window.location.href = data.redirect;
				},
				error: (error: webuilder.AjaxError) => {
					if (error.error === "data_duplicate" || error.error === "data_validation") {
						const $input = $(`[name="${error.input}"]`, form);
						const $params = {
							title: t("ticketing.request.response.error"),
							message: t("ticketing.request.response.error.message"),
						};
						if (error.error == "data_validation") {
							$params.message = t("ticketing.request.response.error.message.data_validation");
						}
						if ($input.length) {
							$input.inputMsg($params);
						} else {
							$.growl.error($params);
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
		const $setOperatorForm = $("#set-operator-form");
		$setOperatorForm.on("submit", function(e) {
			e.preventDefault();
			const form = this as HTMLFormElement;
			if (! $("input[name=operator]", form).val()) {
				return false;
			}
			$(form).formAjax({
				success: () => {
					$.growl.notice({
						title: t("ticketing.request.response.successful"),
						message: t("ticketing.request.response.successful.message.ticket.operator.edit"),
					});
				},
				error: (error: webuilder.AjaxError) => {
					if (error.error == "data_duplicate" || error.error == "data_validation") {
						const $input = $(`[name="${error.input}"]`, form);
						const $params = {
							title: t("ticketing.request.response.error"),
							message: "",
						};
						if (error.error == "data_validation") {
							$params.message = t("ticketing.request.response.error.message.data_validation");
						}
						if ($input.length) {
							$input.inputMsg($params);
						} else {
							$.growl.error($params);
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
		$("input[name=operator_name]", $setOperatorForm).ticketOperatorAutoComplete($setOperatorForm.data("department"));
		$("input[name=operator_name]", $editForm).ticketOperatorAutoComplete($editForm.data("department"));
	}
}
