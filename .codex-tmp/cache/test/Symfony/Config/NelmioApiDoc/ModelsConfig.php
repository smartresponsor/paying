<?php

namespace Symfony\Config\NelmioApiDoc;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Models'.\DIRECTORY_SEPARATOR.'NamesConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ModelsConfig 
{
    private $useJms;
    private $names;
    private $_usedProperties = [];

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function useJms($value): static
    {
        $this->_usedProperties['useJms'] = true;
        $this->useJms = $value;

        return $this;
    }

    public function names(array $value = []): \Symfony\Config\NelmioApiDoc\Models\NamesConfig
    {
        $this->_usedProperties['names'] = true;

        return $this->names[] = new \Symfony\Config\NelmioApiDoc\Models\NamesConfig($value);
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('use_jms', $config)) {
            $this->_usedProperties['useJms'] = true;
            $this->useJms = $config['use_jms'];
            unset($config['use_jms']);
        }

        if (array_key_exists('names', $config)) {
            $this->_usedProperties['names'] = true;
            $this->names = array_map(fn ($v) => new \Symfony\Config\NelmioApiDoc\Models\NamesConfig($v), $config['names']);
            unset($config['names']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['useJms'])) {
            $output['use_jms'] = $this->useJms;
        }
        if (isset($this->_usedProperties['names'])) {
            $output['names'] = array_map(fn ($v) => $v->toArray(), $this->names);
        }

        return $output;
    }

}
