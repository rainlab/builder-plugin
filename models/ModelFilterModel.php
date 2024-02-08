<?php namespace RainLab\Builder\Models;

use ApplicationException;
use ValidationException;
use SystemException;
use Lang;

/**
 * ModelFilterModel represents and manages model filters.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ModelFilterModel extends ModelYamlModel
{
    /**
     * @var array scopes
     */
    public $scopes;

    /**
     * @var array fillable
     */
    protected static $fillable = [
        'fileName',
        'scopes'
    ];

    /**
     * @var array validationRules
     */
    protected $validationRules = [
        'fileName' => ['required', 'regex:/^[a-z0-9\.\-_]+$/i']
    ];

    /**
     * loadForm
     */
    public function loadForm($path)
    {
        $this->fileName = $path;

        return parent::load($this->getFilePath());
    }

    /**
     * fill
     */
    public function fill(array $attributes)
    {
        if (!is_array($attributes['scopes'])) {
            $attributes['scopes'] = json_decode($attributes['scopes'], true);

            if ($attributes['scopes'] === null) {
                throw new SystemException('Cannot decode scopes JSON string.');
            }
        }

        return parent::fill($attributes);
    }

    /**
     * validateFileIsModelType
     */
    public static function validateFileIsModelType($fileContentsArray)
    {
        $modelRootNodes = [
            'scopes'
        ];

        foreach ($modelRootNodes as $node) {
            if (array_key_exists($node, $fileContentsArray)) {
                return true;
            }
        }

        return false;
    }

    /**
     * validate
     */
    public function validate()
    {
        parent::validate();

        $this->validateDuplicateScopes();

        if (!$this->scopes) {
            throw new ValidationException(['scopes' => 'Please create at least one scope.']);
        }
    }

    /**
     * initDefaults
     */
    public function initDefaults()
    {
        $this->fileName = 'scopes.yaml';
    }

    /**
     * validateDuplicateScopes
     */
    protected function validateDuplicateScopes()
    {
        foreach ($this->scopes as $outerIndex => $outerScope) {
            foreach ($this->scopes as $innerIndex => $innerScope) {
                if ($innerIndex != $outerIndex && $innerScope['field'] == $outerScope['field']) {
                    throw new ValidationException([
                        'scopes' => Lang::get(
                            'rainlab.builder::lang.list.error_duplicate_scope',
                            ['scope' => $outerScope['field']]
                        )
                    ]);
                }
            }
        }
    }

    /**
     * modelToYamlArray converts the model's data to an array before it's saved to a YAML file.
     * @return array
     */
    protected function modelToYamlArray()
    {
        $fileScopes = [];

        foreach ($this->scopes as $scope) {
            if (!isset($scope['field'])) {
                throw new ApplicationException('Cannot save the list - the scope field name should not be empty.');
            }

            $scopeName = $scope['field'];
            unset($scope['field']);

            if (array_key_exists('id', $scope)) {
                unset($scope['id']);
            }

            $scope = $this->preprocessScopeDataBeforeSave($scope);

            $fileScopes[$scopeName]  = $scope;
        }

        return [
            'scopes'=>$fileScopes
        ];
    }

    /**
     * yamlArrayToModel loads the model's data from an array.
     * @param array $array An array to load the model fields from.
     */
    protected function yamlArrayToModel($array)
    {
        $fileScopes = $array['scopes'];
        $scopes = [];
        $index = 0;

        foreach ($fileScopes as $scopeName => $scope) {
            if (!is_array($scope)) {
                // Handle the case when a scope is defined as
                // scope: Title
                $scope = [
                    'label' => $scope
                ];
            }

            $scope['id'] = $index;
            $scope['field'] = $scopeName;

            $scopes[] = $scope;

            $index++;
        }

        $this->scopes = $scopes;
    }

    /**
     * preprocessScopeDataBeforeSave
     */
    protected function preprocessScopeDataBeforeSave($scope)
    {
        // Filter empty values
        $scope = array_filter($scope, function ($value) {
            return !is_array($value) && strlen($value) > 0;
        });

        // Cast booleans
        $booleanFields = [];

        foreach ($booleanFields as $booleanField) {
            if (!array_key_exists($booleanField, $scope)) {
                continue;
            }

            $value = $scope[$booleanField];
            if ($value == '1' || $value == 'true') {
                $value = true;
            }
            else {
                $value = false;
            }


            $scope[$booleanField] = $value;
        }

        return $scope;
    }
}
