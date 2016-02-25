<?php namespace {{ pluginNamespace }}\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

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
}