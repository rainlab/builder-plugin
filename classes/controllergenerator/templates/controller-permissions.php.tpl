{% if permissions %}
    public $requiredPermissions = [
{% for permission in permissions %}
        '{{ permission }}'{% if not loop.last %},{% endif %} 
{% endfor %}
    ];
{% endif %}