<?php namespace {{ pluginNamespace }}\Controllers;

{% if hasListBehavior %}
use Lang;
use Flash;
{% endif %}
use BackendMenu;
use Backend\Classes\Controller;

class {{ controller }} extends Controller
{
    public $implement = [{% for behavior in behaviors %}'{{ behavior }}'{% if not loop.last %},{% endif %}{% endfor %}];
    {{ templateParts|raw }}
    public function __construct()
    {
        parent::__construct();
{% if menuItem %}
{% if not sideMenuItem %}
        BackendMenu::setContext('{{ pluginCode }}', '{{ menuItem }}');
{% else %}
        BackendMenu::setContext('{{ pluginCode }}', '{{ menuItem }}', '{{ sideMenuItem }}');
{% endif %}
{% endif %}
    }
    {% if hasListBehavior %}
    public function index_onDelete()
    {
        $model = $this->getConfig('modelClass');

        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            foreach ($checkedIds as $id) {
                if (!$record = $model::find($id)) {
                    continue;
                }

                $record->delete();
            }

            Flash::success(Lang::get('backend::lang.list.delete_selected_success'));
        }

        return $this->listRefresh();
    }
    {% endif %}

}