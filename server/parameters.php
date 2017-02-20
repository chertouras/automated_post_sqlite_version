<?php 
#files to exclude from file list
$array_exclude = array('server_files.json');

#Here you will search for files
$dir    = './files_to_transfer';

#your credentials

$username_parameters='xxxxxxxxxx';
$password_parameters = 'xxxxxxxxx';

//The whitelist of the files to allowable to be transfered
//The whitelist of the files to allowable to be transfered
$valid_exts = array('doc', 'pdf' , 'docx' , 'xlsx' , 'xls' , 'ppt' , 'pptx' , 'rtf' , 'txt' , 'odp' );

$path_of_files = "/files_to_transfer/";

$maxsize ="1000000"; //max file size allowed to be uploaded

$private_folder ="all_files/";
?>