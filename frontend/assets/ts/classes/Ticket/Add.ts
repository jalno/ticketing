import "@jalno/translator";
import "bootstrap-inputmsg";
import * as $ from "jquery";
import "jquery.growl";
import { AjaxRequest, Router , webuilder } from "webuilder";
import "../jquery.ticketingUserAutoComplete";
import {IUser} from "../jquery.ticketingUserAutoComplete";
import Ticket from "../Ticket";

interface IFormAjaxError {
	input?: string;
	error: webuilder.error;
	type: webuilder.errorType;
	code?: string;
	message?: string;
}

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
		Ticket.runTextareaAutosize(Add.$form);
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
				$btn.blur().css("background-color", "#e6e6e6").html(`<i class="fa fa-user" aria-hidden="true"></i> ${t("ticketing.ticket.add.user.select_one_user")}`);
				$newTicketPanelContainer.removeClass("col-sm-12").addClass("col-sm-8");
				$("select[name=product]", Add.$form).val("").parents(".form-group").hide();
				$("select[name=service]", Add.$form).val("").parents(".form-group").hide();
				setTimeout(() => {
					$multiuserPanelContainer.slideDown();
					$("input[name=clients_name]", $multiuserPanelContainer).focus();
					$newTicketPanelContainer.addClass("col-sm-pull-4");
				}, 700);
			} else {
				$("select[name=department]", Add.$form).trigger("change");
				$clientInput.prop("disabled", false).attr("name", "client");
				$clientNameInput.prop("disabled", false);
				$btn.blur().css("background-color", "inherit").html(`<i class="fa fa-users" aria-hidden="true"></i> ${t("ticketing.ticket.add.user.select_multi_user")}`);
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

		let timeout: number;
		let confirmStep = false;
		const $btn = $(".btn-remove-user", $tr);
		$btn.on("click", (e) => {
			e.preventDefault();
			if (confirmStep) {
				if (timeout) {
					clearTimeout(timeout);
				}
				$btn.parents("tr").remove();
				const $rows = $(".multiuser-users .table tbody > tr");
				if (!$rows.length) {
					$("input[name=clients_name]", Add.$form).val("").focus();
					return;
				}
				$rows.each((item, elemnt) => {
					const $row = $(elemnt);
					const $input = $("input", $row);
					if ($input.length) {
						$input.attr("name", `clients[${item}]`);
					}
					$("td.index", $row).html((item + 1).toString());
				});
				return;
			}
			confirmStep = true;
			const $icon = $("i", $btn);
			$icon.removeClass().addClass("fa fa-info-circle");
			$btn.removeClass("btn-link").addClass("btn-danger");
			timeout = setTimeout(() => {
				confirmStep = false;
				$icon.removeClass().addClass("fa fa-trash");
				$btn.addClass("btn-link").removeClass("btn-danger");
				timeout = undefined;
			}, 1000);
		});
	}
	private static runUserSearch() {
		$("input[name=client_name]", Add.$form).ticketingUserAutoComplete();
	}
	private static runDepartmentListener() {
		$("select[name=department]", Add.$form).change(function() {
			const $products = $("select[name=product]", Add.$form).html("");
			$products.parents(".form-group").hide();
			if (Add.multiuserMode) {
				return;
			}
			const $selectedOption = $("option:selected", this);
			const products = $selectedOption.data("products") as Array<{title: string, value: string}>;
			if (products) {
				for (const product of products) {
					$products.append($("<option>", {
						text : product.title,
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
					url: "userpanel/ticketing/new/department/" + department,
					data: {},
					success: (data: webuilder.AjaxResponse) => {
						if (data.department.currentWork.message) {
							$(".alert").slideUp("slow", function() {
								$(this).remove();
							});
							let code: string = `<div class="row">`;
							code += `<div class="col-xs-12">`;
							code += `<div class="alert alert-block alert-info">`;
							code += `<button data-dismiss="alert" class="close" type="button">×</button>`;
							code += `<h4 class="alert-heading"><i class="fa fa-info-circle"></i> توجه</h4>`;
							code += `<p>` + data.department.currentWork.message + `</p>`;
							code += `</div></div></div>`;
							Add.$form.parents(".panel").before(code);
						}
					},
					error: (error: webuilder.AjaxError) => {
						$.growl.error({
							title: t("ticketing.request.response.error"),
							message: t("ticketing.request.response.error.message"),
						});
					},
				});
			} else {
				$(".alert").slideUp("slow", function() {
					$(this).remove();
				});
			}
		}).trigger("change");
	}
	private static runServicesListener() {
		const $services = $("select[name=service]", Add.$form);
		const $formGroup = $services.parents(".form-group");
		const $alert = $(".alert-service", Add.$form);
		$("select[name=product], input[name=client]", Add.$form).on("change", () => {
			const product = $("select[name=product]").val() as string;
			if (!product || Add.multiuserMode) {
				$("select[name=service]").parents(".form-group").hide();
				return;
			}
			let user: string;
			const $user = $("input[name=client]", Add.$form);
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
				success: (data: webuilder.AjaxResponse) => {
					const length = data.items.length;
					if (length) {
						$formGroup.show();
						if (!$("input[name=client_name]", Add.$form).length) {
							$("textarea[name=text]", Add.$form).attr("rows", 8);
						}
						for (let i = 0; i < length; i++) {
							$services.append($("<option>", {
								value: data.items[i].id,
								text : data.items[i].title,
							}));
						}
					} else {
						$services.val("");
						$formGroup.hide();
					}
				},
				error: (error: webuilder.AjaxError) => {
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
		Add.$form.on("submit", function(e) {
			e.preventDefault();
			const product = $("select[name=product]", Add.$form).val();
			if (!product) {
				$("select[name=service]", Add.$form).val("");
			}
			$(".has-error", Add.$form).removeClass("has-error").children(".help-block").remove();
			if (Add.multiuserMode && !$('input[name^="client[.]"]').length) {
				$.growl.error({
					title: t("ticketing.error"),
					message: t("error.ticketing.error.ticket.add.user.select_multi_user.should_select_one_user_at_least"),
				});
				return;
			}
			($(this) as any).formAjax({
				data: new FormData(this as HTMLFormElement),
				contentType: false,
				processData: false,
				success: (response: webuilder.AjaxResponse) => {
					$.growl.notice({
						title: t("ticketing.request.response.successful"),
						message: t("ticketing.request.response.successful.message"),
					});
					window.location.href = response.redirect;
				},
				error: (error: IFormAjaxError) => {
					const params = {
						title: t("ticketing.request.response.error"),
						message: t("ticketing.request.response.error.message"),
					};
					if (error.error === "data_duplicate" || error.error === "data_validation") {
						params.message = t(`ticketing.request.response.error.message.${error.error}`);
						if (error.input === "client") {
							error.input = "client_name";
						}
						const $input = $(`[name="${error.input}"]`);
						if (error.error === "data_validation") {
							if (error.input) {
								if ((new RegExp(/^file\[[0-9]\]/)).test(error.input)) {
									params.message = t("ticketing.request.response.error.message.data_validation.file");
								}
							}
						}
						if ($input.length) {
							$input.inputMsg(params);
						} else {
							$.growl.error(params);
						}
						return;
					}
					if (error.message) {
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
