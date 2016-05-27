<?php

/**
 * Created by Prowect
 * Author: Lars Müller
 * Date: 29.04.15
 */
namespace Drips\Uploader;

//use DripsPHP\Converter\FilesizeConverter;
use Exception;
//use Drips\HTTP\Request;

/**
 * class Uploader.
 *
 * used for uploading files
 */
class Uploader {
    protected $files = array();
    protected $tmpdir = 'tmp/.upload/';

    protected $filetypes = array();
    protected $filesize = 0;
    
    protected $inputArray = null;
    protected $inputStream = "php://input";

    /**
     * creates a new uploader-instance.
     *
     * @throws \DripsPHP\Converter\UnitNotFoundException
     */
    public function __construct() {
        // set tmp upload directory
        $this->inputArray = $_FILES;
        if (is_dir($this->tmpdir)) {
            ini_set('upload_tmp_dir', $this->tmpdir);
        }
        $this->filesize = 1048576;//FilesizeConverter::convert(1, 'mib', 'byte');
    }


    /**
     * set allowed filetypes.
     *
     * @param $filetypes
     */
    public function setFiletypes($filetypes) {
        if (is_array($filetypes)) {
            $this->filetypes = $filetypes;
        } else {
            $this->filetypes[] = $filetypes;
        }
    }

    /**
     * returns allowed filetypes.
     *
     * @return array
     */
    public function getFiletypes() {
        return $this->filetypes;
    }

    /**
     * returns if $filetype is allowed or not.
     *
     * @param $filetype
     *
     * @return bool
     */
    public function isAllowedFiletype($filetype) {
        return empty($this->filetypes) || in_array(strtolower($filetype), $this->filetypes);
    }

    /**
     * set max filesize in bytes.
     *
     * @param $byte
     */
    public function setFilesize($byte) {
        $this->filesize = $byte;
    }

    /**
     * return max filesize in bytes.
     *
     * @return int|mixed
     */
    public function getFilesize() {
        return $this->filesize;
    }

    /**
     * returns if $filesize is smaller than max filesize.
     *
     * @param $filesize
     *
     * @return bool
     */
    public function isAllowedFilesize($filesize) {
        return $filesize <= $this->filesize;
    }

    /**
     * check function for filetype, filesize, and other restrictions.
     *
     * @param $file
     *
     * @return bool
     *
     * @throws UploadFileImageIsToBigException
     * @throws UploadFileIsToBigException
     * @throws UploadFiletypeNotAllowedException
     */
    public function checkFile($file) {
        $filetype = $file['filetype'];
        if (!$this->isAllowedFiletype($filetype)) {
            throw new UploadFiletypeNotAllowedException($filetype);
        }
        if (!$this->isAllowedFilesize($file['size'])) {
            throw new UploadFileIsToBigException($this->filesize);
        }

        return true;
    }

    /**
     * returns filetype of a $filename.
     *
     * @param $filename
     *
     * @return string
     */
    public function getFiletype($filename) {
        $fileParts = explode('.', $filename);
        if (count($fileParts) > 1) {
            return array_pop($fileParts);
        }

        return '';
    }

    /**
     * returns if upload via post-request or pu-request was successful.
     *
     * @param $name                    Key for $inputArray when using POST, filename for PUT
     * @param $destination_dir         Absolute Path
     * @param bool $override_existing
     *
     * @return bool
     *
     * @throws UploadErrorException
     * @throws UploadFileIsToBigException
     * @throws UploadFileNameNotFoundException
     * @throws UploadFiletypeNotAllowedException
     * @throws UploadOverrideNotAllowedException
     */
    public function upload($name, $destination_dir, $override_existing = true) {
        //IF $inputArray is set and not empty, make a post upload
        if (isset($this->inputArray) && !empty($this->inputArray)) {
            if (array_key_exists($name, $this->inputArray)) {
                // multiple uploads?
                if (is_array($this->inputArray[$name]['tmp_name'])) {
                    for ($i = 0; $i < count($this->inputArray[$name]['tmp_name']); $i++) {
                        $this->files[] = array(
                            'name' => $name,
                            'filename' => $this->inputArray[$name]['name'][$i],
                            'tmpname' => $this->inputArray[$name]['tmp_name'][$i],
                            'type' => $this->inputArray[$name]['type'][$i],
                            'error' => $this->inputArray[$name]['error'][$i],
                            'size' => $this->inputArray[$name]['size'][$i],
                            'filetype' => $this->getFiletype($this->inputArray[$name]['name'][$i]),
                        );
                    }
                } else {
                    $this->files[] = array(
                        'name' => $name,
                        'filename' => $this->inputArray[$name]['name'],
                        'tmpname' => $this->inputArray[$name]['tmp_name'],
                        'type' => $this->inputArray[$name]['type'],
                        'error' => $this->inputArray[$name]['error'],
                        'size' => $this->inputArray[$name]['size'],
                        'filetype' => $this->getFiletype($this->inputArray[$name]['name']),
                    );
                }

                foreach ($this->files as $file) {
                    if ($this->checkFile($file)) {
                        $path = $destination_dir.'/'.$file['filename'];
                        if ($file['error'] != UPLOAD_ERR_OK) {
                            throw new UploadErrorException($file['error'].','.$file['filename']);
                        }
                        if (is_file($path) && !$override_existing) {
                            throw new UploadOverrideNotAllowedException($file['filename']);
                        }
                        if (move_uploaded_file($file['tmpname'], $path)) {
                            return true;
                        }
                    }
                }
            } else {
                throw new UploadFileNameNotFoundException();
            }
            //ELSE lets pretend it's a PUT
        } else {
            $path = $destination_dir.'/'.$name;
            //Theoretically not necessary, but users dare to be stupid
            if (is_file($path) && !$override_existing) {
                throw new UploadOverrideNotAllowedException($name);
            }

            /* PUT data comes in on the stdin stream */
            $putdata = fopen($this->inputStream, "r");

            /* Open a file for writing */
            $fp = fopen($path, "w");

            /* Read the data 1 KB at a time
               and write to the file */
            while ($data = fread($putdata, 1024)) {
                fwrite($fp, $data);
            }

            /* Close the streams */
            if (!fclose($fp)) {
                throw new Exception("Can't close tmp file");
            }
            if (!fclose($putdata)) {
                throw new Exception("Can't close input stream");
            }

            return true;
        }


        return false;
    }

}

class UploadFileNameNotFoundException extends Exception
{
}

class UploadErrorException extends Exception
{
}

class UploadOverrideNotAllowedException extends Exception
{
}

class UploadFiletypeNotAllowedException extends Exception
{
}

class UploadFileIsToBigException extends Exception
{
}

class UploadFileImageIsToBigException extends Exception
{
}

class UploadFileImageIsToSmallException extends Exception
{
}
