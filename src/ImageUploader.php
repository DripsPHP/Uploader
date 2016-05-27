<?php

/**
 * Created by Prowect
 * Author: Lars Müller
 * Date: 29.04.15
 */
namespace Drips\Uploader;

use Exception;

/**
 * class Uploader.
 *
 * used for uploading files
 */
class ImageUploader extends Uploader
{

    protected $imgWidth = 0;
    protected $imgHeight = 0;
    protected $minWidth = 0;
    protected $minHeight = 0;

    /**
     * creates a new uploader-instance.
     *
     * @throws \DripsPHP\Converter\UnitNotFoundException
     */
    public function __construct()
    {
        parent::__construct();
        $this->filetypes = array('png', 'jpg', 'gif', 'jpeg');
    }

    /**
     * sets max image width.
     *
     * @param $width
     */
    public function setMaxImageWidth($width)
    {
        $this->imgWidth = $width;
    }

    /**
     * returns max image width.
     *
     * @return int
     */
    public function getMaxImageWidth()
    {
        return $this->imgWidth;
    }

    /**
     * sets max image height.
     *
     * @param $height
     */
    public function setMaxImageHeight($height)
    {
        $this->imgHeight = $height;
    }

    /**
     * returns max image height.
     *
     * @return int
     */
    public function getMaxImageHeight()
    {
        return $this->imgHeight;
    }

    /**
     * set min image width.
     *
     * @param $width
     */
    public function setMinImageWidth($width)
    {
        $this->minWidth = $width;
    }

    /**
     * returns min image width.
     *
     * @return int
     */
    public function getMinImageWidth()
    {
        return $this->minWidth;
    }

    /**
     * sets min image height.
     *
     * @param $height
     */
    public function setMinImageHeight($height)
    {
        $this->minHeight = $height;
    }

    /**
     * returns min image height.
     *
     * @return int
     */
    public function getMinImageHeight()
    {
        return $this->minHeight;
    }

    /**
     * returns if image is smaller than maxwidth and maxheight.
     *
     * @param $tmpfile
     *
     * @return bool
     */
    public function isAllowedImageSize($tmpfile)
    {
        $img = getimagesize($tmpfile);
        if ($img[2] != 0) {
            if ($this->imgWidth == 0 && $this->imgHeight == 0) {
                return true;
            }

            return $img[0] >= $this->minWidth && $img[1] >= $this->minHeight;
        }

        return false;
    }

    /**
     * returns if image is bigger than minwidth and minheight.
     *
     * @param $tmpfile
     *
     * @return bool
     */
    public function isAllowedMinImageSize($tmpfile)
    {
        $img = getimagesize($tmpfile);
        if ($img[2] != 0) {
            if ($this->minWidth == 0 && $this->minHeight == 0) {
                return true;
            }

            return $img[0] <= $this->imgWidth && $img[1] <= $this->imgHeight;
        }

        return false;
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
    public function checkFile($file)
    {
        if(parent::checkFile($file)) {
            // is image upload?
            if (!$this->isAllowedImageSize($file['tmpname'])) {
                throw new UploadFileImageIsToBigException($this->imgWidth.'x'.$this->imgHeight);
            }
            if (!$this->isAllowedMinImageSize($file['tmpname'])) {
                throw new UploadFileImageIsToSmallException($this->imgWidth.'x'.$this->imgHeight);
            }
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
    public function getFiletype($filename)
    {
        $fileParts = explode('.', $filename);
        if (count($fileParts) > 1) {
            return array_pop($fileParts);
        }

        return '';
    }

    /**
     * returns if upload via post or put was successful.
     *
     * @param $name
     * @param $destination_dir
     * @param bool $override_existing
     *
     * @return bool
     *
     * @throws UploadErrorException
     * @throws UploadFileImageIsToBigException
     * @throws UploadFileIsToBigException
     * @throws UploadFileNameNotFoundException
     * @throws UploadFiletypeNotAllowedException
     * @throws UploadOverrideNotAllowedException
     */
    public function upload($name, $destination_dir, $override_existing = true)
    {
        return parent::upload($name, $destination_dir, $override_existing);
    }
    
}