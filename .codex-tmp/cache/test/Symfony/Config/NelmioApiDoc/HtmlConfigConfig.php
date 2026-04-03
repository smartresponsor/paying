<?php

namespace Symfony\Config\NelmioApiDoc;

require_once __DIR__.\DIRECTORY_SEPARATOR.'HtmlConfig'.\DIRECTORY_SEPARATOR.'SwaggerUiConfigConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'HtmlConfig'.\DIRECTORY_SEPARATOR.'RedoclyConfigConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'HtmlConfig'.\DIRECTORY_SEPARATOR.'StoplightConfigConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class HtmlConfigConfig 
{
    private $assetsMode;
    private $swaggerUiConfig;
    private $redoclyConfig;
    private $stoplightConfig;
    private $_usedProperties = [];

    /**
     * @default 'cdn'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function assetsMode($value): static
    {
        $this->_usedProperties['assetsMode'] = true;
        $this->assetsMode = $value;

        return $this;
    }

    /**
     * https://swagger.io/docs/open-source-tools/swagger-ui/usage/configuration/
     */
    public function swaggerUiConfig(array $value = []): \Symfony\Config\NelmioApiDoc\HtmlConfig\SwaggerUiConfigConfig
    {
        if (null === $this->swaggerUiConfig) {
            $this->_usedProperties['swaggerUiConfig'] = true;
            $this->swaggerUiConfig = new \Symfony\Config\NelmioApiDoc\HtmlConfig\SwaggerUiConfigConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "swaggerUiConfig()" has already been initialized. You cannot pass values the second time you call swaggerUiConfig().');
        }

        return $this->swaggerUiConfig;
    }

    /**
     * https://redocly.com/docs/redoc/config/
     */
    public function redoclyConfig(array $value = []): \Symfony\Config\NelmioApiDoc\HtmlConfig\RedoclyConfigConfig
    {
        if (null === $this->redoclyConfig) {
            $this->_usedProperties['redoclyConfig'] = true;
            $this->redoclyConfig = new \Symfony\Config\NelmioApiDoc\HtmlConfig\RedoclyConfigConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "redoclyConfig()" has already been initialized. You cannot pass values the second time you call redoclyConfig().');
        }

        return $this->redoclyConfig;
    }

    /**
     * https://docs.stoplight.io/docs/elements/b074dc47b2826-elements-configuration-options
     */
    public function stoplightConfig(array $value = []): \Symfony\Config\NelmioApiDoc\HtmlConfig\StoplightConfigConfig
    {
        if (null === $this->stoplightConfig) {
            $this->_usedProperties['stoplightConfig'] = true;
            $this->stoplightConfig = new \Symfony\Config\NelmioApiDoc\HtmlConfig\StoplightConfigConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "stoplightConfig()" has already been initialized. You cannot pass values the second time you call stoplightConfig().');
        }

        return $this->stoplightConfig;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('assets_mode', $config)) {
            $this->_usedProperties['assetsMode'] = true;
            $this->assetsMode = $config['assets_mode'];
            unset($config['assets_mode']);
        }

        if (array_key_exists('swagger_ui_config', $config)) {
            $this->_usedProperties['swaggerUiConfig'] = true;
            $this->swaggerUiConfig = new \Symfony\Config\NelmioApiDoc\HtmlConfig\SwaggerUiConfigConfig($config['swagger_ui_config']);
            unset($config['swagger_ui_config']);
        }

        if (array_key_exists('redocly_config', $config)) {
            $this->_usedProperties['redoclyConfig'] = true;
            $this->redoclyConfig = new \Symfony\Config\NelmioApiDoc\HtmlConfig\RedoclyConfigConfig($config['redocly_config']);
            unset($config['redocly_config']);
        }

        if (array_key_exists('stoplight_config', $config)) {
            $this->_usedProperties['stoplightConfig'] = true;
            $this->stoplightConfig = new \Symfony\Config\NelmioApiDoc\HtmlConfig\StoplightConfigConfig($config['stoplight_config']);
            unset($config['stoplight_config']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['assetsMode'])) {
            $output['assets_mode'] = $this->assetsMode;
        }
        if (isset($this->_usedProperties['swaggerUiConfig'])) {
            $output['swagger_ui_config'] = $this->swaggerUiConfig->toArray();
        }
        if (isset($this->_usedProperties['redoclyConfig'])) {
            $output['redocly_config'] = $this->redoclyConfig->toArray();
        }
        if (isset($this->_usedProperties['stoplightConfig'])) {
            $output['stoplight_config'] = $this->stoplightConfig->toArray();
        }

        return $output;
    }

}
