<?php

namespace ZfcTwig\Twig;

use Twig_Environment;
use ZfcTwig\Twig\Func\ViewHelper;
use Zend\View\HelperBroker;

class Environment extends Twig_Environment
{
    /**
     * @var \Zend\View\HelperBroker
     */
    protected $helperBroker;
    protected $php_fallback;

    function __construct(\Twig_LoaderInterface $loader = null, $options = array())
    {
        parent::__construct($loader, $options);
        $options = array_merge(array(
            'allow_php_fallback' => false
        ), $options);
        $this->php_fallback = $options['allow_php_fallback'];
    }

    /**
     * @return \Zend\View\HelperBroker
     */
    public function helperBroker()
    {
        return $this->helperBroker;
    }

    /**
     * @param $name string
     */
    public function plugin($name)
    {
        return $this->helperBroker()->get($name);
    }

    /**
     * @param \Zend\View\HelperBroker $broker
     * @return Environment
     */
    public function setHelperBroker(HelperBroker $broker)
    {
        $this->helperBroker = $broker;
        return $this;
    }

    public function getFunction($name)
    {
        if (($function = parent::getFunction($name))) {
            return $function;
        }

        try{
            if ($this->plugin($name)){
                $function = new ViewHelper($name);

                $this->addFunction($name, $function);
                return $function;
            }
        }catch(\Exception $exception){

        }

        if ($this->php_fallback){
            $constructs = array('isset', 'empty');
            $_name = $name;
            if (function_exists($_name) || in_array($_name, $constructs)) {
                $function = new \Twig_Function_Function($_name);
                $this->addFunction($name, $function);
                return $function;
            }
        }

        return false;
    }
}