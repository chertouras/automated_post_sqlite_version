<?php
require 'parameters.php';
$db      = new SQLite3( 'lookuphash.db' );
$sql     = "SELECT * FROM lookup";
$results = $db->query( $sql );
//create directory to store files 
if ( !file_exists( $dir_all_files ) ) {
    mkdir( $dir_all_files, 0777, true );
} //!file_exists( $dir_all_files )
//Clear files is there are existing ones
$di = new RecursiveDirectoryIterator( $dir_all_files, FilesystemIterator::SKIP_DOTS );
if ( iterator_count( $di ) !== 0 ) {
    foreach ( new DirectoryIterator( $dir_all_files ) as $fileInfo ) {
        if ( !$fileInfo->isDot() ) {
            unlink( $fileInfo->getPathname() );
        } //!$fileInfo->isDot()
    } //new DirectoryIterator( $dir_all_files ) as $fileInfo
} //iterator_count( $di ) !== 0
$zip          = new ZipArchive();
$filename_zip = "all_files.zip";
if ( $zip->open( $private_folder . $filename_zip, ZipArchive::CREATE ) !== TRUE ) {
    exit( "cannot open <$filename>\n" );
} //$zip->open( $private_folder . $filename_zip, ZipArchive::CREATE ) !== TRUE
while ( $res = $results->fetchArray( 1 ) ) {
    //insert row into array
    $filename = iconv( 'UTF-8', 'ISO-8859-7', $res['filename'] ); //windows
    file_put_contents( $private_folder . $filename, $res['file_uploaded'] );
    $zip->addFile( $private_folder . $filename, iconv( 'ISO-8859-7', 'UTF-8', $filename ) );
} //$res = $results->fetchArray( 1 )
echo "Προστέθηκαν: " . $zip->numFiles . " αρχεία. <br>";
if ( $zip->status == 0 ) {
    echo "To αρχειο zip δημιουργήθηκε με επιτυχία <br>";
    echo "<form method=\"get\" action=\"" . $private_folder . "\all_files.zip\">";
    echo " <button type='submit' id = \"download\">Download</button> ";
    echo "</form>";
} //$zip->status == 0
else
    echo "Υπήρξε κάποιο πρόβλημα. Παρακαλώ προσπαθήστε αργότερα. ";
$zip->close();
$db->close();
?>