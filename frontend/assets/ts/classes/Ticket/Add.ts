import * as $ from "jquery";
import "../jquery.userAutoComplete";
import { AjaxRequest, Router , webuilder } from "webuilder";
import "jquery.growl";
import "bootstrap-inputmsg";
export default class Add{
	private static $form = $('#ticket-add');
	private static runUserSearch(){
		$('input[name=client_name]', Add.$form).userAutoComplete();
	}
	private static runDepartmentListener(){
		$("select[name=department]", Add).change(function(){
			let isWorking:number = $("option:selected", this).data("working");
			let department:string = $(this).val().toString();
			if(isWorking == 0){
				AjaxRequest({
					url: 'userpanel/settings/departments/edit/' + department,
					data:{},
					success: (data: webuilder.AjaxResponse) => {
						if(data.department.currentWork.message){
							$(".alert").slideUp("slow", function(){
								$(this).remove();
							});
							let code:string = '<div class="row">';
							code += '<div class="col-xs-12">';
							code += '<div class="alert alert-block alert-info">';
							code += '<button data-dismiss="alert" class="close" type="button">×</button>';
							code += '<h4 class="alert-heading"><i class="fa fa-info-circle"></i> توجه</h4>';
							code += '<p>'+data.department.currentWork.message+'</p>';
							code += '</div></div></div>';
							Add.$form.parents('.panel').before(code);
						}
					},
					error: function(error:webuilder.AjaxError){
						$.growl.error({
							title:"خطا",
							message:'درخواست شما توسط سرور قبول نشد'
						});
					}
				});
			}else{
				$(".alert").slideUp("slow", function(){
					$(this).remove();
				});
			}
		});
		$("select[name=department]", Add.$form).trigger('change');
	}
	private static runServicesListener(){
		const $services = $("select[name=service]", Add.$form);
		const $formGroup = $services.parents(".form-group");
		const $alert = $(".alert-service", Add.$form);
		$("select[name=product], input[name=client]").on("change", function() {
			let product:string = $('select[name=product]').val().toString();
			if(product.length){
				let user:string;
				if($("input[name=client]", Add.$form).length){
					user = $("input[name=client]").val() as string;
					if(!user){
						$.growl.error({
							title:"خطا",
							message:'کاربر مشخص نشده ، لطفا کاربر را مشخص کنید .'
						});
						return ;
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
							for (let i = 0; i < length; i++) {
								$services.append($('<option>',{
									value: data.items[i].id,
									text : data.items[i].title
								}));
							}
						} else {
							$formGroup.hide();
						}
					},
					error: function(error:webuilder.AjaxError){
						$.growl.error({
							title:"خطا",
							message:'درخواست شما توسط سرور قبول نشد'
						});
					}
				});
			}else{
				$("select[name=service]").parents(".form-group").hide();
			}
		});
	}
	private static hiddenServices(){
		let $service:JQuery = $('select[name=service]', Add.$form);
		if($('option', $service).length == 0){
			$service.parents('.form-group').first().hide();
		}
	}
	private static runSubmitFormListener(){
		Add.$form.on('submit', function(e){
			e.preventDefault();
			$(this).formAjax({
				data: new FormData(this as HTMLFormElement),
				contentType: false,
				processData: false,
				success: (data: webuilder.AjaxResponse) => {
					$.growl.notice({
						title:"موفق",
						message:"انجام شد ."
					});
					window.location.href = data.redirect;
				},
				error: function(error:webuilder.AjaxError){
					if(error.error == 'data_duplicate' || error.error == 'data_validation'){
						let $input = $('[name='+error.input+']');
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
	}
	public static init(){
		if($('input[name=client_name]', Add.$form).length){
			Add.runUserSearch();
		}
		Add.runDepartmentListener();
		Add.runServicesListener();
		Add.hiddenServices();
		Add.runSubmitFormListener();
	}
	public static initIfNeeded(){
		if(Add.$form.length){
			Add.init();
		}
	}
}