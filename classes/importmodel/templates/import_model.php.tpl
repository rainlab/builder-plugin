<?php namespace {{namespace}};

use Backend\Models\ImportModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Import{{className}} extends ImportModel
{
    public $rules = [];

    public function importData($results, $sessionKey = null)
    {
        foreach ($results as $row => $data) {

            try {
                try {
                    $entry = {{className}}::findOrFail($data['id']);
                    $entry->fill($data);
                    $entry->save();
                    $this->logUpdated();
                }
                catch (ModelNotFoundException $ex) {
                    $entry = new {{className}};
                    $entry->fill($data);
                    $entry->save();
                    $this->logCreated();
                }
            }

            catch (\Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }
        }
    }
}