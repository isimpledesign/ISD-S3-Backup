<?php 

require_once('classes/S3.php');

// Enter your amazon s3 creadentials
$s3 = new S3('AKIAITQQDYV5WK4LLYWQ', 'wCl2ERGtejtdwVM/DT1ubYqJi7qumzy5BZAoRXUU');
 
$baseurl = $_SERVER['DOCUMENT_ROOT'] . "/amazons3bk/files"; // files saved to files directory

if ($handle = opendir('./files/')) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
				
			if ($s3->putObjectFile("files/$file", "starthostbackup", "isd/$file", S3::ACL_PUBLIC_READ)) {
				if (file_exists($baseurl . '/' . $file)) { unlink ($baseurl . '/' . $file); }
					echo "successfully";
					
}else{
					echo "fail";
}
            
        }
    }
    closedir($handle);
}
 
?>