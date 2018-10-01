<?php 
class Main_Translate_Array extends Main_Translate_Abstract
{
    public static function regTranslation(Zend_Controller_Request_Abstract $request) 
    {
        $config = self::_getConfig();

        // язык по умолчанию
        $langDefault = $config->languages->default;
        //ключ языка
        $langKey = $config->languages->langKey;
        // текущий язык
        $lang = $request->getParam($langKey);

        $langFile = APPLICATION_PATH . '/languages/' . $lang . '/' . $lang . '.php';
        // если файла с переводами нет, назначаем дефолтный файл
        if(!is_file($langFile)) {
            //TODO логировать ошибку нужно
            $lang = $langDefault;
            $langFile = APPLICATION_PATH . '/languages/' . $lang . '/' . $lang . '.php';
        }

        $translate = new Zend_Translate(array(
            'adapter' => 'array',
            'content' => $langFile,
            'locale'  => $lang));

        // файл с переводами для валидаторов
        $validatorFile =  APPLICATION_PATH . '/languages/' . $lang . '/' . 'Zend_Validate.php';
        // если файл для валидаторов существует, то добавляем переводы к основным
        // и для Валидаторов назначаем переводы
        if(is_file($validatorFile)) {
            $translateValidators = new Zend_Translate(array(
                    'adapter' => 'array',
                    'content' => $validatorFile,
                    'locale'  => $lang));

            Zend_Validate_Abstract::setDefaultTranslator($translateValidators);

            $translate->addTranslation(array(
                        'content' => $validatorFile,
                        'locale' => $lang));
        }

        Zend_Registry::set(self::REGISTRY_KEY, $translate);
    }
}