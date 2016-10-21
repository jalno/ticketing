var TicketAdd = function () {
	var form = $('.create_form');
	var runUserListener = function(){
		$("input[name=user_name]", form).autocomplete({
			source: function( request, response ) {
				$.ajax({
					url: "/fa/userpanel/users",
					dataType: "json",
					data: {
						ajax:1,
						word: request.term
					},
					success: function( data ) {
						if(data.hasOwnProperty('status')){
							if(data.status){
								if(data.hasOwnProperty('items')){
									response( data.items );
								}
							}
						}

					}
				});
			},
			select: function( event, ui ) {
				$(this).val(ui.item.name+(ui.item.lastname ? ' '+ui.item.lastname : ''));
				$('input[name=client]', form).val(ui.item.id).trigger('change');
				return false;
			},
			focus: function( event, ui ) {
				$(this).val(ui.item.name+(ui.item.lastname ? ' '+ui.item.lastname : ''));
				$('input[name=client]', form).val(ui.item.id);
				return false;
			}
		}).autocomplete( "instance" )._renderItem = function( ul, item ) {
			return $( "<li>" )
				.append( "<strong>" + item.name+(item.lastname ? ' '+item.lastname : '')+ "</strong><small class=\"ltr\">"+item.email+"</small><small class=\"ltr\">"+item.cellphone+"</small>" )
				.appendTo( ul );
		};
	};
	var getServices = function(){
		$("select[name=product], input[name=client]").change(function() {
			var product = $('select[name=product]').val();
			if(product.length){
				$("select[name=service]").parents(".form-group").show("slow");
				var user = $("input[name=client]").val();
				if(user.length){
					$('select[name=service]').html('');
					$.ajax({
						url: "/fa/userpanel/ticketing/getservices",
						dataType: "json",
						data: {
							ajax:1,
							client: user,
							product: product
						},
						success:function(data){
							if(data.status){
								for (var i = 0; i < data.items.length; i++) {
									$('select[name=service]').append($('<option>',
										{
										   value: data.items[i].id,
										   text : data.items[i].title
									   }));

								}
							}
						},
					});
				}else{
					$.growl.notice({title:"خطا", message:"کاربر پیدا نشد"});
				}
			}else{
				$("select[name=service]").parents(".form-group").hide();
			}
		});
	};
	var initElements = function(){
		hiddenServices();
	};
	var hiddenServices = function(){
		var $service = $('select[name=service]',form);
		if($('option', $service).length == 0){
			$service.parents('.form-group').first().hide();
		}
	}
	return {
		init: function() {
			initElements();
			runUserListener();
			getServices();

		}
	}
}();
$(function(){
	TicketAdd.init();
});
