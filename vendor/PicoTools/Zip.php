<?php
namespace PicoTools\Zip;

/**
 * Wrapper function for ZipArchive uncompress zip file.
 * This function will overwrite existing files.
 *
 * @param string $path_to_zip
 * @param string $path_to_extract
 * @throws \Exception on opening failure
 * @return boolean
 */
function uncompress($path_to_zip, $path_to_extract) {
    $zip  = new \ZipArchive();
    $code = $zip->open($path_to_zip);

    if($code === true) {
        $zip->extractTo($path_to_extract);
        $zip->close();
    } else {
        $str_code = '';
        switch($code) {
            case \ZipArchive::ER_INCONS:
                $str_code = '\ZipArchive::ER_INCONS';
                break;
            case \ZipArchive::ER_INTERNAL:
                $str_code = '\ZipArchive::ER_INTERNAL';
                break;
            case \ZipArchive::ER_MEMORY:
                $str_code = '\ZipArchive::ER_MEMORY';
                break;
            case \ZipArchive::ER_NOENT:
                $str_code = '\ZipArchive::ER_NOENT';
                break;
            case \ZipArchive::ER_NOZIP:
                $str_code = '\ZipArchive::ER_NOZIP';
                break;
            case \ZipArchive::ER_READ:
                $str_code = '\ZipArchive::ER_READ';
                break;
            case \ZipArchive::ER_OPEN:
                $str_code = '\ZipArchive::ER_OPEN';
                break;
            case \ZipArchive::ER_EXISTS:
                $str_code = '\ZipArchive::ER_EXISTS';
                break;
            default :
                $str_code = 'unknown';
        }

        throw new \Exception(sprintf('Cannot open %s, error code: %s (%d)', $path_to_zip, $str_code, $code));
    }

    return true;
}

