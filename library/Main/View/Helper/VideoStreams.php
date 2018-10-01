<?php

/**
 * Video stream generator helper
 */
class Zend_View_Helper_VideoStreams extends Zend_View_Helper_Abstract {
    /**
     * Video streams generator
     * @param $baseDir
     * @param $folder
     * @param Companies_Model_CompanyVideo $video
     */
    public function videoStreams($baseDir, $folder, $video) {
        $streams = array();

        foreach (Companies_Model_VideoStreamTable::getInstance()->getVideoStreams($video->id) as $stream) {
            $ext = Main_Service_Models::getExtensionByMimeType($stream->type);
            $streams[$ext] = $this->view->getPath($baseDir, $folder, $video->name . "." . $ext);
        }

        $streamsText = array();

        foreach ($streams as $type => $stream) {
            $streamsText[] = "{\"$type\":\"$stream\"}";
        }

        return implode(",", $streamsText);
    }
}