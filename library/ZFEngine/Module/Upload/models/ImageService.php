<?php

class  ZFEngine_Module_Upload_Model_ImageService extends ZFEngine_Model_Service_Database_Abstract
{
    /**
     * Путь к изображению относительно public
     */
    public static $publicUploadPath = '/upload/images/';

    /**
     * Suffix for preview
     */
    const SUFFIX_PREVIEW_IMAGE = '-preview';

    /**
     * Suffix for thumbnail
     */
    const SUFFIX_THUMBNAIL_IMAGE = '-thumb';

    /**
     * Original image sizes
     * @var array
     */
    public static $originalSize = array('width' => 450, 'height' => 450, 'saveProportions' => true);

    /**
     * Preview image sizes
     * @var array
     */
    public static $previewSize = array('width' => 150, 'height' => 150, 'saveProportions' => true);

    /**
     * Preview image sizes
     * @var array
     */
    public static $thumbnailSize = array('width' => 50, 'height' => 50, 'saveProportions' => true);

    /**
     * Возвращает инициализованый елемент для загрузки изображения
     * @param string $name
     * @return Zend_Form_Element_File 
     */
    public function getImageFormElement($name)
    {
        $imageFilename = new Zend_Form_Element_File($name);
        $imageFilename
            ->addValidator('IsImage')
            ->setDestination($this->getImageAbsoluteUploadPath())
            ->addValidator('Size', false, 1048576)
            ->addValidator('Extension', false, 'jpg,jpeg,png,gif');
        $imageFilename->addFilter(new ZFEngine_Filter_File_SetUniqueName(array(
            'targetDir' => $this->getImageAbsoluteUploadPath(),
            'nameLength' => 10
        )));
        $imageFilename->addFilter(new ZFEngine_Filter_File_ImageResize(array(
            'width' => $this->getOriginalSize('width'),
            'height' =>$this->getOriginalSize('height'),
            'saveProportions' => self::$originalSize['saveProportions'] )
        ));
        return $imageFilename;
    }

    /**
     * Устанавливает путь для загрузки
     *
     * @return void
     */
    public static function setPublicUploadPath($publicUploadPath) {
        self::$publicUploadPath = $publicUploadPath;
    }

    /**
     * Устанавливает размер для оригинальной картинки
     *
     * @return void
     */
    public static function setOriginalSize($originalSize) {
        self::$originalSize = $originalSize;
    }

    /**
     * Устанавливает размер для превью
     *
     * @return void
     */
    public static function setPreviewSize($previewSize) {
        self::$previewSize = $previewSize;
    }

    /**
     * Устанавливает размер для Thumbnail
     *
     * @return void
     */
    public static function setThumbnailSize($thumbnailSize) {
        self::$thumbnailSize = $thumbnailSize;
    }


    /**
     * Устанавливает размер для оригинальной картинки
     *
     * @return void
     */
    public static function getOriginalSize($type) {
        return self::$originalSize[$type];
    }

    /**
     * Устанавливает размер для превью
     *
     * @return void
     */
    public static function getPreviewSize($type) {
        return self::$previewSize[$type];
    }

    /**
     * Устанавливает размер для Thumbnail
     *
     * @return void
     */
    public static function getThumbnailSize($type) {
        return self::$thumbnailSize[$type];
    }

    /**
     * Uploaded image postprocessing
     *
     * @param string $imageFilename
     * @return Brand
     */
    public static function postprocessing($imageFilename, $preview = true, $thumbnail = true)
    {
        // Load image
        $image = ZFEngine_Image::factory(self::getImageFullPath($imageFilename));

        // Create thumbnail and preview image
        if ($preview) {
            self::createImagePreview($imageFilename, $image);
        }
        if ($thumbnail) {
            self::createImageThumbnail($imageFilename, $image);
        }
        
        return true;
    }



    /**
     * Create image thumbnail and save it to file
     *
     * @param ZFEngine_Image_Adapter_Abstract $image
     * @param string $imageFilename
     * @return void
     */
    public static function createImageThumbnail($imageFilename, ZFEngine_Image_Adapter_Abstract $image)
    {
        $imageThumbFileName = self::getImageAbsoluteUploadPath()
                              . DIRECTORY_SEPARATOR
                              . ZFEngine_File::addSuffixToFileName($imageFilename, self::SUFFIX_THUMBNAIL_IMAGE);

        $image->resize(self::$thumbnailSize['width'], self::$thumbnailSize['height'], self::$thumbnailSize['saveProportions'])
              ->saveAs($imageThumbFileName, 95);
    }

    /**
     * Create image preview and save it to file
     *
     * @param ZFEngine_Image_Adapter_Abstract $image
     * @param string $imageFilename
     * @return void
     */
    public static function createImagePreview($imageFilename, ZFEngine_Image_Adapter_Abstract $image)
    {
        $imageThumbFileName = self::getImageAbsoluteUploadPath()
                              . DIRECTORY_SEPARATOR
                              . ZFEngine_File::addSuffixToFileName($imageFilename, self::SUFFIX_PREVIEW_IMAGE);
        
        $image->resize(self::$previewSize['width'], self::$previewSize['height'], self::$previewSize['saveProportions'])
              ->saveAs($imageThumbFileName, 95);
    }

    /**
     * Return absolute path to the image file
     * @param string $imageFilename
     * @param string $type 'original', 'preview' or 'thumbnail'
     *
     * @return string|NULL
     */
    public static function getImageFullPath($imageFilename, $type = 'original')
    {
        if (strlen($imageFilename)) {
            switch ($type) {
                default:
                case 'original':
                    return self::getImageAbsoluteUploadPath() . DIRECTORY_SEPARATOR . $imageFilename;
                case 'preview':
                    return self::getImageAbsoluteUploadPath() . DIRECTORY_SEPARATOR
                        . ZFEngine_File::addSuffixToFileName($imageFilename, self::SUFFIX_PREVIEW_IMAGE);
                case 'thumbnail':
                    return self::getImageAbsoluteUploadPath() . DIRECTORY_SEPARATOR
                        . ZFEngine_File::addSuffixToFileName($imageFilename, self::SUFFIX_THUMBNAIL_IMAGE);
            }
        }
        return NULL;
    }


    /**
     * Возвращает абсолютный путь к папке с оригинальными изображениями
     *
     * @return string
     */
    public static function getImageAbsoluteUploadPath()
    {
        return realpath(APPLICATION_PATH . '/../public' . self::$publicUploadPath);
    }

     /**
     * Delete images
     *
     * @return void
     */
    public static function unlinkImages($imageFilename)
    {
        if (!strlen($imageFilename)) {
            return null;
        }

        if (file_exists(self::getImageFullPath($imageFilename))) {
            unlink(self::getImageFullPath($imageFilename));
        }
        if (file_exists(self::getImageFullPath($imageFilename, 'preview'))) {
            unlink(self::getImageFullPath($imageFilename, 'preview'));
        }
        if (file_exists(self::getImageFullPath($imageFilename, 'thumbnail'))) {
            unlink(self::getImageFullPath($imageFilename, 'thumbnail'));
        }
    }
}