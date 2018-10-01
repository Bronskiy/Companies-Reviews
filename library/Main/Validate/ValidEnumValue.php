<?php

/**
 * Main_Validate_PassConfirm
 * 
 * Валидатор проверяет корректность передаваемого значения в поле enum
 */

class Main_Validate_ValidEnumValue extends Zend_Validate_Abstract 
{
    /**
     * Метка ошибки при проверке
     * @var const 
     */    
    const INVALID_ENUM_VAL = 'dbInvalidEnumValue';
    
    /**
     * Текст ошибки
     * @var array 
     */
    protected $_messageTemplates = array(
        self::INVALID_ENUM_VAL => 'Значение %value% не корректно'
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
     * @param Zend_Db_Adapter_Abstract $adapter Адаптер базы данных
     * @param bool $isReverse реверсировать ответ или нет
     */
    public function __construct($table, $field, Zend_Db_Adapter_Abstract $adapter = null)
    {
        $this->_table = $table;
        $this->_field = $field;
        
        if ($adapter == null) {
            // Если адаптер не задан, пробуем подключить адаптер заданный по умолчанию для Zend_Db_Table
            $adapter = Zend_Db_Table::getDefaultAdapter();
            // Если адаптер по умолчанию не задан выбрасываем исключение
            if ($adapter == null) {
                throw new Exception('Не определен адаптер для работы с БД');
            }
        }
        
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
        
        $adapter->setFetchMode(Zend_Db::FETCH_OBJ);
        $query = "SHOW COLUMNS FROM %s LIKE '%s'";
        $query = sprintf($query, $this->_table, $this->_field);
        $result = $adapter->fetchRow($query);
        // в массив $matches[1] (карман) попадают значения без кавычек
        preg_match_all("#'(.*?)'#i", $result->Type, $matches);
        $values = (array_values($matches[1]));

        if(in_array($value, $values)) {
            return true;
        }
        $this->_error(self::INVALID_ENUM_VAL);
        return false;
    }
}