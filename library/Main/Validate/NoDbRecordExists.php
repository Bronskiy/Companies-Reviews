<?php

/**
 * Main_Validate_NoDbRecordExists
 * 
 * Проверка отсутствия/присутствия записи в таблице. 
 * 
 * Если передан 4 параметр($_isReverse) в конструктор как true, то валидатор будет 
 * проверять на присутствие в базе (Если запись отсутствует - вернется ошибка)
 * 
 * Если $_isReverse = false, то проверяться будет отсутствие в базе
 * 
 */
class Main_Validate_NoDbRecordExists extends Zend_Validate_Abstract 
{

    /**
     * Метка ошибки при проверке на отсутствие в базе(при регистрации)
     * @var const 
     */    
    const RECORD_EXISTS = 'dbRecordExists';
    
    /**
     * Метка ошибки, если isReverse=true при проверке на присутствие в базе
     * (при восстановлении пароля)
     * @var const
     */
    const RECORD_NOEXISTS = 'dbRecordNoExists';
    
    /**
     * Текст ошибки
     * @var array 
     */
    protected $_messageTemplates = array(
        self::RECORD_EXISTS => 'Запись со значением %value% уже существует в базе',
        self::RECORD_NOEXISTS => 'Запись со значением %value% отсутствует в базе'
    );

    /**
     * Реверсировать или нет результат ответа на противоположный
     * @var bool
     */
    protected $_isReverse = false;
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
    public function __construct($table, $field, 
    				Zend_Db_Adapter_Abstract $adapter = null, 
                                $isReverse = false)
    {
        $this->_table = $table;
        $this->_field = $field;
        $this->_isReverse = $isReverse;
        
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
        // сравнение без учета регистра
        $select = $adapter->select()
            ->from($this->_table)
            ->where($adapter->quoteIdentifier($this->_field) . ' = ?',$value)
            ->limit(1);
            
        $stmt   = $adapter->query($select);
        $result = $stmt->fetch(Zend_Db::FETCH_ASSOC);
        // проверка на присутствие в базе. Если отсутствует - возвращаем false
        if($this->_isReverse === true && $result === false){
        	$this->_error(self::RECORD_NOEXISTS);
        	return false;
        }
        // проверка на отсутствие в базе. Если присутствует - возвращаем fase
        if ($this->_isReverse === false && $result !== false) {
            $this->_error(self::RECORD_EXISTS);
            return false;
        }
        
        return true;
    }

}

