<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'NelmioApiDoc'.\DIRECTORY_SEPARATOR.'CacheConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'NelmioApiDoc'.\DIRECTORY_SEPARATOR.'HtmlConfigConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'NelmioApiDoc'.\DIRECTORY_SEPARATOR.'AreasConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'NelmioApiDoc'.\DIRECTORY_SEPARATOR.'ModelsConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class NelmioApiDocConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $typeInfo;
    private $useValidationGroups;
    private $operationIdGeneration;
    private $cache;
    private $documentation;
    private $mediaTypes;
    private $htmlConfig;
    private $areas;
    private $models;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * Use the symfony/type-info component for determining types.
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function typeInfo($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['typeInfo'] = true;
        $this->typeInfo = $value;

        return $this;
    }

    /**
     * If true, `groups` passed to #[Model] attributes will be used to limit validation constraints
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function useValidationGroups($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['useValidationGroups'] = true;
        $this->useValidationGroups = $value;

        return $this;
    }

    /**
     * How to generate operation ids
     * @default \Nelmio\ApiDocBundle\Describer\OperationIdGeneration::ALWAYS_PREPEND
     * @param ParamConfigurator|\Nelmio\ApiDocBundle\Describer\OperationIdGeneration::ALWAYS_PREPEND|\Nelmio\ApiDocBundle\Describer\OperationIdGeneration::CONDITIONALLY_PREPEND|\Nelmio\ApiDocBundle\Describer\OperationIdGeneration::NO_PREPEND|'always_prepend'|'conditionally_prepend'|'no_prepend' $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function operationIdGeneration($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['operationIdGeneration'] = true;
        $this->operationIdGeneration = $value;

        return $this;
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function cache(array $value = []): \Symfony\Config\NelmioApiDoc\CacheConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->cache) {
            $this->_usedProperties['cache'] = true;
            $this->cache = new \Symfony\Config\NelmioApiDoc\CacheConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "cache()" has already been initialized. You cannot pass values the second time you call cache().');
        }

        return $this->cache;
    }

    /**
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function documentation(string $key, mixed $value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['documentation'] = true;
        $this->documentation[$key] = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function mediaTypes(ParamConfigurator|array $value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['mediaTypes'] = true;
        $this->mediaTypes = $value;

        return $this;
    }

    /**
     * UI configuration options
     * @default {"assets_mode":"cdn","swagger_ui_config":[],"redocly_config":[],"stoplight_config":[]}
     * @deprecated since Symfony 7.4
     */
    public function htmlConfig(array $value = []): \Symfony\Config\NelmioApiDoc\HtmlConfigConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->htmlConfig) {
            $this->_usedProperties['htmlConfig'] = true;
            $this->htmlConfig = new \Symfony\Config\NelmioApiDoc\HtmlConfigConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "htmlConfig()" has already been initialized. You cannot pass values the second time you call htmlConfig().');
        }

        return $this->htmlConfig;
    }

    /**
     * Filter the routes that are documented
     * @default {"default":{"path_patterns":[],"host_patterns":[],"with_attribute":false,"documentation":[],"name_patterns":[],"disable_default_routes":false,"cache":[],"security":[]}}
     * @deprecated since Symfony 7.4
     */
    public function areas(string $name, array $value = []): \Symfony\Config\NelmioApiDoc\AreasConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (!isset($this->areas[$name])) {
            $this->_usedProperties['areas'] = true;
            $this->areas[$name] = new \Symfony\Config\NelmioApiDoc\AreasConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "areas()" has already been initialized. You cannot pass values the second time you call areas().');
        }

        return $this->areas[$name];
    }

    /**
     * @default {"use_jms":false,"names":[]}
     * @deprecated since Symfony 7.4
     */
    public function models(array $value = []): \Symfony\Config\NelmioApiDoc\ModelsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->models) {
            $this->_usedProperties['models'] = true;
            $this->models = new \Symfony\Config\NelmioApiDoc\ModelsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "models()" has already been initialized. You cannot pass values the second time you call models().');
        }

        return $this->models;
    }

    public function getExtensionAlias(): string
    {
        return 'nelmio_api_doc';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('type_info', $config)) {
            $this->_usedProperties['typeInfo'] = true;
            $this->typeInfo = $config['type_info'];
            unset($config['type_info']);
        }

        if (array_key_exists('use_validation_groups', $config)) {
            $this->_usedProperties['useValidationGroups'] = true;
            $this->useValidationGroups = $config['use_validation_groups'];
            unset($config['use_validation_groups']);
        }

        if (array_key_exists('operation_id_generation', $config)) {
            $this->_usedProperties['operationIdGeneration'] = true;
            $this->operationIdGeneration = $config['operation_id_generation'];
            unset($config['operation_id_generation']);
        }

        if (array_key_exists('cache', $config)) {
            $this->_usedProperties['cache'] = true;
            $this->cache = new \Symfony\Config\NelmioApiDoc\CacheConfig($config['cache']);
            unset($config['cache']);
        }

        if (array_key_exists('documentation', $config)) {
            $this->_usedProperties['documentation'] = true;
            $this->documentation = $config['documentation'];
            unset($config['documentation']);
        }

        if (array_key_exists('media_types', $config)) {
            $this->_usedProperties['mediaTypes'] = true;
            $this->mediaTypes = $config['media_types'];
            unset($config['media_types']);
        }

        if (array_key_exists('html_config', $config)) {
            $this->_usedProperties['htmlConfig'] = true;
            $this->htmlConfig = new \Symfony\Config\NelmioApiDoc\HtmlConfigConfig($config['html_config']);
            unset($config['html_config']);
        }

        if (array_key_exists('areas', $config)) {
            $this->_usedProperties['areas'] = true;
            $this->areas = array_map(fn ($v) => \is_array($v) ? new \Symfony\Config\NelmioApiDoc\AreasConfig($v) : $v, $config['areas']);
            unset($config['areas']);
        }

        if (array_key_exists('models', $config)) {
            $this->_usedProperties['models'] = true;
            $this->models = new \Symfony\Config\NelmioApiDoc\ModelsConfig($config['models']);
            unset($config['models']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['typeInfo'])) {
            $output['type_info'] = $this->typeInfo;
        }
        if (isset($this->_usedProperties['useValidationGroups'])) {
            $output['use_validation_groups'] = $this->useValidationGroups;
        }
        if (isset($this->_usedProperties['operationIdGeneration'])) {
            $output['operation_id_generation'] = $this->operationIdGeneration;
        }
        if (isset($this->_usedProperties['cache'])) {
            $output['cache'] = $this->cache->toArray();
        }
        if (isset($this->_usedProperties['documentation'])) {
            $output['documentation'] = $this->documentation;
        }
        if (isset($this->_usedProperties['mediaTypes'])) {
            $output['media_types'] = $this->mediaTypes;
        }
        if (isset($this->_usedProperties['htmlConfig'])) {
            $output['html_config'] = $this->htmlConfig->toArray();
        }
        if (isset($this->_usedProperties['areas'])) {
            $output['areas'] = array_map(fn ($v) => $v instanceof \Symfony\Config\NelmioApiDoc\AreasConfig ? $v->toArray() : $v, $this->areas);
        }
        if (isset($this->_usedProperties['models'])) {
            $output['models'] = $this->models->toArray();
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
