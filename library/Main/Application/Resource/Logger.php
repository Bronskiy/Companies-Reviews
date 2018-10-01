<?php
class Main_Application_Resource_Logger extends Zend_Application_Resource_ResourceAbstract
{
    const FORMAT_STANDART = '%timestamp% %priorityName% (%priority%): %message%';

    /**
     * Объект Zend_Log
     * @var Zend_Log
     */
    private $_logger = null;
    
    const DEFAULT_REGISTRY_KEY = 'logger';
	
    /**
     * Имя файла лога по-умолчанию
     * @var string
     */
    protected $_fileName = 'logger.log';
	
    protected $_format = '';

    public function init()
    {
        try {

            $logInfo = $this->getOptions();

            if(! isset($logInfo['log_dir'])) {
                throw new Exception(' Option "log_dir" does not defined in configuration file');
            }
            
            $this->_logger = new Zend_Log();
            
            $format =  array_key_exists("format", $logInfo) ? $logInfo['format'] : self::FORMAT_STANDART;

            $logDir = $this->_normalizePathInfo($logInfo['log_dir']);
            $fileName = empty($logInfo['file_name']) ? $this->_fileName : $logInfo['file_name'];
            $stream = $this->_getLogStream($logDir, $fileName);

            $this->_logger->addWriter($this->_createWriter($stream, $format));
            Zend_Registry::set(self::DEFAULT_REGISTRY_KEY, $this);
            return $this;
        
        }catch(Exception $e) {
            throw $e;
        }
    }
	
	
    /**
     * Просто обертка над методом log объекта Zend_Log.
     *
     * Можно передавать объект Exception в первом параметре.
     *
     * @param string|Exception $info
     * @param int $priority
     */
    public function log($info, $priority = 6)
    {
        if(is_object($info) && $info instanceof Exception) {
            $this->_logger->err($info);
        }elseif(is_string($info)) {
            $this->_logger->log($info, (int)$priority);
        }else {
            throw new Exception(' Logging error parameter $info should be a string or Exception object ');
        }
    }
	
	
    protected function _createWriter($stream, $format)
    {
        $writer = new Zend_Log_Writer_Stream($stream);
        $formatter = new Zend_Log_Formatter_Simple($format . PHP_EOL);
        $writer->setFormatter($formatter);
        return $writer;
    }

	
    /**
     * Формирование полного имени файла лога
     *
     * @param  string $logPath
     * @param  string $fileName
     * @return string полный путь к файлу лога
     * @throws Exception
     */
    private function _getLogStream($logPath, $fileName)
    {
        $this->_checkPath($logPath);
        return $logPath . $fileName;
    }


    /**
     * Добавление разделителя к папке с файлами логов.
     *
     * @param string $path
     */
    private function _normalizePathInfo($path)
    {
        if(mb_substr($path, -1) != '/') {
            $path .= '/';
        }
        return $path;
    }
	
    /**
     * Проверка существования каталога, а также возможность записи в него
     *
     * @param string $dirName
     * @throws Exception
     */
    private function _checkPath($dirName)
    {
        if(!is_dir($dirName)) {
            throw new Exception('Directory '. $dirName . ' does not exists');
        }
        if(!is_writable($dirName)) {
            throw new Exception('You are not allowed to write data into directory  ' . $dirName);
        }
    }
	
}