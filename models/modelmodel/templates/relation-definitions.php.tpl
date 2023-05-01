
    /**
     * @var array {{ relationType }}
     */
    public ${{ relationType }} = [
{% for name, relation in relations %}
        '{{ name }}' => [
            \{{ relation.class }}::class,
{% for prop, value in relation.props %}
            '{{ prop }}' => {{ value|raw }}{% if not loop.last %},
{% endif %}
{% endfor %}

        ]{% if not loop.last %},
{% endif %}
{% endfor %}

    ];