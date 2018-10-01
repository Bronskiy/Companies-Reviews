<?php

/**
 * Main_Validate_DbRecordExistsExt
 * 
 * Аналог Zend_Validate_Db_RecordExists но с доп параметром к конструктору
 * 
 * $exclude - означает какие параметры не подвергать валидации и возвращать true
 * Это может потребоваться, когда в базу передаются значения не присутствующие 
 * в ней, но которые можно передать. 
 * 
 * Например parent_id для страниц может быть 0, или маршрут для страницы 'NULL'.
 * 
 * 
 */
class Main_Validate_DbRecordExistsExt extends Zend_Validate_Db_RecordExists 
{
    
    /**
     * Значение(я) которое(ые) нужно валидировать по умолчанию.
     * 
     * @var mixed
     */
    protected $_excludeExt = false;
    
    public function __construct($options, $exclude = false) {
        parent::__construct($options);
        $this->_excludeExt = $exclude;
    }
    
    public function isValid($value)
    {
        if(($this->_excludeExt !== false)) {
            if(is_array($this->_excludeExt)) {
                foreach($this->_excludeExt as $excVal) {
                    if(is_numeric($this->_excludeExt) && is_numeric($value)) {
                        if(floatval($this->_excludeExt) === floatval($value)) return true;
                    }
                    if($value === $excVal) return true;
                }
            }else {
                if(is_numeric($this->_excludeExt) && is_numeric($value)) {
                    if(floatval($this->_excludeExt) === floatval($value)) return true;
                }
                if($this->_excludeExt === $value) return true;
            }
        }
        
        return parent::isValid($value);
    }
    
}