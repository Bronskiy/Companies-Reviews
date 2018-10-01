<?php

require_once 'common.php';

/**
 * Video converter class
 */
class ConvertVideosTask extends Task {
    /**
     * Video conversion commands and params
     */
    const CMD_CONVERT_MP4 = "ffmpeg -i %s -f mp4 -ar 44100 -profile:v baseline -x264opts level=3.0:vbv-maxrate=750:vbv-bufsize=3750:ref=1 -y %s %s";
    const CMD_CONVERT_WEBM = "ffmpeg -i %s -f webm -b:v 750k -quality good -codec:v libvpx -codec:a libvorbis -maxrate 750k -bufsize 1000k -y %s %s";
    const CMD_GET_FIRST_FRAME = "ffmpeg -i %s -r 1 -t 00:00:01 -f image2 -y %s";
    const CMD_VIDEO_PROBE = "ffmpeg -i %s";
    const ROTATE_PARAM = "-vf \"transpose=%d\" -metadata:s:v rotate=0";
    
    /**
     * Lock file name
     */
    const LOCK_FILE_NAME = 'convert-videos.lock';

    /**
     * Execute command
     */
    public function exec() {
        try {
            parent::exec();

            $videos = Companies_Model_CompanyVideoTable::getInstance()->getUnprocessed();

            foreach ($videos as $video) {
                // skip unconfirmed reviews
                if ($video->review_id && $video->Review->status != Companies_Model_Review::STATUS_PROCESSING) {
                    continue;
                }

                $video->status = Companies_Model_CompanyVideo::STATUS_PROCESSING;
                $video->save();

                try {
                    foreach ($video->Streams as $stream) {
                        if (!in_array($stream->status, array(
                            Companies_Model_VideoStream::STATUS_NOT_PROCESSED,
                            Companies_Model_VideoStream::STATUS_PROCESSING
                        ))) {
                            continue;
                        }

                        try {
                            $stream->status = Companies_Model_VideoStream::STATUS_PROCESSING;
                            $stream->save();

                            if ($stream->is_source) {
                                $this->_processSource($stream);
                            } else {
                                $this->_processConverted($stream);
                            }
                        } catch (Exception $e) {
                            $stream->status = Companies_Model_VideoStream::STATUS_ERROR;
                            $stream->save();

                            throw $e;
                        }
                    }

                    // check if all streams are processed
                    $allProcessed = true;
                    $streams = Companies_Model_VideoStreamTable::getInstance()->findByVideoId($video->id);

                    foreach ($streams as $stream) {
                        if ($stream->status != Companies_Model_VideoStream::STATUS_PROCESSED) {
                            $allProcessed = false;
                            break;
                        }
                    }

                    if ($allProcessed) {
                        $video->status = Companies_Model_CompanyVideo::STATUS_PROCESSED;
                        $video->save();
                    }
                } catch (Exception $e) {
                    $video->status = Companies_Model_CompanyVideo::STATUS_ERROR;
                    $video->save();

                    echo $e->getMessage() . "\n";
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
        }        
    }

    /**
     * Get video path
     * @param Companies_Model_CompanyVideo $video
     */
    private function _getVideoPath(Companies_Model_CompanyVideo $video) {
        $view = $this->getView();
        $view->addHelperPath(realpath(APPLICATION_PATH . '/modules/companies/views/helpers/'));

        if ($video->review_id) {
            // review video
            $dirGenerator = new Main_Service_Dir_Generator_Review($video->Review);
            $dirsInfo = $dirGenerator->getFoldersPathsFromRule();
            $path = realpath($view->getPath($dirsInfo, 'video'));
        } else {
            // company video
            $dirGenerator = new Main_Service_Dir_Generator_Company($video->Company);
            $dirsInfo = $dirGenerator->getFoldersPathsFromRule();
            $path = realpath($view->getPath($dirsInfo, 'videos'));
        }

        return $path;
    }

    /**
     * Process source stream
     * @param Companies_Model_VideoStream $stream
     */
    private function _processSource(Companies_Model_VideoStream &$stream) {
        $video = $stream->Video;
        $path = $this->_getVideoPath($video);
        $pathInfo = pathinfo($video->name);

        $extension = Main_Service_Models::getExtensionByMimeType($stream->type);
        $streamPath = $path . '/' . $pathInfo['filename'] . '.' . $extension;

        // get rotation angle
        $streamData = $this->_getOutput(sprintf(self::CMD_VIDEO_PROBE, $streamPath), $code);
        $matches = array();
        $rotate = 0;

        if (preg_match('/rotate\s+: (\d+)/', $streamData, $matches)) {
            $angle = (int) $matches[1];

            if ($angle == 90) {
                $rotate = 1;
            } else if ($angle == 270) {
                $rotate = 2;
            }
        }

        $targetTypes = array(
            "video/mp4",
            "video/webm",
        );

        // convert stream
        foreach ($targetTypes as $type) {
            if ($stream->type != $type) {
                $this->_convert($path, $stream, $type, $rotate);
            }
        }

        // if source type is in target type list, then just prepare it for the further processing
        if (in_array($stream->type, $targetTypes)) {
            $stream->is_source = false;
            $stream->status = Companies_Model_VideoStream::STATUS_NOT_PROCESSED;
            $stream->save();
        } else {
            @unlink($streamPath);
            $stream->delete();
        }
    }

    /**
     * Process converted stream
     * @param Companies_Model_VideoStream $stream
     */
    private function _processConverted(Companies_Model_VideoStream &$stream) {
        $video = $stream->Video;
        $path = $this->_getVideoPath($video);
        $pathInfo = pathinfo($video->name);

        $streamPath = $path . '/' . $pathInfo['filename'] . '.' . Main_Service_Models::getExtensionByMimeType($stream->type);
        $thumbnailPath = $path . '/' . $pathInfo['filename'] . '.jpg';

        if (!file_exists($streamPath)) {
            throw new Exception("Stream does not exist: $streamPath");
        }

        // thumbnail
        if (!file_exists($thumbnailPath)) {
            if ($this->_runExternal(sprintf(self::CMD_GET_FIRST_FRAME, $streamPath, $thumbnailPath)) != 0) {
                throw new Exception("Failed to generate video thumbnail");
            }

            $service = new Companies_Model_ReviewService();
            $service->resizeVideoThumbnail($thumbnailPath);

            if (!file_exists($thumbnailPath)) {
                throw new Exception("Failed to generate video thumbnail");
            }
        }

        // get video width and height
        if (!$video->width || !$video->height) {
            $code = null;
            $streamData = $this->_getOutput(sprintf(self::CMD_VIDEO_PROBE, $streamPath), $code);
            $matches = array();

            if (preg_match('/ (\d{2,})x(\d{2,}),?/', $streamData, $matches)) {
                $video->width = (int) $matches[1];
                $video->height = (int) $matches[2];
                $video->save();
            } else {
                throw new Exception("Failed to get stream dimensions");
            }
        }

        $stream->status = Companies_Model_VideoStream::STATUS_PROCESSED;
        $stream->save();
    }

    /**
     * Convert stream
     * @param $path
     * @param Companies_Model_VideoStream $stream
     * @param $targetType
     * @param $rotate
     */
    private function _convert($path, Companies_Model_VideoStream &$stream, $targetType, $rotate) {
        $video = $stream->Video;

        if ($stream->type == $targetType) {
            return;
        }

        $pathInfo = pathinfo($video->name);
        $sourceVideo = $path . '/' . $pathInfo['filename'] . '.' . Main_Service_Models::getExtensionByMimeType($stream->type);
        $targetVideo = $path . '/' . $pathInfo['filename'] . '.' . Main_Service_Models::getExtensionByMimeType($targetType);

        if (file_exists($targetVideo)) {
            @unlink($targetVideo);
        }

        // convert video
        $commands = array(
            "video/mp4" => self::CMD_CONVERT_MP4,
            "video/webm" => self::CMD_CONVERT_WEBM,
        );

        if (!array_key_exists($targetType, $commands)) {
            throw new Exception("Unsupported video format: $targetType");
        }

        $command = $commands[$targetType];
        $command = sprintf(
            $command,
            $sourceVideo,
            $rotate ? sprintf(self::ROTATE_PARAM, $rotate) : "",
            $targetVideo
        );

        if ($this->_runExternal($command) != 0) {
            throw new Exception("Failed to convert video to $targetType");
        }

        $newStream = Companies_Model_VideoStreamTable::getInstance()->findOneByVideoIdAndType($video->id, $targetType);

        if (!$newStream) {
            $newStream = new Companies_Model_VideoStream();
        }

        $newStream->fromArray(array(
            "video_id" => $video->id,
            "type" => $targetType,
            "status" => Companies_Model_VideoStream::STATUS_NOT_PROCESSED,
            "is_source" => false,
        ));
        $newStream->save();
    }
    
    /**
     * Return full locking file name path
     */
    protected function _getLockedFileName() {
        return realpath(APPLICATION_PATH . '/../tmp') . '/' . self::LOCK_FILE_NAME;
    }
}

$task = new ConvertVideosTask();
$task->exec();