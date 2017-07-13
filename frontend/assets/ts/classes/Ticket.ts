import List from "./Ticket/List";
import Add from "./Ticket/Add";
import Reply from "./Ticket/Reply";
import Edit from "./Ticket/Edit";
import Close from "./Ticket/Close";
import { AjaxRequest, Router , webuilder } from "webuilder";
import "jquery.growl";
export default class Ticket{
	private static closeTicketListener(){
		$('#ticket-close').on('click', function(e){
			e.preventDefault();
			AjaxRequest({
				url: Router.url('userpanel/ticketing/close/' + $(this).data('ticket') + '?ajax=1'),
				data:{},
				type: 'post',
				success: (data: webuilder.AjaxResponse) => {
					$.growl.notice({
						title:"موفق",
						message:"انجام شد ."
					});
					window.location.href = data.redirect;
				},
				error: function(error:webuilder.AjaxError){
					$.growl.error({
						title:"خطا",
						message:'درخواست شما توسط سرور قبول نشد'
					});
				}
			});
		});
	}
	public static init(){
		Ticket.closeTicketListener();
	}
	public static initIfNeeded(){
		List.initIfNeeded();
		Add.initIfNeeded();
		Reply.initIfNeeded();
		Edit.initIfNeeded();
		Close.initIfNeeded();
		if($('#ticket-close').length){
			Ticket.init();
		}
	}
}