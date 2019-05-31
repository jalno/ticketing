import * as $ from "jquery";
import List from "./Ticket/List";
import Add from "./Ticket/Add";
import Reply from "./Ticket/Reply";
import Edit from "./Ticket/Edit";
import Close from "./Ticket/Close";
import { AjaxRequest, Router , webuilder } from "webuilder";
import "jquery.growl";
import "bootstrap/js/modal";
import "./jquery.ticketOperatorAutoComplete";

export default class Ticket{
	private static closeTicketListener(){
		$('#ticket-close').on('click', function(e){
			e.preventDefault();
			$('i', $(this)).attr('class', 'fa fa-spinner fa-pulse');
			AjaxRequest({
				url: Router.url('userpanel/ticketing/close/' + $(this).data('ticket') + '?ajax=1'),
				data:{},
				type: 'post',
				success: (data: webuilder.AjaxResponse) => {
					$.growl.notice({
						title:"موفق",
						message:"انجام شد ."
					});
					$(this).remove();
				},
				error: function(error:webuilder.AjaxError){
					$('i', $(this)).attr('class', 'fa fa-times');
					$.growl.error({
						title:"خطا",
						message:'درخواست شما توسط سرور قبول نشد'
					});
				}
			});
		});
	}
	private static inProgressTicketListener(){
		$('#ticket-inProgress').on('click', function(e){
			e.preventDefault();
			$('i', $(this)).attr('class', 'fa fa-spinner fa-pulse');
			AjaxRequest({
				url: Router.url('userpanel/ticketing/inprogress/' + $(this).data('ticket') + '?ajax=1'),
				data:{},
				type: 'post',
				success: (data: webuilder.AjaxResponse) => {
					$.growl.notice({
						title:"موفق",
						message:"انجام شد ."
					});
					$(this).remove();
				},
				error: function(error:webuilder.AjaxError){
					$('i', $(this)).attr('class', 'fa fa-tasks');
					$.growl.error({
						title:"خطا",
						message:'درخواست شما توسط سرور قبول نشد'
					});
				}
			});
		});
	}
	private static editListener(){
		$('#ticket-edit').hover(function(){
			$('i', $(this)).addClass('fa-spin');
		}, function(){
			$('i', $(this)).removeClass('fa-spin');
		});
		$('#ticket-edit').on('click', function(e){
			e.preventDefault();
			$('i', $(this)).addClass('fa-spin');
			$('#settings').modal('show');
			$('#settings').on('hidden.bs.modal', () => {
				$('i', $(this)).removeClass('fa-spin');
			});
		});
		const $editForm = $("#settings #editForm");
		$editForm.on('submit', function(e){
			e.preventDefault();
			const form = this as HTMLFormElement;
			$(form).formAjax({
				success: (data: webuilder.AjaxResponse) => {
					$.growl.notice({
						title:"موفق",
						message:"تیکت با موفقیت ویرایش شد ."
					});
					$(this).parents('.modal').modal('hide');
					window.location.href = data.redirect;
				},
				error: function(error:webuilder.AjaxError){
					if(error.error == 'data_duplicate' || error.error == 'data_validation'){
						let $input = $(`[name="${error.input}"]`, form);
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
						title: "موفق",
						message: "اپراتور با موفقیت ثبت شد."
					});
				},
				error: (error:webuilder.AjaxError) => {
					if (error.error == "data_duplicate" || error.error == "data_validation") {
						const $input = $(`[name="${error.input}"]`, form);
						const $params = {
							title: "خطا",
							message:""
						};
						if (error.error == "data_validation") {
							$params.message = "داده وارد شده معتبر نیست";
						}
						if ($input.length) {
							$input.inputMsg($params);
						} else {
							$.growl.error($params);
						}
					} else {
						$.growl.error({
							title:"خطا",
							message:"درخواست شما توسط سرور قبول نشد"
						});
					}
				}
			});
		});
		$("input[name=operator_name]", $setOperatorForm).ticketOperatorAutoComplete($setOperatorForm.data("department"));
		$("input[name=operator_name]", $editForm).ticketOperatorAutoComplete($editForm.data("department"));
	}
	public static init(){
		if($('#ticket-close').length){
			Ticket.closeTicketListener();
		}
		if($('#ticket-inProgress').length){
			Ticket.inProgressTicketListener();
		}
		if($('#settings').length){
			Ticket.editListener();
		}
	}
	public static initIfNeeded(){
		List.initIfNeeded();
		Add.initIfNeeded();
		Reply.initIfNeeded();
		Edit.initIfNeeded();
		Close.initIfNeeded();
		Ticket.init();
	}
}