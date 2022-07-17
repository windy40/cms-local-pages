{% extends ('header-par.php')  %}

{% block header_link %}
	{{ parent() }}
	{# rajouter ici les éléments <link> #}
	{% if  global.style_widget_file is not empty %}
		<link rel="stylesheet" href="{{ global.style_widget_file }}">
	{% endif %}
{% endblock %}

{% block header_img %}
	{{ parent() }}
	{# rajouter ici les éléments img #}
{% endblock %}

{% block header_script %}
	{{ parent() }}
	{# rajouter ici les éléments script #}

{% endblock %}
	


	