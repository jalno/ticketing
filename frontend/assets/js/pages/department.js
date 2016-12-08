var DepartmentEdit = function () {
	var form = $("#departmentEdit");
	var runjQRangeSlider = function(){
		$(".slider").rangeSlider({bounds: {min: 0, max: 23}, step:1});
	}
	var rangeSliderEditDefaultValuesjQRangeSlider = function(){
		$(".slider").each(function(){
			var day = $(this).data('day');
			var min = parseInt($("input[name='day["+day+"][worktime][start]']").val());
			var max = parseInt($("input[name='day["+day+"][worktime][end]']").val());
			$(this).rangeSlider("min", min);
			$(this).rangeSlider("max", max);
			checkedchanged(this, (min || max));
		});
	}
	var valuesChangingjQRangeSlider = function(){
		$(".slider").bind("valuesChanging", function(e, data){
			$('input[name="day['+$(this).data('day')+'][worktime][start]"]').val(data.values.min);
			$('input[name="day['+$(this).data('day')+'][worktime][end]"]').val(data.values.max);
		});
	}
	var EnabledjQRangeSlider = function(){
		$("input[type=checkbox]").on('ifChecked', function(){
			checkedchanged(this, true);
		}).on('ifUnchecked', function(){
			checkedchanged(this, false);
		});
	}
	var checkedchanged = function(elem,checked){
		var $slider = $(elem).parents("tr").find(".slider");
		$slider.rangeSlider("option", "enabled",checked);
	}
return {
	init: function() {
		runjQRangeSlider();
		rangeSliderEditDefaultValuesjQRangeSlider();
		valuesChangingjQRangeSlider();
		EnabledjQRangeSlider();
	}
}
}();
$(function(){
	DepartmentEdit.init();
});
