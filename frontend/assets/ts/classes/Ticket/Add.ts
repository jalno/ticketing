import "@jalno/translator";
import "bootstrap-inputmsg";
import * as $ from "jquery";
import "jquery.growl";
import { AjaxRequest, Router , webuilder } from "webuilder";
import "../jquery.userAutoComplete";
import Ticket from "../Ticket";

export default class Add {
	public static initIfNeeded() {
		Add.$form = $("#ticket-add");
		if (Add.$form.length) {
			Add.init();
		}
	}
	private static $form: JQuery;
	private static init() {
		if ($("input[name=client_name]", Add.$form).length) {
			Add.runUserSearch();
		}
		Add.runDepartmentListener();
		Add.runServicesListener();
		Add.hiddenServices();
		Add.runSubmitFormListener();
		Ticket.runEnableDisableNotificationListener(Add.$form);
		Ticket.runTextareaAutosize(Add.$form);
	}

	private static runUserSearch() {
		$("input[name=client_name]", Add.$form).userAutoComplete();
	}
	private static runDepartmentListener() {
		$("select[name=department]", Add.$form).change(function() {
			const $selectedOption = $("option:selected", this);
			const $products = $("select[name=product]", Add.$form).html("");
			$products.parents(".form-group").hide();
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
			const department: string = $(this).val().toString();
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
			const product = $("select[name=product]").val();
			if (product) {
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
						product: product.toString(),
					},
					success: (data: webuilder.AjaxResponse) => {
						const length = data.items.length;
						if (length) {
							$formGroup.show();
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
			} else {
				$("select[name=service]").parents(".form-group").hide();
			}
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
