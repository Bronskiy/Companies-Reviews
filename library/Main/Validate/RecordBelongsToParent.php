<?php

/**
 * Check if record exists with specified parent
 */
class Main_Validate_RecordBelongsToParent extends Zend_Validate_Abstract {
    /**
     * Error constants
     */
    const ERROR_NO_RECORD_FOUND = "noRecordFound";

    /**
     * @var array Message templates
     */
    protected $_messageTemplates = array(
        self::ERROR_NO_RECORD_FOUND => "No record was found",
    );

    /**
     * @var string
     */
    protected $_table = "";

    /**
     * @var string
     */
    protected $_field = "";

    /**
     * @var string
     */
    protected $_parentField = "";

    /**
     * @var int
     */
    protected $_parent = null;

    /**
     * @var boolean
     */
    protected $_mandatory = false;

    /**
     * Database adapter to use. If null isValid() will use Zend_Db::getInstance instead
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_adapter = null;

    /**
     * Constructor
     * @param $table
     * @param $field
     * @param $parentField
     * @param $parent
     * @param $mandatory
     * @param Zend_Db_Adapter_Abstract $adapter
     */
    public function __construct($table, $field, $parentField, $parent, $mandatory = false, Zend_Db_Adapter_Abstract $adapter = null) {
        $this->_table = $table;
        $this->_field = $field;
        $this->_parentField = $parentField;
        $this->_parent = $parent;
        $this->_mandatory = $mandatory;
        
        if ($adapter == null) {
            $adapter = Zend_Db_Table::getDefaultAdapter();

            if ($adapter == null) {
                throw new Exception("No database adapter present");
            }
        }
        
        $this->_adapter = $adapter;
    }

    /**
     * Validate
     * @param mixed $value
     * @param mixed $context
     * @return bool
     */
    public function isValid($value, $context = null) {
        $this->_setValue($value);

        if (!$this->_mandatory && !$value) {
            return true;
        }

        $adapter = $this->_adapter;

        $select = $adapter->select()
            ->from($this->_table)
            ->where($adapter->quoteIdentifier($this->_field) . " = :value")
            ->where($adapter->quoteIdentifier($this->_parentField) . " = :parent")
            ->limit(1);
            
        $stmt = $adapter->query($select, array(
            "value" => $value,
            "parent" => $this->_parent
        ));

        $result = $stmt->fetch(Zend_Db::FETCH_ASSOC);

        if ($result === false) {
        	$this->_error(self::ERROR_NO_RECORD_FOUND);
        	return false;
        }

        return true;
    }
}
