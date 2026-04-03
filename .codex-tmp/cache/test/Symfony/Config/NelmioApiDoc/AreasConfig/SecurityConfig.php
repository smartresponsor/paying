<?php

namespace Symfony\Config\NelmioApiDoc\AreasConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;

/**
 * This class is automatically generated to help in creating a config.
 */
class SecurityConfig 
{
    private $type;
    private $scheme;
    private $in;
    private $name;
    private $description;
    private $openIdConnectUrl;
    private $_usedProperties = [];
    private $_extraKeys;

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function type($value): static
    {
        $this->_usedProperties['type'] = true;
        $this->type = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function scheme($value): static
    {
        $this->_usedProperties['scheme'] = true;
        $this->scheme = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function in($value): static
    {
        $this->_usedProperties['in'] = true;
        $this->in = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function name($value): static
    {
        $this->_usedProperties['name'] = true;
        $this->name = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function description($value): static
    {
        $this->_usedProperties['description'] = true;
        $this->description = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function openIdConnectUrl($value): static
    {
        $this->_usedProperties['openIdConnectUrl'] = true;
        $this->openIdConnectUrl = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('type', $config)) {
            $this->_usedProperties['type'] = true;
            $this->type = $config['type'];
            unset($config['type']);
        }

        if (array_key_exists('scheme', $config)) {
            $this->_usedProperties['scheme'] = true;
            $this->scheme = $config['scheme'];
            unset($config['scheme']);
        }

        if (array_key_exists('in', $config)) {
            $this->_usedProperties['in'] = true;
            $this->in = $config['in'];
            unset($config['in']);
        }

        if (array_key_exists('name', $config)) {
            $this->_usedProperties['name'] = true;
            $this->name = $config['name'];
            unset($config['name']);
        }

        if (array_key_exists('description', $config)) {
            $this->_usedProperties['description'] = true;
            $this->description = $config['description'];
            unset($config['description']);
        }

        if (array_key_exists('openIdConnectUrl', $config)) {
            $this->_usedProperties['openIdConnectUrl'] = true;
            $this->openIdConnectUrl = $config['openIdConnectUrl'];
            unset($config['openIdConnectUrl']);
        }

        $this->_extraKeys = $config;

    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['type'])) {
            $output['type'] = $this->type;
        }
        if (isset($this->_usedProperties['scheme'])) {
            $output['scheme'] = $this->scheme;
        }
        if (isset($this->_usedProperties['in'])) {
            $output['in'] = $this->in;
        }
        if (isset($this->_usedProperties['name'])) {
            $output['name'] = $this->name;
        }
        if (isset($this->_usedProperties['description'])) {
            $output['description'] = $this->description;
        }
        if (isset($this->_usedProperties['openIdConnectUrl'])) {
            $output['openIdConnectUrl'] = $this->openIdConnectUrl;
        }

        return $output + $this->_extraKeys;
    }

    /**
     * @param ParamConfigurator|mixed $value
     *
     * @return $this
     */
    public function set(string $key, mixed $value): static
    {
        $this->_extraKeys[$key] = $value;

        return $this;
    }

}
