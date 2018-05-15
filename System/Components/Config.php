<?php

namespace System\Components;

class Config extends AppComponent
{
    /**
     * Config array
     *
     * @param array $configs App configurations/settings
     */
    private $_configs;

    /**
     * Set the configurationf or the application
     * @param array $configs Read-in array of configurations
     */
    public function setConfigs($configs) {
        $this->_configs = $configs;
    }

    /**
     * Get configuration by name
     * @param  string $name The config value label
     * @return string       The config value
     */
    public function get($name = null)
    {
        if(empty($name)) {
            return $this->_configs;
        }
        return $this->_configs[$name];
    }
}
