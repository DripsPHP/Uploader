<?php

/**
 * Created by Prowect
 * Author: Lars Müller
 * Date: 28.06.15
 */
namespace Drips\Uploader;

use Exception;

/**
 * class PdfUploader.
 *
 * used for uploading PDF-documents.
 */
class PdfUploader extends Uploader
{

    /**
     * creates a new uploader-instance.
     *
     * @throws \DripsPHP\Converter\UnitNotFoundException
     */
    public function __construct()
    {
        parent::__construct();
        $this->filetypes = array('pdf');
    }
    
}