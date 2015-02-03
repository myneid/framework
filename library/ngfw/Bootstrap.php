<?php

/**
 * ngfw
 * ---
 * Copyright (c) 2014, Nick Gejadze
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy 
 * of this software and associated documentation files (the "Software"), 
 * to deal in the Software without restriction, including without limitation 
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, 
 * and/or sell copies of the Software, and to permit persons to whom the 
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included 
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A 
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR 
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, 
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace ngfw;

/**
 * Bootstrap
 * @package ngfw
 * @subpackage library
 * @version 0.1
 * @copyright (c) 2014, Nick Gejadze
 */
class Bootstrap {

    /**
     * $_controllerLoaded
     * Holds boolean value of controller loaded status
     * @access protected
     * @var boolean
     */
    protected $_controllerLoaded = false;

    /**
     * __construct()
     * Instantiates new Autoloader and all methods
     * @see initMethods()
     * @access public
     * @return void
     */
    public function __construct() {
        $this->initMethods();
    }

    /**
     * initMethods()
     * Calls Every Class method with starts with "_" OR "__"
     * @access private
     * @return void
     */
    private function initMethods() {
        // Get all class methods with _ in the beginning, sort alphabetically and then invoke all the methods.
        $methods = get_class_methods($this);
        // Methods must be sorted by name in a hackish attempt to make it deterministic.
        // get_class_methods makes no assurances about the order methods are returned. http://php.net/manual/en/function.get-class-methods.php
        // Before adding the sort, PHP was calling _initStorage after _NewControllerLoader in the following example:
        // array ( 0 => '_initConfig', 1 => '_initRoute', 2 => '_initDB', 3 => '_initTrafficDB', 4 => '_initDbobject', 5 => '_NewControllerLoader', 6 => '__construct', 7 => 'initMethods', 8 => '_initStorage', 9 => '_loadController', )
        sort($methods);
        foreach ($methods as $method):
            if (substr($method, 0, 1) == "_" and substr($method, 0, 2) !== "__"):
                call_user_func(array($this, $method));
            endif;
        endforeach;
    }

    /**
     * _initStorage()
     * Instantiates \ngfw\Registry
     * @see \ngfw\Registry
     * @access private
     * @return void
     */
    private function _initStorage() {
        \ngfw\Registry::init();
    }

    /**
     * _loadController()
     * Loads application controller
     * @see \ngfw\Route
     * @throws \ngfw\Exception
     * @access private
     * @return void
     */
    private function _loadController() {
        if (!$this->_controllerLoaded):
            $className = \ngfw\Route::getController() . "Controller";
            if (class_exists($className)):
                $app = new $className;
            else:
                \ngfw\Route::redirect(\ngfw\Uri::baseUrl() . "error/notfound", "404");
                exit();
            endif;
            $this->_controllerLoaded = true;
            $method = \ngfw\Route::getAction() . "Action";
            if (method_exists($app, $method)):
                call_user_func(array($app, $method));
            else:
                if(defined('DEVELOPMENT_ENVIRONMENT')):
                    if (DEVELOPMENT_ENVIRONMENT):
                        throw new \ngfw\Exception(sprintf('The required method "%s" does not exist for %s', $method, $className));
                        exit();
                    endif;
                endif;
            endif;
        endif;
    }

}
