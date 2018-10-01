<?php

/**
 * Main_Validate_PassConfirm
 * 
 * Валидатор проверяет совпадение двух полей паролей.
 */

class Main_Validate_PassConfirm extends Zend_Validate_Abstract {
    
    /**
     * Метка ошибки
     * @var const 
     */
    const NOT_MATCH = 'notMatch';
    
    /**
     * Текст ошибки
     * @var array 
     */
    protected $_messageTemplates = array(
        self::NOT_MATCH => 'Пароли не совпадают'
    );
    
    /**
     * Имя поля, с которым сравниваем значение
     * @var string 
     */
    protected $_matchFieldName;
    
    /**
     * Конструктор валидатора принимает обязательный строковый параметр имени 
     * элемента формы значение которого будет сравниваться со значением элемента
     * к которому принадлежит этот валидатор. 
     *
     * @param string $name Имя поля, с которым сравниваем
     */
    public function __construct($name) {
        $this->_matchFieldName = (string)$name;
    }
    
    
    /**
     * Сравнение полей паролей
     * 
     * Сравнение значения $value с $context[ $this->_matchFieldName ]
     * 
     * @param string        $value значение которое поддается валидации элемента
     * 
     * @param array|string  $context массив (или строка) со значениями 
     * 			    всех элементов в форме которые подвергаются
     * 			    валидации. См. мануал
     */
    public function isValid($value, $context = null) {
        
        $value = (string) $value;

        if (is_array($context)) {
            if (isset($context[$this->_matchFieldName]) 
            	&& ($value === $context[$this->_matchFieldName])) 
          	{
                return true;
            }
        }
        else if (is_string($context) && ($value === $context))  {
            return true;
        }
    
        $this->_error(self::NOT_MATCH);
        
        return false;
    }
}