<?php

namespace System\Components;

/**
 * Model for use by the system
 */
class Session extends AppComponent
{

    private $_flashCleanup = true;

    /**
     * Initialize session
     */
    public function __construct()
    {
        session_start();
        if(!isset($_SESSION['main'])) {
            $_SESSION['main'] = array();
        }
        if(!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = array();
        }
    }

    /**
     * Destruct the session
     */
    public function __destruct()
    {
        if($this->_flashCleanup) {
            $_SESSION['flash'] = array();
        }
    }

    public function clear()
    {
        $_SESSION['main'] = array();
    }

    /**
     * Set a session variable
     *
     * @param string $key The key to look the variable up
     * @param mixed  $val The variable to return from the session
     */
    public function set($key, $val)
    {
        $_SESSION['main'][$key] = $val;
    }

    /**
     * Get/Set a short-living session variable
     *
     * @param string $key The key to look the variable up
     * @param mixed  $val The variable to return from the session
     */
    public function flash($key, $val = null)
    {
        if(is_null($val)) {
            if(!isset($_SESSION['flash'][$key])) {
                return null;
            }
            return $_SESSION['flash'][$key];
        }
        $_SESSION['flash'][$key] = $val;
        $this->_flashCleanup = false;
    }

    /**
     * Get a session variable
     * @param  string $key The key for the variable to retrieve
     * @return mixed       The variable stored in the session
     */
    public function get($key)
    {
        if(isset($_SESSION['main'][$key])) {
            return $_SESSION['main'][$key];
        }
        return null;
    }
}
