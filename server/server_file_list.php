<?php
/**************************************************************************
server_file_list.php: SQLIte Version. A server side component used to create the server files list.
Not intented to be used standalone.
Programmed by: Konstantinos Chertouras - chertour@gmail.com
************************************************************************/

require 'parameters.php';
/*
Allow access only if username and password are supplied. 
These values are kept in parameters.php
*/
$username = $password = $userError = $passError = '';
if ( isset( $_POST['username'] ) && isset( $_POST['password'] ) ) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if ( $username === $username_parameters && $password === $password_parameters ) {
        $db = new SQLite3( 'lookuphash.db' );
        $db->exec( 'CREATE TABLE IF NOT EXISTS lookup (hash varchar(455) primary key, filename varchar (455), file_uploaded BLOB, file_timestamp varchar(55))' );
        $sql     = "SELECT hash, filename FROM lookup";
        $results = $db->query( $sql );
        $keys    = array();
        $names   = array();
        $files   = array();
        while ( $res = $results->fetchArray( SQLITE3_ASSOC ) ) {
            //insert row into array
            array_push( $keys, $res['hash'] );
            array_push( $names, $res['filename'] );
        } //$res = $results->fetchArray( SQLITE3_ASSOC )
        $files          = array_combine( $keys, $names );
        /*Json encode the array and send back to python for it to be parsed. Keep the contents in a file (server_files.json)*/
        $files_json_enc = json_encode( $files, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        file_put_contents( "./files_to_transfer/server_files.json", ( $files_json_enc ) );
        print_r( $files_json_enc );
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
$db->close();
?>            
