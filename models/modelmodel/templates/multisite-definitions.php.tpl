{% if multisite %}{% if multisite.fields %}

    /**
     * @var array propagatable fields.
     */
    protected $propagatable = [
{% for field in multisite.fields %}
        '{{ field }}'{% if not loop.last %},
{% endif %}
{% endfor %}

    ];
{% else %}

    /**
     * @var array propagatable fields.
     */
    protected $propagatable = [];
{% endif %}{% if multisite.sync %}

    /**
     * @var array propagatableSync for multisite.
     */
    protected $propagatableSync = true;
{% endif %}{% endif %}