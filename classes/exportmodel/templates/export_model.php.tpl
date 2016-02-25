<?php namespace {{namespace}};

use Backend\Models\ExportModel;
use ApplicationException;

class Export{{className}} extends ExportModel
{
    public $table = '{{table}}';

    public function exportData($columns, $sessionKey = null)
    {
        $result = self::make()->get()->toArray();

        return $result;
    }
}