jQuery(document).ready(function () {
	var old_value = 0;
	$('.dynamic-input').focus(function() {
		old_value = $(this).val();
	}).blur(function(){
		var value = $(this).val();
		if(old_value != value) {
			$.ajax({
				type: "POST",
				dataType: 'json',
				context: $(this),
				url: $(this).attr('data-url'),
				data: {value: value},
				success: function(data){
					if(data.state) {
						$(this).val(data.msg);
					} else {
						alert(data.msg);
						$(this).val(old_value);
					}
				}
			});
		}
	});
});
