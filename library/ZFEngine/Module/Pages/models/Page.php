<?php

/**
 * Pages_Model_Page
 */
class ZFEngine_Module_Pages_Model_Page extends ZFEngine_Module_Pages_Model_Base_Page
{
    /**
     * Set title
     *
     * @param string $title
     * @param string $lang
     * @return void
     */
    public function setTitle($title, $lang = null)
    {
        if (is_null($lang)) {
            $locales = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('locales');
            $lang = key($locales);
        }
        $this->Translation[$lang]->title = $title;
    }

    /**
     * Get description
     *
     * @param string $lang
     * @return string
     */
    public function getTitle($lang = null)
    {
        if (!is_string($lang)) {
            $locales = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('locales');
            $lang = key($locales);

            if ($this->Translation[$lang]->title) {
                //если есть перевод
                return $this->Translation[$lang]->title;
            } else {
                //если нет - перевод на языке по умолчанию
                $config = Zend_Registry::get('config');
                return $this->Translation[$config->get('locales')->key()]->title;
            }
        } else {
            //иначе - возвращаем описание на запрашиваемом языке
            return $this->Translation[$lang]->title;
        }
     }

    /**
     * Set keywords
     *
     * @param string $keywords
     * @param string $lang
     * @return void
     */
    public function setKeywords($keywords, $lang = null)
    {
            if (is_null($lang)) {
                $locales = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('locales');
                $lang = key($locales);
            }
            $this->Translation[$lang]->keywords = $keywords;
    }

    /**
     * Get description
     *
     * @param string $lang
     * @return string
     */
    public function getKeywords($lang = null)
    {
        if (!is_string($lang)) {
            $locales = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('locales');
            $lang = key($locales);

            if ($this->Translation[$lang]->keywords) {
                //если есть перевод
                return $this->Translation[$lang]->keywords;
            } else {
                //если нет - перевод на языке по умолчанию
                $config = Zend_Registry::get('config');
                return $this->Translation[$config->get('locales')->key()]->keywords;
            }
        } else {
            //иначе - возвращаем описание на запрашиваемом языке
            return $this->Translation[$lang]->keywords;
        }
     }

    /**
     * Set description
     *
     * @param string $description
     * @param string $lang
     * @return void
     */
    public function setDescription($description, $lang = null)
    {
        if (is_null($lang)) {
            $locales = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('locales');
            $lang = key($locales);
        }
        $this->Translation[$lang]->description = $description;
    }

    /**
     * Get description
     *
     * @param string $lang
     * @return string
     */
    public function getDescription($lang = null)
    {
        if (!is_string($lang)) {
            $locales = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('locales');
            $lang = key($locales);

            if ($this->Translation[$lang]->description) {
                //если есть перевод
                return $this->Translation[$lang]->description;
            } else {
                //если нет - перевод на языке по умолчанию
                $config = Zend_Registry::get('config');
                return $this->Translation[$config->get('locales')->key()]->description;
            }
        } else {
            //иначе - возвращаем описание на запрашиваемом языке
            return $this->Translation[$lang]->description;
        }
     }

    /**
     * Set content
     *
     * @param string $content
     * @param string $lang
     * @return void
     */
    public function setContent($content, $lang = null)
    {

        if (is_null($lang)) {
            $lang = Zend_Registry::get('lang');
        }
        $this->Translation[$lang]->content = $content;

    }

    /**
     * Get description
     *
     * @param string $lang
     * @return string
     */
    public function getContent($lang = null)
    {
        if (!is_string($lang)) {
            $locales = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('locales');
            $lang = key($locales);

            if ($this->Translation[$lang]->content) {
                //если есть перевод
                return $this->Translation[$lang]->content;
            } else {
                //если нет - перевод на языке по умолчанию
                $config = Zend_Registry::get('config');
                return $this->Translation[$config->get('locales')->key()]->content;
            }
        } else {
            //иначе - возвращаем описание на запрашиваемом языке
            return $this->Translation[$lang]->content;
        }
     }

/**
 *
 *
 *
 *  if (strlen($title)) {
 *  } else {
 *       if ($this->Translation[$lang]->exists()) {
//                $this->Translation[$lang]->delete();
//                $this->Translation[$lang]->free();
//            }
 *  }
 */

}