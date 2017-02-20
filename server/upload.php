<?php
/*************************************************************************
upload.php: SQLite Version: A server side component used to upload files to server through the HTTP Post.
Not intented to be used standalone.
Programmed by: Konstantinos Chertouras - chertour@gmail.com
************************************************************************/
header( 'Content-Type: text/html; charset=ISO-8859-7' );
require 'parameters.php';
$username = $password = $userError = $passError = '';
if ( isset( $_POST['username'] ) && isset( $_POST['password'] ) ) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if ( $username === $username_parameters && $password === $password_parameters ) {
        if ( isset( $_POST['size'] ) && !empty( $_POST['size'] ) ) {
            if ( $_POST['size'] > intval( $maxsize ) ) {
                print_r( 'Exceeded filesize limit.' );
                exit;
            } //$_POST['size'] > intval( $maxsize )
        } //isset( $_POST['size'] ) && !empty( $_POST['size'] )
        if ( isset( $_FILES['file']['name'] ) && !empty( $_FILES['file']['name'] ) ) {
            $name       = ( urldecode( $_FILES['file']['name'] ) );
            $enc_name   = $_FILES['file']['name'];
            $file_array = explode( ".", $name );
            if ( count( $file_array ) !== 2 ) {
                echo "No double extensions or unsafe filenames with dots allowed for security reasons.";
                exit;
            } //count( $file_array ) !== 2
            if ( count( $file_array ) === 2 ) {
                if ( !in_array( pathinfo( $name, PATHINFO_EXTENSION ), $valid_exts ) ) {
                    echo "This type of files is not allowed to upload.";
                    exit;
                } //!in_array( pathinfo( $name, PATHINFO_EXTENSION ), $valid_exts )
            } //count( $file_array ) === 2
            $size = $_FILES['file']['size'];
            if ( $_FILES['file']['size'] > intval( $maxsize ) ) {
                print_r( 'Exceeded filesize limit.' );
                exit;
            } //$_FILES['file']['size'] > intval( $maxsize )
            //Open the database lookuphash
            $db = new SQLite3( 'lookuphash.db' );
            //Create the basic table
            $db->exec( 'CREATE TABLE IF NOT EXISTS lookup (hash varchar(455) primary key, filename varchar (455), file_uploaded BLOB, file_timestamp varchar(55))' );
            $type          = $_FILES['file']['type'];
            $tmp_name      = ( $_FILES['file']['tmp_name'] );
            $error         = $_FILES['file']['error'];
            $timestamp     = $_POST['timestamp'];
            $hash          = $_POST['hash'];
            $uploaddir     = $dir . '/'; //entering into the directory ./files_to_transfer/ WE NEED THE TRAILING SLASH (/)
            $uploadfile    = ( ( $uploaddir . basename( $name ) ) );
            $file_to_store = ( file_get_contents( $tmp_name ) );
            //calculating hash and updating db
            $ctx           = hash_init( 'md5' );
            $result        = hash_update_file( $ctx, $tmp_name );
            $utfvalue      = iconv( 'ISO-8859-7', 'UTF-8', $name );
            $result        = hash_update( $ctx, $utfvalue );
            $hash          = hash_final( $ctx );
            $stmt          = $db->prepare( 'INSERT INTO lookup (hash , filename , file_uploaded , file_timestamp ) VALUES (?,?,?,?)' );
            $stmt->bindValue( 1, $hash );
            $stmt->bindValue( 2, $utfvalue );
            $stmt->bindValue( 3, $file_to_store, SQLITE3_BLOB );
            $stmt->bindValue( 4, $timestamp );
            $stmt->execute();
            $db->close();
            
        } //if(isset($_FILES['file']['name']) && !empty($_FILES['file']['name']))
    } // if($username === $username_parameters && $password === $password_parameters)
    else {
        print_r( 'Unauthorized access' );
        exit();
    }
} //if(isset($_POST['username']) && isset($_POST['password'])  )
else {
    print( "You have not  supplied any credentials... " );
    print_r( '  Exiting...' );
    exit();
}
?> 