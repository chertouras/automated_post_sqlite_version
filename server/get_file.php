<?php
$hash = $_GET['id'];
define( 'R_MD5_MATCH', '/^[a-f0-9]{32}$/i' );
if ( preg_match( R_MD5_MATCH, $hash ) ) {
    $hash      = filter_var( $hash, FILTER_SANITIZE_STRING );
    $db        = new SQLite3( 'lookuphash.db' );
    $statement = $db->prepare( 'SELECT * FROM lookup WHERE hash = ?' );
    $statement->bindValue( '1', $hash );
    $result = $statement->execute();
    $res    = $result->fetchArray( SQLITE3_ASSOC );
    header( 'Content-type: application/octet-stream' );
    $filename = ( $res['filename'] );
    header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
    $db->close();
    echo ( ( $res['file_uploaded'] ) );
} //preg_match( R_MD5_MATCH, $hash )
else {
    echo "It does not match.";
    exit;
}
?>