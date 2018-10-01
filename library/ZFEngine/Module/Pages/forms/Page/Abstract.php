<?php

abstract class ZFEngine_Module_Pages_Form_Page_Abstract extends Zend_Form
{
    /**
     * Form initialization
     *
     * @return void
     */
    public function init()
    {
        $locales = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('locales');
        $defaultLanguage = key($locales);

        $alias = new Zend_Form_Element_Text('alias');
        $alias->setLabel(_('Алиас'))
            ->setDescription(_('Название страницы в адресной строке'))
            ->addValidator('regex', false, array("/^[a-z0-9\-_]{2,32}$/i"))
            ->addValidator(new ZFEngine_Validate_Doctrine_NoRecordExist('Pages_Model_Page', 'alias'))
            ->addFilter('StringTrim')
            ->setRequired()
            ->setAttrib('class', 'input-text');
        $this->addElement($alias);

        $subFormTitle = new Zend_Form_SubForm();
        $subFormTitle->removeDecorator('Fieldset');
        $subFormContent = new Zend_Form_SubForm();
        $subFormContent->removeDecorator('Fieldset');

        foreach ($locales as $language => $value) {
            $title = new Zend_Form_Element_Text($language);
            $title->setLabel(strtoupper($language) . ' ' . _('Заголовок'));
            $title->setAttrib('class', 'input-text');

            $content = new ZFEngine_Form_Element_TinyMce($language);
            $content->setLabel(strtoupper($language) . ' ' . _('Контент'))
            ->setAttrib('editorOptions', new Zend_Config_Ini(APPLICATION_PATH . '/modules/pages/configs/tinymce.ini', 'moderator'));

            if ($language == $defaultLanguage) {
                $title->setRequired(true);
                $content->setRequired(true);
            }
            $subFormTitle->addElement($title);
            $subFormContent->addElement($content);
        }

        $this->addSubForm($subFormTitle, 'title');
        $this->addSubForm($subFormContent, 'content');

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setIgnore(true);
//        $submit->setDecorators(array(new Default_Form_Decorator_Button()));
        $this->addElement($submit);

        return $this;

    }





}