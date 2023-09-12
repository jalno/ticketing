import "@jalno/translator";
import "bootstrap-inputmsg";
import * as $ from "jquery";
import "jquery.growl";
import { AjaxRequest, Router, webuilder } from "webuilder";
import "webuilder/formAjax";
import "../jquery.ticketingUserAutoComplete";
import { IUser } from "../jquery.ticketingUserAutoComplete";
import Ticket from "../Ticket";
import IFormAjaxError from "../IFormAjaxError"
import TemplateSelector from '../TemplateSelector';

export default class Add {
	public static initIfNeeded() {
		Add.$form = $("#ticket-add");
		if (Add.$form.length) {
			Add.init();
		}
	}
	private static $form: JQuery;
	private static multiuserMode: boolean = false;
	private static init() {
		if ($("input[name=client_name]", Add.$form).length) {
			Add.runUserSearch();
		}
		Add.runDepartmentListener();
		Add.runServicesListener();
		Add.hiddenServices();
		Add.resetMultiuserTable();
		Add.runMultiuserPanel();
		Add.runMultiuserBtnChangeListener();
		Add.runSubmitFormListener();
		Ticket.runEnableDisableNotificationListener(Add.$form);
		Ticket.runChangeFileInputListener(Add.$form);
		Ticket.runTemplateselector(Add.$form);
	}
	private static runMultiuserBtnChangeListener(): void {
		const $btn = $("button.btn-multiuser", Add.$form);
		if (!$btn.length) {
			return;
		}
		const $newTicketPanelContainer = $(".new-ticket-panel-container", Add.$form);
		const $multiuserPanelContainer = $(".multiuser-panel-container", Add.$form);
		const $clientInput = $("input[name=client]", Add.$form);
		const $clientNameInput = $("input[name=client_name]", Add.$form);
		const $multiuserInput = $("input[name=multiuser_mode]", Add.$form);
		$btn.on("click", (e) => {
			e.preventDefault();
			Add.multiuserMode = !Add.multiuserMode;
			$multiuserInput.val(Add.multiuserMode ? 1 : 0);
			if (Add.multiuserMode) {
				if ($clientInput.val() && $clientNameInput.val()) {
					Add.AddMultiuser($clientInput.data("user") as IUser);
				}
				$clientInput.prop("disabled", true).val("").attr("name", "");
				$clientNameInput.prop("disabled", true).val("");
				$btn.blur().html(`<i class="fa fa-user" aria-hidden="true"></i> ${t("ticketing.ticket.add.user.select_one_user")}`);
				$newTicketPanelContainer.removeClass("col-sm-12").addClass("col-sm-8");
				setTimeout(() => {
					$multiuserPanelContainer.slideDown();
					$("input[name=clients_name]", $multiuserPanelContainer).focus();
					$newTicketPanelContainer.addClass("col-sm-pull-4");
				}, 700);
			} else {
				$("select[name=department]", Add.$form).trigger("change");
				$clientInput.prop("disabled", false).attr("name", "client");
				$clientNameInput.prop("disabled", false);
				$btn.blur().html(`<i class="fa fa-users" aria-hidden="true"></i> ${t("ticketing.ticket.add.user.select_multi_user")}`);
				$newTicketPanelContainer.addClass("col-sm-12").removeClass("col-sm-8 col-sm-pull-4");
				$multiuserPanelContainer.hide();
				Add.resetMultiuserTable(false);
			}
		});
		if ($btn.data("has-clients") as boolean) {
			$btn.trigger("click");
		}
	}
	private static runMultiuserPanel(): void {
		const $clientsName = $("input[name=clients_name]", Add.$form);
		$clientsName.ticketingUserAutoComplete($("input[name=clients]", Add.$form), (event, ui) => {
			Add.AddMultiuser(ui.item);
			$clientsName.val("");
		});
	}
	private static resetMultiuserTable(addPredefinedUsers: boolean = true): void {
		const $table = $(".multiuser-users .table");
		$("tbody > tr", $table).remove();
		$("input[name=clients_name]", Add.$form).val("");
		const users = $table.data("items") as IUser[];
		if (users && addPredefinedUsers) {
			for (const user of users) {
				this.AddMultiuser(user);
			}
		}
	}
	private static AddMultiuser(user: IUser): void {
		const $table = $(".multiuser-users .table", Add.$form);
		const $tbody = $("tbody", $table);
		const $trs = $("> tr", $tbody);
		if ($(`input[value=${user.id}]`, $trs).length) {
			$.growl.warning({
				title: t("ticketing.error"),
				message: t("ticketing.ticket.add.user.select_multi_user.duplicate"),
			});
			return;
		}
		const index = $trs.length;
		const html = `<tr>
			<td class="index">${index + 1}</td>
			<td>
				<span><a target="_blank" href="${Router.url(`userpanel/users?id=${user.id}`)}">${user.name + (user.lastname ? " " + user.lastname : "")}</a></span>
				<input type="hidden" name="client[${index}]" value="${user.id}">
			</td>
			<td class="btn-remove-container">
				<button type="button" class="btn btn-link btn-block btn-remove-user">
					<i class="fa fa-trash"></i>
				</button>
			</td>
		</tr>`;
		const $tr = $(html).appendTo($tbody);

		if ($("tr", $tbody).length > 1) {
			$("select[name=product]", Add.$form).val("").parents(".form-group").hide();
			$("select[name=service]", Add.$form).val("").parents(".form-group").hide();
		}

		let timeout: number;
		let confirmStep = false;
		const $btn = $(".btn-remove-user", $tr);
		const $icon = $("i", $btn);
		$btn.on("click", (e) => {
			e.preventDefault();
			if (confirmStep) {
				if (timeout) {
					clearTimeout(timeout);
				}
				$btn.parents("tr").remove();
				const $users = $("tr", $tbody);
				if (!$users.length) {
					$("input[name=clients_name]", Add.$form).val("").focus();
					return;
				}
				$users.each((item, elemnt) => {
					const $user = $(elemnt);
					const $input = $("input", $user);
					if ($input.length) {
						$input.attr("name", `client[${item}]`);
					}
					$("td.index", $user).html((item + 1).toString());
				});
				if ($users.length < 2) {
					$("select[name=department]", Add.$form).trigger("change");
				}
				return;
			}
			confirmStep = true;
			$icon.removeClass().addClass("fa fa-info-circle");
			$btn.addClass("confirm-remove");
			$btn.tooltip({
				title: "تایید حذف",
				trigger: "manual",
				placement: "top",
			});
			$btn.tooltip("show");
			timeout = setTimeout(() => {
				confirmStep = false;
				$icon.removeClass().addClass("fa fa-trash");
				$btn.removeClass("confirm-remove");
				timeout = undefined;
				$btn.tooltip("hide");
			}, 2000);
		});
	}
	private static runUserSearch() {
		$("input[name=client_name]", Add.$form).ticketingUserAutoComplete();
	}
	private static runDepartmentListener() {
		const $users = $(".multiuser-users .table tbody", Add.$form);
		const $template = $('select[name="template"]', Add.$form);
		const $templates = $('option', $template);
		$("select[name=department]", Add.$form).on('change', function () {
			if ($template.length) {
				const val = $(this).val() as string;

				if (val) {
					$template.prop('disabled', false);
					$templates.each(function () {
						if (!$(this).val()) {
							return;
						}

						if (!$(this).data('department') || val == $(this).data('department')) {
							$(this).show();
						} else {
							$(this).hide();
						}
					});
				} else {
					$template.prop('disabled', true);
					$templates.show();
				}
			}

			const $products = $("select[name=product]", Add.$form).html("");
			$products.parents(".form-group").hide();
			if (Add.multiuserMode && $("tr", $users).length > 1) {
				return;
			}
			const $selectedOption = $("option:selected", this);
			const products = $selectedOption.data("products") as Array<{ title: string, value: string }>;
			if (products) {
				for (const product of products) {
					$products.append($("<option>", {
						text: product.title,
						value: product.value,
					}));
				}
				$products.trigger("change").parents(".form-group").show();
			} else {
				$products.parents(".form-group").hide();
				$("select[name=service]", Add.$form).parents(".form-group").hide();
			}
			const isWorking: number = $selectedOption.data("working") as number;
			const department = $(this).val() as string;
			if (isWorking === 0) {
				AjaxRequest({
					url: "userpanel/ticketing/new/department/" + department + "?ajax=1",
					success: (response) => {
						if (response.department.currentWork.message) {
							$(".alert.department-working-message-alert").slideUp("slow", function () {
								$(this).remove();
							});

							Add.$form.parents(".panel").before(`<div class="alert alert-block department-working-message-alert alert-block alert-info">
							<button data-dismiss="alert" class="close" type="button">×</button>
							<h4 class="alert-heading"><i class="fa fa-info-circle"></i> توجه</h4>
							<p>${response.department.currentWork.message}</p>
						</div>`);
						}
					},
					error: () => {
						$.growl.error({
							title: t("ticketing.request.response.error"),
							message: t("ticketing.request.response.error.message"),
						});
					},
				});
			} else {
				$(".alert.department-working-message-alert").slideUp("slow", function () {
					$(this).remove();
				});
			}
		}).trigger("change");
	}
	private static runServicesListener() {
		const $services = $("select[name=service]", Add.$form);
		const $formGroup = $services.parents(".form-group");
		const $users = $(".multiuser-users .table tbody", Add.$form);
		let $serviceError: JQuery;
		const $product = $("select[name=product]", Add.$form);
		const $btns = $(".new-ticket-panel-container .btn-group button, .new-ticket-panel-container .btn-group .btn-file2 input", Add.$form);
		const $btnFile = $(".new-ticket-panel-container .btn-group .btn-file2", Add.$form);

		const createError = (type: "FATAL" | "WARNING", text: string): JQuery => {
			return $(`<div class="alert alert-block alert-${type === "FATAL" ? "danger" : "warning"}">
			<h4 class="alert-heading">
				${type === "FATAL" ? `<i class="fa fa-times-circle"></i>` : `<i class="fa fa-exclamation-triangle"></i>`}
			${t(`error.${type.toLocaleLowerCase()}.title`)}
		</h4>
		<div class="alert-body">${text}</div>
		</div>`);
		}

		const removeError = () => {
			if ($serviceError && $serviceError.length) {
				$serviceError.remove();
				$serviceError = undefined;
			}
		};

		$("select[name=product], input[name=client]", Add.$form).on("change", () => {
			removeError();

			$btns.prop("disabled", false);
			$btnFile.removeClass("disabled");
			const product = $product.val() as string;
			if (!product || (Add.multiuserMode && $("tr", $users).length > 1)) {
				$formGroup.hide();
				return;
			}
			let user: string;
			const $user = Add.multiuserMode ? $('input[name="client[0]"]', $("tr", $users)) : $("input[name=client]", Add.$form);
			if ($user.length) {
				user = $user.val() as string;
				if (!user) {
					$.growl.error({
						title: t("ticketing.request.response.error"),
						message: t("ticketing.ticket.add.user_not_entered"),
					});
					return;
				}
			}
			$services.html("");
			AjaxRequest({
				url: "userpanel/ticketing/getservices",
				data: {
					ajax: 1,
					client: user,
					product: product,
				},
				success: (response) => {
					removeError();
					const length = response.items.length;
					if (length) {
						$formGroup.show();
						for (let i = 0; i < length; i++) {
							$services.append($("<option>", {
								value: response.items[i].id,
								text: response.items[i].title,
							}));
						}
					} else {
						$services.val("");
						$formGroup.hide();
						let productIsOptional = false;
						$("option", $product).each(function () {
							if (!$(this).val()) {
								productIsOptional = true;
								return false;
							}
						});
						$serviceError = createError(productIsOptional ? "WARNING" : "FATAL", t("ticketing.add.product_empty_error") + (!productIsOptional ? "<br>" + t("ticketing.add.noproduct") : "")).insertAfter($formGroup);

						$btns.prop("disabled", !productIsOptional);
						if (productIsOptional) {
							$btnFile.removeClass("disabled");
						} else {
							$btnFile.addClass("disabled");
						}
					}
				},
				error: () => {
					$.growl.error({
						title: t("ticketing.request.response.error"),
						message: t("ticketing.request.response.error.message"),
					});
				},
			});
		});
	}
	private static hiddenServices() {
		const $service: JQuery = $("select[name=service]", Add.$form);
		if (!$("option", $service).length) {
			$service.parents(".form-group").first().hide();
		}
	}
	private static runSubmitFormListener() {
		const $users = $(".multiuser-users .table tbody", Add.$form);
		const $progressBar = $("#progressBar", Add.$form);
		Add.$form.on("submit", function (e) {
			e.preventDefault();
			const product = $("select[name=product]", Add.$form).val();
			if (!product) {
				$("select[name=service]", Add.$form).val("");
			}
			$(".has-error", Add.$form).removeClass("has-error").children(".help-block").remove();
			if (Add.multiuserMode && !$("tr", $users).length) {
				$.growl.error({
					title: t("ticketing.error"),
					message: t("error.ticketing.error.ticket.add.user.select_multi_user.should_select_one_user_at_least"),
				});
				return;
			}
			$(this).formAjax({
				data: Ticket.appendFilesToFormData(new FormData(this as HTMLFormElement)),
				cache: false,
				contentType: false,
				processData: false,
				beforeSend: () => {
					$(".progress-bar-fill", Add.$form).width('0%');
					$(".progress-bar-text", Add.$form).html('0%');
					$(".remove-file-icon", Add.$form).removeClass("text-danger").addClass("text-info").html('<i class="fa fa-spinner fa-spin fa-pw fa-lg"></i>');
				},
				xhr: () => {
					const xhr = new XMLHttpRequest();
					xhr.upload.addEventListener('progress', (evt) => {
						if (evt.lengthComputable) {
							$progressBar.show();
							const percentComplete = ((evt.loaded / evt.total) * 100).toFixed(2);
							$(".progress-bar-fill", Add.$form).width(percentComplete + "%");
							$(".progress-bar-text", Add.$form).html(percentComplete + "%");
						}
					}, false);
					return xhr;
				},
				success: (response: webuilder.AjaxResponse) => {
					$.growl.notice({
						title: t("ticketing.request.response.successful"),
						message: t("ticketing.request.response.successful.message"),
					});
					window.location.href = response.redirect;
				},
				error: (error: IFormAjaxError) => {
					$(".remove-file-icon").addClass("text-danger").removeClass("text-info").html('<i class="fa fa-times-circle fa-lg"></i>');
					$progressBar.hide();
					const params: growl.Options = {
						title: t("ticketing.request.response.error"),
						message: t("ticketing.request.response.error.message"),
					};
					if (error.error === "data_duplicate" || error.error === "data_validation") {
						const fileRegex = /^file\[([0-9])\]/;
						if (error.error == "data_validation" && fileRegex.test(error.input)) {
							params.message = t("ticketing.request.response.error.message.data_validation.file");
							const index = error.input.match(fileRegex)[1];
							const $file = $("#attachmentsContent .upload-file-container", Add.$form).eq(parseInt(index, 10));
							$file.addClass("has-error").append(`<span class="help-block text-center">${params.message}</span>`);
							$(".remove-file-icon", $file).html('<i class="fa fa-ban fa-lg"></i>');
						} else if (error.input === "service" && $(`[name="${error.input}"]`, this).parent().is(":hidden")) {
							params.message = t("ticketing.add.product_empty_error") + "<br>" + t("ticketing.add.noproduct");
						} else {
							params.message = t(`ticketing.request.response.error.message.${error.error}`);
							if (error.input === "client") {
								error.input = "client_name";
							}
							const $input = $(`[name="${error.input}"]`, this);
							if ($input.length) {
								$input.inputMsg(params);
								return;
							}
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
