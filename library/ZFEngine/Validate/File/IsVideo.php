<?php

/**
 * @see Zend_Validate_File_MimeType
 */
require_once 'Zend/Validate/File/MimeType.php';

/**
 * Validator that check file if file is image or not
 */
class ZFEngine_Validate_File_IsVideo extends Zend_Validate_File_MimeType
{
    /**
     * @const string Error constants
     */
    const FALSE_TYPE   = 'fileIsVideoFalseType';
    const NOT_DETECTED = 'fileIsVideoNotDetected';
    const NOT_READABLE = 'fileIsVideoNotReadable';

    /**
     * @var array Error message templates
     */
    protected $_messageTemplates = array(
        self::FALSE_TYPE   => "File '%value%' is no video, '%type%' detected",
        self::NOT_DETECTED => "The mimetype of file '%value%' could not been detected",
        self::NOT_READABLE => "File '%value%' can not be read",
    );

    /**
     * Sets validator options
     *
     * @param  string|array|Zend_Config $mimetype
     * @return void
     */
    public function __construct($mimetype = array())
    {
        if ($mimetype instanceof Zend_Config) {
            $mimetype = $mimetype->toArray();
        }

        $temp    = array();
        $default = array(
            'video/3gpp',
            'video/3gpp-tt',
            'video/3gpp2',
            'video/bmpeg',
            'video/bt656',
            'video/celb',
            'video/dv',
            'video/example',
            'video/h261',
            'video/h263',
            'video/h263-1998',
            'video/h263-2000',
            'video/h264',
            'video/jpeg',
            'video/jpeg2000',
            'video/jpm',
            'video/mj2',
            'video/mp1s',
            'video/mp2p',
            'video/mp2t',
            'video/mp4',
            'video/mp4v-es',
            'video/mpeg',
            'video/mpeg4-generic',
            'video/mpv',
            'video/nv',
            'video/ogg',
            'video/parityfec',
            'video/pointer',
            'video/quicktime',
            'video/raw',
            'video/rtp-enc-aescm128',
            'video/rtx',
            'video/smpte292m',
            'video/ulpfec',
            'video/vc1',
            'video/vnd.cctv',
            'video/vnd.dlna.mpeg-tts',
            'video/vnd.fvt',
            'video/vnd.hns.video',
            'video/vnd.iptvforum.1dparityfec-1010',
            'video/vnd.iptvforum.1dparityfec-2005',
            'video/vnd.iptvforum.2dparityfec-1010',
            'video/vnd.iptvforum.2dparityfec-2005',
            'video/vnd.iptvforum.ttsavc',
            'video/vnd.iptvforum.ttsmpeg2',
            'video/vnd.motorola.video',
            'video/vnd.motorola.videop',
            'video/vnd.mpegurl',
            'video/vnd.ms-playready.media.pyv',
            'video/vnd.nokia.interleaved-multimedia',
            'video/vnd.nokia.videovoip',
            'video/vnd.objectvideo',
            'video/vnd.sealed.mpeg1',
            'video/vnd.sealed.mpeg4',
            'video/vnd.sealed.swf',
            'video/vnd.sealedmedia.softseal.mov',
            'video/vnd.vivo',
            'video/x-f4v',
            'video/x-fli',
            'video/x-flv',
            'video/x-m4v',
            'video/x-ms-asf',
            'video/x-ms-wm',
            'video/x-ms-wmv',
            'video/x-ms-wmx',
            'video/x-ms-wvx',
            'video/x-msvideo',
            'video/x-sgi-movie',
            'x-conference/x-cooltalk',
            'application/octet-stream; charset=binary',
        );

        if (is_array($mimetype)) {
            $temp = $mimetype;
            if (array_key_exists('magicfile', $temp)) {
                unset($temp['magicfile']);
            }

            if (array_key_exists('headerCheck', $temp)) {
                unset($temp['headerCheck']);
            }

            if (empty($temp)) {
                $mimetype += $default;
            }
        }

        if (empty($mimetype)) {
            $mimetype = $default;
        }

        parent::__construct($mimetype);
    }

    /**
     * Throws an error of the given type
     * Duplicates parent method due to OOP Problem with late static binding in PHP 5.2
     *
     * @param  string $file
     * @param  string $errorType
     * @return false
     */
    protected function _throw($file, $errorType)
    {
        $this->_value = $file['name'];
        switch($errorType) {
            case Zend_Validate_File_MimeType::FALSE_TYPE :
                $errorType = self::FALSE_TYPE;
                break;
            case Zend_Validate_File_MimeType::NOT_DETECTED :
                $errorType = self::NOT_DETECTED;
                break;
            case Zend_Validate_File_MimeType::NOT_READABLE :
                $errorType = self::NOT_READABLE;
                break;
        }

        $this->_error($errorType);
        return false;
    }

}