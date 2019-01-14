<?php
define("UPLOAD_ERR_EMPTY", 5);

function smarty_modifier_ziperrstr($zip_errno)
{
    $zip_errors = array(
        ZipArchive::ER_MULTIDISK => 'Multi-disk zip archives not supported.',
        ZipArchive::ER_RENAME => 'Renaming temporary file failed.',
        ZipArchive::ER_CLOSE => 'Closing zip archive failed',
        ZipArchive::ER_SEEK => 'Seek error',
        ZipArchive::ER_READ => 'Read error',
        ZipArchive::ER_WRITE => 'Write error',
        ZipArchive::ER_CRC => 'CRC error',
        ZipArchive::ER_ZIPCLOSED => 'Containing zip archive was closed',
        ZipArchive::ER_NOENT => 'No such file.',
        ZipArchive::ER_EXISTS => 'File already exists',
        ZipArchive::ER_OPEN => 'Cannot open file',
        ZipArchive::ER_TMPOPEN => 'Failure to create temporary file.',
        ZipArchive::ER_ZLIB => 'Zlib error',
        ZipArchive::ER_MEMORY => 'Memory allocation failure',
        ZipArchive::ER_CHANGED => 'Entry has been changed',
        ZipArchive::ER_COMPNOTSUPP => 'Compression method not supported.',
        ZipArchive::ER_EOF => 'Premature EOF',
        ZipArchive::ER_INVAL => 'Invalid argument',
        ZipArchive::ER_NOZIP => 'Not a zip archive',
        ZipArchive::ER_INTERNAL => 'Internal error',
        ZipArchive::ER_INCONS => 'Zip archive inconsistent',
        ZipArchive::ER_REMOVE => 'Cannot remove file',
        ZipArchive::ER_DELETED => 'Entry has been deleted'
    );
     
    if (array_key_exists($zip_errno, $zip_errors))
    {
        /* Get the text parameter. */
        $text = $zip_errors[$zip_errno];
    }
    else
    {
        $text = "Unknown error code " . $zip_errno;
    }

    return $text;
}

?>
