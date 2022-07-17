{% for widget in phpcode_widget_list %}
	{% if attribute(sections, widget).config.controller %}
		{% set vars = {'config': attribute(sections, widget).config } %}
		{% include (attribute(sections, widget).config.controller) with vars %}

		$data['{{ widget}}']['data'] = $wdata;
		
	{% endif %}
{% endfor %}