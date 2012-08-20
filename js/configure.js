
function verify_path(value)
{

	$("input[name="+value+"]").blur(function(){
		$.ajax({
			type: "GET",
			url: 'configure.ajax.php',
			data: {"action":"verify","path":$(this).val(),"write":"yes"},
			success: function(response) {
				if(response == "ok")
				{
					$("#"+value+".error").html("");
				}
				else
				{
					$("#"+value+".error").html("Warning: "+response);
				}
			}
		});
	});

}

function verify_infofile(value)
{
	$("input[name="+value+"]").blur(function(){
		$.ajax({
			type: "GET",
			url: 'configure.ajax.php',
			data: {"action":"verify","file":$(this).val()},
			success: function(response) {
				if(response == "ok")
				{
					$("#"+value+".error").html("");
				}
				else
				{
					$("#"+value+".error").html("Warning: "+response);
				}
			}
		});
	});
}

function verify_java(value)
{
	$("input[name="+value+"]").blur(function(){
		$.ajax({
			type: "GET",
			url: 'configure.ajax.php',
			data: {"action":"verify","java":$(this).val()},
			success: function(response) {
				$("#"+value+".notice").html(response);
			}
		});
	});
}


$(document).ready(function(){

	verify_path("MINECRAFT_PATH");
	verify_path("PIPE_PATH");

	verify_infofile("NETDEV_PATH");
	verify_infofile("CPU_PATH");
	verify_infofile("LOAD_PATH");

	verify_java("JAVA_PATH");

});
