			$(document).ready(function(){
			$("#open").on("click",function(){
	           var adminurl=$("#ajaxurl").val();
	           // alert(adminurl);
				$.ajax({
				type    : 'post',
				url     :adminurl,
					 data:{
					 	action:'wp_ajax_function'
					 },
					 success:function(response){
					    var returnedData = JSON.parse(response);
					 	console.log(returnedData);
					 	console.log(returnedData[0]['user_login']);
					 	alert(returnedData[0]['user_login']);
					 		$('#update').html('<h3 style="color:green;">Data Updated Successfully</h3>').fadeIn('slow');
		         	        $('#update').delay(3000).fadeOut('slow');
					 }
				});
		
		
			});
			$("#newid").mouseover(function(){
			$("#newid").css("background-color", "yellow");
			});
			$("#newid").mouseout(function(){
			$("#newid").css("background-color", "lightgray");
			});





			});