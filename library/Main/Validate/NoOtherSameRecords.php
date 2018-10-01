<?php

/**
 * Dc_Validators_NoOtherSameRecords
 * 
 * Проверка отсутствия записи в таблице не соответствующей определенному полю
 * Например, если пользователь обновляет свой e-mail то нужно проверить есть ли
 * такоей же у кого-то из других юзеров, но проверку у самого пользователя 
 * нужно опустить
 * 
 */
class Main_Validate_NoOtherSameRecords extends Zend_Validate_Abstract 
{

    /**
    * Метка ошибки
    * @var const 
    */    
    const RECORD_EXISTS = 'dbRecordExists';
    
    /**
    * Текст ошибки
    * @var array 
    */
    protected $_messageTemplates = array(
        self::RECORD_EXISTS => 'Record with value %value% already exists in database'
    );

    /**
    * Имя таблица в которой будет происходить поиск записи
    * @var string
    */    
    protected $_table = null;    
    
    /**
    * Имя поля по которому будет происходить поиск значения 
    * @var string
    */    
    protected $_field = null;    
    
    /**
    * имя поля которое нужно исключить из проверки
    * @var string
    */
    protected $_excludeField = null;
    
	/**
    * значение поля которое нужно исключить из проверки
    * @var mixed
    */
    protected $_excludeValue = null;
    
    /**
    * Используемый адаптер базы данных
    *
    * @var unknown_type
    */    
    protected $_adapter = null;    
       
    
    /**
     * Конструктор
     * 
     * @param string $table Имя таблицы
     * @param string $field Имя поля
     * @param array  $exlude массив исключаемого поля и его значения из поиска
     * 						 н-р. array('user_id'=>23)
     * @param Zend_Db_Adapter_Abstract $adapter Адаптер базы данных
     */
    public function __construct($table, $field, array $exlude, Zend_Db_Adapter_Abstract $adapter = null)
    {
        $this->_table = $table;
        $this->_field = $field;
        
        if ($adapter == null) {
            // Если адаптер не задан, пробуем подключить адаптер 
            //заданный по умолчанию для Zend_Db_Table
            $adapter = Zend_Db_Table::getDefaultAdapter();

            // Если адаптер по умолчанию не задан выбрасываем исключение
            if ($adapter == null) {
                throw new Exception('Can not find database adapter');
            }
        }
        // если ключ массива $exlude не является строкой
        if(!is_string(key($exlude))){
        	throw new Exception('Array key $exlude must be a string type');
        }
        
        $this->_excludeField = key($exlude);
        $this->_excludeValue = current($exlude);
        
        $this->_adapter = $adapter;
    }
    
    /**
     * Проверка
     * 
     * @param string $value значение которое поддается валидации
     */
    public function isValid($value) 
    {
        $this->_setValue($value);
        
        $adapter = $this->_adapter;
        
        $select = $adapter->select()
                          ->from($this->_table)
                          ->where($adapter->quoteIdentifier($this->_field) . ' = ?',$value)
                          ->where($adapter->quoteIdentifier($this->_excludeField) . ' != ?', $this->_excludeValue)
                          ->limit(1);
        $stmt   = $adapter->query($select);
        $result = $stmt->fetch(Zend_Db::FETCH_ASSOC);
        
        if ($result !== false) {
            $this->_error(self::RECORD_EXISTS);
            return false;
        }
        
        return true;

    }

}

