
{{
'{% extends (
	section_layout is defined ? 
	section_layout : 
		(config.section_modele.section_layout is defined ? 
		config.section_modele.section_layout :
			(global.default_section_layout is defined ? 
			global.default_section_layout :
			\'section-layout-default.php\'))) %}
'}}


{{'
{% set content_layout_gbt= 
	(content_layout is defined ? 
		content_layout : 
		(config.section_modele.content_layout is defined ?
			config.section_modele.content_layout :
			(global.default_content_layout is defined ?
			global.default_content_layout :
			\'content-layout-default.php\')))
%}
'
}}

{% if config.section_modele.section_blocks is defined %}
{% for item in config.section_modele.section_blocks|keys %}
	{{ '{%- block '~item~' -%}
		  {{ config.section_modele.section_blocks.'~item~'|raw }}
		{%- endblock -%}
' }} 
{% endfor %}
{% endif %}

{% if content_blocks_list is not defined %}
{% set content_blocks_list= 
	(config.section_modele.content_blocks is defined ?
		config.section_modele.content_blocks|keys :
		{}) %}
{% endif %}
		
{% for item in content_blocks_list %}
	{{ '{% set override_'~item~' %}
		{% if config.section_modele.content_blocks.'~item~' is defined %}
		  {{- config.section_modele.content_blocks.'~item~'|raw -}}
		{% else %}
			{%- block '~item~' -%}
			{%- endblock -%}
		{% endif %}
		{% endset %}'
	}} 
{% endfor %}


{{ 
'{% block section_content %}
	{% embed content_layout_gbt  %}
'
}}

{% for item in content_blocks_list %}
		{{ '{%- block ' ~ item ~ ' -%}
			{{ override_'~item~' }}
			{%- endblock -%}
			' }}
{% endfor %}

{{
'
{% endembed %}
{% endblock %}
' 
}}
