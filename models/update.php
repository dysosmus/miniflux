<?php

namespace Model\Update;

use ZipArchive;
use RecursiveFilterIterator;
use RecursiveIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use PicoFeed\Logging;


/**
 * Synchronises the destination folder with the source folder.
 * This function will skip all unreadable files, UPDATE_DIRECTORY 
 * and symbolics links.
 * In case of error, the function stop at the first error.
 * 
 * @param  string $source
 * @param  string $destination
 * @param  string $log_flag
 * @return boolean 
 */
function synchronize_files($source, $destination, $log_flag = '')
{
    $success                 = true;
    $source_length           = strlen($source);

    $filtred_files           = array(
        '.', '..',
    );

    $filtred_paths           = array(
        path(array($source, UPDATE_DIRECTORY)), // @todo move outside
    );

    $source_iterator         = new RecursiveDirectoryIterator($source);
    $filtred_source_iterator = new FileFilterIterator($source_iterator, 
                                                      $filtred_files,
                                                      $filtred_paths);

    /* RecursiveIteratorIterator::SELF_FIRST ensure that directories comes 
     * first, so we are able to create the directories before.
     */
    $files = new RecursiveIteratorIterator(
        $filtred_source_iterator,
        RecursiveIteratorIterator::SELF_FIRST
    ); 

    foreach ($files as $file) {
        $pathname         = $file->getPathname();
        $base_pathname    = ltrim(substr($pathname, $source_length), 
                                  DIRECTORY_SEPARATOR);
        $destination_name = path(array($destination, $base_pathname));


        if($file->isReadable()) {
            if ($file->isDir()) {
                if(! is_dir($destination_name)) {
                    if( @mkdir($destination_name) ) {
                        Logging::log("[{$log_flag}] Create directory {$destination_name}.");
                    } else {
                        Logging::log("[{$log_flag}] Can not create {$destination_name}.");
                        $success = false;
                        break;
                    }
                } else {
                    Logging::log("[{$log_flag}] Skip creation {$file->getRealPath()}, directory already exsist.");
                }
            } else if($file->isFile()) { 
                if( @copy($file->getRealPath(), $destination_name)) {
                    Logging::log("[{$log_flag}] Copy {$file->getRealPath()} to {$destination_name}.");
                } else {
                    Logging::log("[{$log_flag}] Can not write {$file->getRealPath()} to {$destination_name}");
                    $success = false;
                    break;
                }
            } else {
                Logging::log("[{$log_flag}] Skip {$file->getRealPath()}, not a file or a directory.");
            }
        } else {
            Logging::log("[{$log_flag}] Skip {$file->getRealPath()}, not readable.");
        }
    }

    return $success;
}

/**
 * 
 * 
 * @param string $name update name 
 * @return boolean
 */
function update($name) 
{
    $success = true;

    if(update_exsist($name)) {
        $download_path = get_download_path($name);

        $directories    = scandir($download_path);
        $upload_payload = array_pop($directories);

        /**
         * we assume the "payload" (eg. the root of miniflux), is in the first 
         * subdirectory.
         */
        if(! in_array($upload_payload, array('.', '..'))) { 
            $upload_payload_path = path(array($download_path, $upload_payload));
            $success             = synchronize_files($upload_payload_path, 
                                                     ROOT_DIRECTORY, 'update');

        } else {
            Logging::log("[update] Can not find upload payload in {$download_path}.");
            $success = false;
        }
    } else {
         Logging::log("[update] Update {$name}, does not exsist.");
         $success = false;
    }

    return $success;
}

/**
 * 
 * @param  string $name
 * @return boolean
 */
function rollback($name)
{
    $success = false;

    if(rollback_exsist($name)) {
        $rollback_path = get_rollback_path($name);
        $success       = synchronize_files($rollback_path, 
                                            ROOT_DIRECTORY, 'rollback');
    } else {
         Logging::log("Rollback {$name}, does not exsist.");
    }

    return $success;
}

/**
 * 
 * 
 * @return string path of fetched file or null 
 */
function fetch($url) 
{
    $temporary_directory = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') 
                                                     : sys_get_temp_dir();
    $temporary_file      = tempnam($temporary_directory, 'miniflux-update-');

    if(file_put_contents($temporary_file, file_get_contents($url)) === false) {
        Logging::log("[fetch] Unable to fetch {$url}.");
        $temporary_file = null;
    }

    return $temporary_file;
}

/**
 * 
 * @param  string $zip_path
 * @return string name of the update 
 */
function uncompress($zip_path)
{
    $update_name = time();
    $zip         = new ZipArchive;

    if(@is_readable($zip_path)) {
        if ($zip->open($zip_path) === true) {
            $target = get_download_path($update_name);
            $zip->extractTo($target);
            $zip->close();
        } else {
            $update_name = null;

            Logging::log("[uncompress] Can not extract {$zip_path} to {$target}.");
        }
    } else {
        $update_name = null;

        Logging::log("[uncompress] File {$zip_path} does not exsist.");
    } 

    return $update_name;
}


/**
 * Make a copy of all current files in the rollback folder.
 *
 * @return string name of the rollback
 */
function freeze() 
{    
    $rollback_name = time();
    
    if(create_rollback_directory($rollback_name)) {
        $rollback_path = get_rollback_path($rollback_name);
        $success       = synchronize_files(ROOT_DIRECTORY, 
                                           $rollback_path, 
                                           'freeze');
    } else {
        $rollback_name = null;
        Logging::log("[freeze] Can not create rollback directory.");
    }

    return $rollback_name;
}

/**
 * 
 * @param  string $rollback_name
 * @return boolean true if the directory creation is successful.
 */
function create_rollback_directory($rollback_name)
{
    $directory = get_rollback_path($rollback_name);

    return @mkdir($directory, 0770, true);
}

/**
 * 
 * @param  string $rollback_name
 * @return boolean
 */
function rollback_exsist($rollback_name) 
{
    $path = get_rollback_path($rollback_name);

    return @is_dir($path) && @is_readable($path);
}

/**
 * 
 * @param  string $update_name
 * @return boolean
 */
function update_exsist($update_name) 
{
    $path = get_download_path($update_name);

    return @is_dir($path) && @is_readable($path);
}

/**
 * 
 * @param  string $update_name
 * @param  string $for_file
 * @return string
 * @todo refactor
 */
function get_rollback_path($rollback_name, $for_file = null)
{
    $tokens = array(
        ROOT_DIRECTORY,
        UPDATE_ROLLBACK_DIRECTORY,
        $rollback_name
    );

    if($for_file != null) {
        $tokens[] = $for_file;
    }
    
    return path($tokens);
}

/**
 * 
 * @param  string $update_name
 * @param  string $for_file
 * @return string
 * @todo refactor
 */
function get_download_path($update_name, $for_file = null)
{   
    $tokens = array(
        ROOT_DIRECTORY,
        UPDATE_DOWNLOAD_DIRECTORY,
        $update_name
    );

    if($for_file != null) {
        $tokens[] = $for_file;
    }
    
    return path($tokens);
}

/**
 * 
 * @param  string $for_file
 * @return string
 * @todo refactor
 */
function get_update_path($for_file = null) {
    $tokens = array(
        ROOT_DIRECTORY,
        UPDATE_DIRECTORY
    );

    if($for_file != null) {
        $tokens[] = $for_file;
    }

    return path($tokens);
}
/**
 * Path builder helper, build a path from a array with the system directory 
 * separator and ensures no trailing directory separator.
 * 
 * @param  array $tokens
 * @return string 
 */
function path($tokens)
{
    return rtrim(implode(DIRECTORY_SEPARATOR, $tokens), DIRECTORY_SEPARATOR);
}

/**
 * 
 */
class FileFilterIterator extends RecursiveFilterIterator {

    /**
     * 
     * @var array
     */
    private $filename_excludes = array();

    /**
     * 
     * @var array
     */
    private $path_excludes     = array();

    /**
     * 
     * @param RecursiveDirectoryIterator $recursiveIter
     * @param array                      $filename_excludes 
     * @param array                       $path_excludes 
     */
    public function __construct(RecursiveDirectoryIterator $recursiveIter, 
                                $filename_excludes = array(), 
                                $path_excludes     = array()) 
    {
        $this->filename_excludes = $filename_excludes;
        $this->path_excludes     = $path_excludes;

        parent::__construct($recursiveIter);
    }

    public function accept() 
    {   
        $accept = !in_array(
            $this->current()->getFilename(),
            $this->filename_excludes
        );

        if($accept) {
            $real_path = $this->current()->getRealPath();

            foreach ($this->path_excludes as $path) {
                if(strpos($real_path, $path) === 0) {
                    $accept = false;
                    break;
                }
            }
        }

        return $accept;
    }

    public function getChildren() 
    {
        return new self($this->getInnerIterator()->getChildren(), 
                        $this->filename_excludes,
                        $this->path_excludes);
    }

}