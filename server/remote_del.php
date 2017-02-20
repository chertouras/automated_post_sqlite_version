<?php
/**************************************************************************

remote_del.php (SQLITE version): A server side component used to delete files from server
Not intented to be used standalone.
Programmed by: Konstantinos Chertouras - chertour@gmail.com
************************************************************************/
header( 'Content-Type: text/html; charset=utf-8' );
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
        $hash_filename = $_POST['hash_filename'];
        if ( $hash_filename != NULL ) {
            $db   = new SQLite3( 'lookuphash.db' );
            $stmt = $db->prepare( 'Select * from lookup WHERE hash= ?' );
            $stmt->bindValue( 1, $_POST['hash_filename'] );
            $res      = $stmt->execute();
            $row      = $res->fetchArray( 1 ); // fetch associatively
            $filename = $row['filename'];
            $stmt     = $db->prepare( 'DELETE FROM lookup WHERE hash= ?' );
            $stmt->bindValue( 1, $_POST['hash_filename'] );
            $stmt->execute();
            print_r( $filename );
            $db->close();
            $db = new SQLite3( 'lookuphash.db' );
            $db->exec( "vacuum" );
            $db->close();
        } //$value != NULL
        else {
            print_r( 'Nothing to delete. Will exit...' );
            exit();
        }
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