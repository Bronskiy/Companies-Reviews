<?php

class ZFEngine_View_Helper_InfoMessage extends Zend_View_Helper_Abstract
{

    /**
     * Render info message
     * 
     * @param string $message
     * @param string $type
     * @return string
     */
    public function infoMessage($message, $type = 'info')
    {
        $content = '<ul class="info-message">';
            $content .= '<li class="'.$type.'">';
                $content .= $message;
            $content .= '</li>';
        $content .= '</ul>';
        return $content;
    }

}