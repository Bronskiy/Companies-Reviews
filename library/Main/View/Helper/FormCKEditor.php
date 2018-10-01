<?php
class Zend_View_Helper_FormCKEditor extends Zend_View_Helper_FormTextarea
{
    protected $_tinyMce;

    public function FormCKEditor ($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        $disabled = '';
        if ($disable) {
            $disabled = ' disabled="disabled"';
        }

        if (empty($attribs['rows'])) {
            $attribs['rows'] = (int) $this->rows;
        }
        if (empty($attribs['cols'])) {
            $attribs['cols'] = (int) $this->cols;
        }

        if (isset($attribs['editorOptions'])) {
            if ($attribs['editorOptions'] instanceof Zend_Config) {
                $attribs['editorOptions'] = $attribs['editorOptions']->toArray();
            }
            if(empty($attribs['editorOptions']['editor_selector'])) {
                $attribs['editorOptions']['editor_selector'] = $this->view->escape($name) ;
            }
            //$attribs['editorOptions']['editor_selector'] = $this->view->escape($name);
            $this->view->CKEditor()->setOptions($attribs['editorOptions']);
            unset($attribs['editorOptions']);
        }
        $this->view->CKEditor()->render();
        $class = !empty($attribs['class']) ?  $this->view->escape($attribs['class']) : $this->view->escape($name);
        $xhtml = '<textarea class="' . $class . '" name="' . $this->view->escape($name) . '"'
                . ' id="' . $this->view->escape($id) . '"'
                . $disabled
                . $this->_htmlAttribs($attribs) . '>'
                . $this->view->escape($value) . '</textarea>';

        return $xhtml;
    }
}
