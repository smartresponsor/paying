<?php

namespace Symfony\Config\NelmioApiDoc\Models;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class NamesConfig 
{
    private $alias;
    private $type;
    private $groups;
    private $options;
    private $serializationContext;
    private $areas;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function alias($value): static
    {
        $this->_usedProperties['alias'] = true;
        $this->alias = $value;

        return $this;
    }

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
     *
     * @return $this
     */
    public function groups(mixed $value = NULL): static
    {
        $this->_usedProperties['groups'] = true;
        $this->groups = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     *
     * @return $this
     */
    public function options(mixed $value = NULL): static
    {
        $this->_usedProperties['options'] = true;
        $this->options = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function serializationContext(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['serializationContext'] = true;
        $this->serializationContext = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function areas(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['areas'] = true;
        $this->areas = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('alias', $config)) {
            $this->_usedProperties['alias'] = true;
            $this->alias = $config['alias'];
            unset($config['alias']);
        }

        if (array_key_exists('type', $config)) {
            $this->_usedProperties['type'] = true;
            $this->type = $config['type'];
            unset($config['type']);
        }

        if (array_key_exists('groups', $config)) {
            $this->_usedProperties['groups'] = true;
            $this->groups = $config['groups'];
            unset($config['groups']);
        }

        if (array_key_exists('options', $config)) {
            $this->_usedProperties['options'] = true;
            $this->options = $config['options'];
            unset($config['options']);
        }

        if (array_key_exists('serializationContext', $config)) {
            $this->_usedProperties['serializationContext'] = true;
            $this->serializationContext = $config['serializationContext'];
            unset($config['serializationContext']);
        }

        if (array_key_exists('areas', $config)) {
            $this->_usedProperties['areas'] = true;
            $this->areas = $config['areas'];
            unset($config['areas']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['alias'])) {
            $output['alias'] = $this->alias;
        }
        if (isset($this->_usedProperties['type'])) {
            $output['type'] = $this->type;
        }
        if (isset($this->_usedProperties['groups'])) {
            $output['groups'] = $this->groups;
        }
        if (isset($this->_usedProperties['options'])) {
            $output['options'] = $this->options;
        }
        if (isset($this->_usedProperties['serializationContext'])) {
            $output['serializationContext'] = $this->serializationContext;
        }
        if (isset($this->_usedProperties['areas'])) {
            $output['areas'] = $this->areas;
        }

        return $output;
    }

}
