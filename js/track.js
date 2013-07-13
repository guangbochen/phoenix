function myTab() {
	$('#myTab a').click(function (e) {
		 	e.preventDefault();
	  		$(this).tab('show');
	  		$(this).attr('id', "onclick");
	  		$('a').not(this).attr("id", "");
		});
}

function fetchMyTab() {
	$('.nav a').click(function (e) {
		 	e.preventDefault();
	  		$(this).tab('show');
	  		$(this).attr('id', "onclick");
	  		$('a').not(this).attr("id", "");
		});
}
