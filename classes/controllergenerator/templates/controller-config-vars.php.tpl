{% for configVar, varValue in behaviorConfigVars %}
    public ${{ configVar }} = '{{ varValue }}';
{% endfor %}