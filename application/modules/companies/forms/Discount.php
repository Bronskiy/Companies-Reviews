<?php

/**
 * Discount code form
 */
class Companies_Form_Discount extends Main_Forms_ZForm {
    /**
     * Form init
     */
    public function init() {
        parent::init();  

        $this->addElementPrefixPath('Zend_Validate_Db', 'Zend/Validate/Db', 'validate');

        $this->addElement('select', 'plan_id', array(
            'validators' => array(
                array(new Main_Validate_DbRecordExistsExt(array('table' => 'plans', 'field' => 'id'), 0))
            ),
            'decorators' => array('ViewHelper'),
            'label' => 'Plan*',
            'multioptions' => $this->_getPlans()
        ));

        $this->addElement('text', 'code', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(1, 1000, 'UTF-8')),
            ),
            'decorators'  => array('ViewHelper'),
            'required'    => false,
            'label'       => 'Code',
            'maxlength'   => '1000',
            'description' => 'Leave blank to create automatically.',
        ));
        
        $this->addElement('text', 'first_month_discount', array(
            'validators' => array(
                array('Float', true),
                array('Between', true, array(0, 9999, true)),
            ),
            'decorators' => array('ViewHelper'),
            'required' => true,
            'label' => 'First Month Discount*',
            'value' => 0,
            'description' => 'Discount for the first month of subscription. If you set this value' .
                ' to the maximum, then this discount code will work like a trial code (e.g. setup and first month ' .
                ' will be free for the customer).',
        ));

        $this->addElement('text', 'monthly_discount', array(
            'validators' => array(
                array('Float', true),
                array('Between', true, array(0, 9999, true)),
            ),
            'decorators' => array('ViewHelper'),
            'required' => true,
            'label' => 'Monthly Discount*',
            'value' => 0,
            'description' => 'Monthly discount. If this value is big enough, then the system doesn\'t charge ' .
                'the applied customers. If you want to make a discount that will allow free sign-ups (even without ' .
                'setup payments), then don\'t forget to set appropriate value for the first month discount above.',
        ));
    }

    /**
     * Get plans
     * @return array
     */
    protected function _getPlans() {
        $table = Doctrine_Core::getTable('Companies_Model_Plan');
        $plans = $table->findAll();

        $options = array();

        foreach ($plans as $plan) {
            $options[$plan->id] = $plan->name;
        }

        return $options;
    }
}