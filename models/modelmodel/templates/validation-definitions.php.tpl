{% if validation.rules or validation.attributeNames or validation.customMessages %}{% if validation.rules %}

    /**
     * @var array rules for validation.
     */
    public $rules = [
{% for rule, value in validation.rules %}
        '{{ rule }}' => '{{ value }}'{% if not loop.last %},
{% endif %}
{% endfor %}

    ];
{% endif %}{% if validation.attributeNames %}

    /**
     * @var array attributeNames for validation.
     */
    public $attributeNames = [
{% for attr, value in validation.attributeNames %}
        '{{ attr }}' => '{{ value }}'{% if not loop.last %},
{% endif %}
{% endfor %}

    ];
{% endif %}{% if validation.customMessages %}

    /**
     * @var array customMessages for validation.
     */
    public $customMessages = [
{% for msg, value in validation.customMessages %}
        '{{ msg }}' => '{{ value }}'{% if not loop.last %},
{% endif %}
{% endfor %}

    ];
{% endif %}{% else %}

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];
{% endif %}