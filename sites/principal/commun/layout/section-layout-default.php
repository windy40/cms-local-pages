<section class="section" id="{%- block section_id -%}{%- endblock -%}">
    <div class="container">
	<h1 class="title has-text-centered">
		{% block section_title %}
		{% endblock %}
	</h1>
	<h2 class="subtitle has-text-centered">
		{% block section_subtitle %}			
		{% endblock %}					  
     </h2>
	{% block section_content %}
	{% endblock %}

	</div>
</section>