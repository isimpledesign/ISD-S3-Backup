$("#backup").submit(function(event){
event.preventDefault(); 
alert("test");
$(".load").show();
$(".load").html('<img src="images/ajax-loader.gif" /> <h4>You backup is in progress <i>do not refresh browser</i> this might take a while go make a cup off tea ;)</h4>'); 
$.post("http://gibbonsdownandcourt.org/wp-content/plugins/s3bk/backup.php", function(data) {															 
		if($.trim(data) == 'done') {
		$(".message").html('<h5>Congratulation your backup is now complete ;)</h5>');
		$(".load").hide();
		$("#backup").hide();
		$("#upload").show();
		}
});
}); 
 
//backup to amazon s3

$("#upload").submit(function(event){
event.preventDefault();
$(".message").hide();
$(".load").show();
$(".load").html('<img src="images/ajax-loader.gif" /> <h4>You Amazon upload is in progress <i>do not refresh browser</i> this might take a while go make another cup off tea ;)</h4>'); 
$.post("http://gibbonsdownandcourt.org/wp-content/plugins/s3bk/upload.php", function(data) {															 
		if($.trim(data) == 'successfully') {
		$(".message").show();
		$(".message").html('<h5>Congratulation your backup has been uploaded to your Amazon S3 Account ;)</h5>');
		$(".load").hide();
		}
		if($.trim(data) == 'fail') {
		$(".message").html('<h5>Their was an error please debug</h5>');
		$(".load").hide();
		}
});
});