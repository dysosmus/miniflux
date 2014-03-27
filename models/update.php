<?php

namespace Model\Update;

use RecursiveFilterIterator;
use RecursiveIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use PicoFeed\Logging;

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
     * @param RecursiveIterator $recursiveIter
     * @param array             $filename_excludes 
     * @param array             $path_excludes 
     */
    public function __construct(RecursiveIterator $recursiveIter, 
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

function rollback($to)
{

}


function fetch() 
{

}

/**
 * Make a copy of all current files in the rollback folder.
 * This function will skip all unreadable files, UPDATE_DIRECTORY 
 * and symbolics links.
 *
 * @return string name of rollback
 */
function freeze_current_files() 
{    
    $rollback_name = time();
    
    if(create_rollback_directory($rollback_name)) {
        $filtred_files              = array(
            '.', '..',
        );

        $filtred_paths              = array(
            get_update_path(),
        );
 
        $directory_iterator         = new RecursiveDirectoryIterator(ROOT_DIRECTORY);
        $filtred_directory_iterator = new FileFilterIterator($directory_iterator, 
                                                             $filtred_files,
                                                             $filtred_paths);

        /* RecursiveIteratorIterator::SELF_FIRST ensure that directories comes 
         * first, so we are able to create the directories before.
         */
        $files = new RecursiveIteratorIterator(
            $filtred_directory_iterator,
            RecursiveIteratorIterator::SELF_FIRST
        ); 

        $root_directory_path_length = strlen(ROOT_DIRECTORY);

        foreach ($files as $file) {
            $pathname            = $file->getPathname();
            $base_pathname       = ltrim(substr($pathname, 
                                                $root_directory_path_length), 
                                         DIRECTORY_SEPARATOR);

            $destination_name    = get_rollback_path($rollback_name, 
                                                     $base_pathname);

            if($file->isReadable()) {
                if ($file->isDir()) {
                    if(! is_dir($destination_name)) {
                        mkdir($destination_name);
                    } else {
                         Logging::log("Skip creation {$file->getRealPath()}, directory already exsist.");
                    }
                } else if($file->isFile()) { 
                    copy($pathname, $destination_name);
                } else {
                    Logging::log("Skip {$file->getRealPath()}, not a file or a directory.");
                }
            } else {
                Logging::log("Skip {$file->getRealPath()}, not readable.");
            }
        }
    } else {
        $rollback_name = null;
        Logging::log("Can not create rollback directory.");
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
    
    return implode(DIRECTORY_SEPARATOR, $tokens);
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
    
    return implode(DIRECTORY_SEPARATOR, $tokens);
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

    return implode(DIRECTORY_SEPARATOR, $tokens);
}
