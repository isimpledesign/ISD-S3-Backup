<?php
/*
Plugin Name: s3 Backup
Plugin URI: 
Description: test Amazon S3
Version: 2.0.3
Author: dsve
Author URI: reve
*/

// Some Defaults
$amazon_key				= 'sd';
$amazon_key_secret		= 'sdc';
$bucket					= 'sdc';
$folder					= 'sd';
$server					= 'sd';


// Put our defaults in the "wp-options" table
add_option("isd-amazon_key", $amazon_key);
add_option("isd-amazon_key_secret", $amazon_key_secret);
add_option("isd-bucket", $bucket);
add_option("isd-folder", $folder);
add_option("isd-server", $server);


// insert js needed for upload
function s3bk_js() {
		$x = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
		echo '<script type="text/javascript" src="'.$x.'js/main.css"></script>';
} 
add_action('admin_head','s3bk_js');


function s3bk_menu() {//Function to create our menu
  add_menu_page('s3bk', 's3bk', 'administrator', 's3bk-options', 's3bk_options');
}


function s3bk_options() {//function to build the contents for the admin window
  
    ?>
    <div class="wrap">
        <h1>s3 bk</h1>
        
<?php if ( isset($_POST['submit']) ) {
				$nonce = $_REQUEST['_wpnonce'];
				if (! wp_verify_nonce($nonce, 's3bk-updatesettings') ) die('Security check failed'); 
				if (!current_user_can('manage_options')) die(__('You cannot edit the search-by-category options.'));
				check_admin_referer('s3bk-updatesettings');
				
				// Get our new option values
			    $amazon_key	= $_POST['amazon_key'];
				$amazon_key_secret	= $_POST['amazon_key_secret'];
				$bucket	= $_POST['bucket'];
				$folder	= $_POST['folder'];
				$server	= $_POST['server'];

				// Update the DB with the new option values
				update_option("isd-amazon_key", mysql_real_escape_string($amazon_key));
				update_option("isd-amazon_key_secret", mysql_real_escape_string($amazon_key_secret));
				update_option("isd-bucket", mysql_real_escape_string($bucket));
				update_option("isd-folder", mysql_real_escape_string($folder));
				update_option("isd-server", mysql_real_escape_string($server));


}        
?>        

<form action="" method="post" id="options">
    <table class="form-table">
      <?php if (function_exists('wp_nonce_field')) { wp_nonce_field('s3bk-updatesettings'); } ?>
      <tr>
        <th scope="row" valign="top"><label for="amazon_key">amazon_key:</label></th>
        <td><input type="text" name="amazon_key" id="amazon_key" class="regular-text" value="<?php echo get_option("isd-amazon_key"); ?>"/></td>
      </tr>
      <tr>
        <th scope="row" valign="top"><label for="amazon_key_secret">amazon_key_secret:</label></th>
        <td><input type="text" name="amazon_key_secret" id="amazon_key_secret" class="regular-text" value="<?php echo get_option("isd-amazon_key_secret"); ?>"/></td>
      </tr>
      <tr>
        <th scope="row" valign="top"><label for="bucket">bucket:</label></th>
        <td><input type="text" name="bucket" id="bucket" class="regular-text" value="<?php echo get_option("isd-bucket"); ?>"/></td>
      </tr>
      <tr>
        <th scope="row" valign="top"><label for="folder">folder: <small>If none please leave blank</small></label></th>
        <td><input type="text" name="folder" id="folder" class="regular-text" value="<?php echo get_option("isd-folder"); ?>"/></td>
      </tr>
       <tr>
        <th scope="row" valign="top"><label for="server">server: - <small><?php echo $_SERVER['DOCUMENT_ROOT']; ?></small></label></th>
        
        <td><input type="text" name="server" id="server" class="regular-text" value="<?php echo get_option("isd-server"); ?>"/></td>
      </tr>

    </table>
    <br/>
    <span class="submit" style="border: 0;">
    <input type="submit" name="submit" value="Save Settings" />
    </span>
  </form>
        
        
<?php if ( isset($_POST['backup']) ) {
				$nonce = $_REQUEST['_wpnonce'];
				if (! wp_verify_nonce($nonce, 's3bk-updatesettings') ) die('Security check failed'); 
				if (!current_user_can('manage_options')) die(__('You cannot edit the search-by-category options.'));
				check_admin_referer('s3bk-updatesettings');

                 // path t backup file
                 $DelFilePath = "{$_SERVER['DOCUMENT_ROOT']}/wp-content/plugins/s3bk/files/backup.tar";
                 // check to see if file exsist if it does delete before running backup
                 if (file_exists($DelFilePath)) {
		   
		         unlink ($DelFilePath); 
		  
		         if(exec("cd {$_SERVER['DOCUMENT_ROOT']}/wp-content/plugins/s3bk/files;tar -cvpzf backup.tar ".get_option(    'isd-server')."")) { echo "done"; }  
	  }else{
	  if(exec("cd {$_SERVER['DOCUMENT_ROOT']}/wp-content/plugins/s3bk/files;tar -cvpzf backup.tar ".get_option(    'isd-server')."")) { 
echo "done";	 
}  
}
}

/// Upload to Amazon s3
if ( isset($_POST['upload']) ) {
				$nonce = $_REQUEST['_wpnonce'];
				if (! wp_verify_nonce($nonce, 's3bk-updatesettings') ) die('Security check failed'); 
				if (!current_user_can('manage_options')) die(__('You cannot edit the search-by-category options.'));
				check_admin_referer('s3bk-updatesettings');
				
				

require_once('classes/S3.php');

$date = date("F-j-Y-g-ia");

// Enter your amazon s3 creadentials
$s3 = new S3(get_option("isd-amazon_key"), get_option("isd-amazon_key_secret"));
 
$baseurl = "{$_SERVER['DOCUMENT_ROOT']}/wp-content/plugins/s3bk/files"; // files saved to files directory

if ($handle = opendir("{$_SERVER['DOCUMENT_ROOT']}/wp-content/plugins/s3bk/files")) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
				
			if ($s3->putObjectFile("{$_SERVER['DOCUMENT_ROOT']}/wp-content/plugins/s3bk/files/$file", get_option("isd-bucket"), get_option("isd-folder")."/$file", S3::ACL_PUBLIC_READ)) {
				if (file_exists($baseurl . '/' . $file)) { unlink ($baseurl . '/' . $file); }
					echo "successfully";
					
}else{
					echo "fail";
}
            
        }
    }
    closedir($handle);
}

}
?>

<form id="backup" method='post' action=''>
<?php if (function_exists('wp_nonce_field')) { wp_nonce_field('s3bk-updatesettings'); } ?>
<input type='submit' class="button" name='backup' value='backup'>


<div class="load"></div></form>

<form id="upload" method='post' action=''>
<?php if (function_exists('wp_nonce_field')) { wp_nonce_field('s3bk-updatesettings'); } ?>
<input type='submit' class="button" name='upload' value='upload'><div class="load"></div></form>
       
</div>
<?php

do_this_hourly();
}

add_action('init', 'my_activation');

add_action('my_hourly_event', 'do_this_hourly');

function my_activation() {
	wp_schedule_event(time(), 'hourly', 'my_hourly_event');
} 

function do_this_hourly() {
	
require_once('classes/S3.php');

$date = date("F-j-Y-g-ia");

// Enter your amazon s3 creadentials
$s3 = new S3(get_option("isd-amazon_key"), get_option("isd-amazon_key_secret"));

$baseurl = "{$_SERVER['DOCUMENT_ROOT']}/wp-content/plugins/s3bk/files"; // files saved to files directory

if ($handle = opendir("{$_SERVER['DOCUMENT_ROOT']}/wp-content/plugins/s3bk/files")) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
				
			if ($s3->putObjectFile("{$_SERVER['DOCUMENT_ROOT']}/wp-content/plugins/s3bk/files/$file", get_option("isd-bucket"), get_option("isd-folder")."/$date-$file", S3::ACL_PUBLIC_READ)) {
				$to      = 'sam@mikeleachcreative.co.uk';
            $subject = 'the subject';
           $message = "Backup " .date('M Y', time());
           $headers = 'From: webmaster@example.com' . "\r\n" .
           'Reply-To: webmaster@example.com' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

            mail($to, $subject, $message, $headers);
           }
            
        }
    }
    closedir($handle);
}


}
add_action('admin_menu', 's3bk_menu');