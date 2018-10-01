<?php

class ZFEngine_Module_UserBase_Form_Mailer_New extends Zend_Form
{
    /**
     *  code after __construct
     */
    public function init()
    {
        $this->setName(strtolower(__CLASS__));

        $mode = new Zend_Form_Element_Radio('mode');
        $mode->setMultiOptions(array(
            'users'  => _('Всем пользователям'),
            'groups' => _('Группе пользователей')))
                ->setLabel(_('Кому:'))
                ->setRequired(false)
                ->setValue('users');
        $this->addElement($mode);

        $groups = new Zend_Form_Element_Multiselect('user_role');
        $groups->removeDecorator('label');
        $groups->setRequired(false);

        $this->addElement($groups);

$script = <<<JS
$(document).ready(function () {
    $('#user_role').hide();

    $('#mode-groups').click(function(){
        if (this.checked) $('#user_role').show();
    })

    $('#mode-users').click(function(){
        if (this.checked) $('#user_role').hide();
    })
})
JS;
        $this->getView()->headScript()->appendScript($script);

        $message = new Zend_Form_Element_Text('subject');
        $message->setLabel(_('Тема'))
                ->addFilter(new Zend_Filter_StripTags())
                ->addFilter(new Zend_Filter_StringTrim())
                ->addFilter(new Zend_Filter_HtmlEntities())
                ->setRequired(true)
                ->setAttrib('class', 'input-text')
                ->setAttrib('maxlength', '255');
        $this->addElement($message);

        $message = new Zend_Form_Element_Textarea('message');
        $message->setLabel(_('Сообщение'))
                ->addFilter(new Zend_Filter_StripTags())
                ->addFilter(new Zend_Filter_StringTrim())
                ->addFilter(new Zend_Filter_HtmlEntities())
                ->setRequired(true)
                ->setAttrib('rows', '15');
        $this->addElement($message);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel(_('Отправить'))
               ->setIgnore(true);
        $this->addElement($submit);
    }
    
}