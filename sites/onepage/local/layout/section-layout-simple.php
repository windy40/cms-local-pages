<section class="section" id="{%- block section_id -%}{%- endblock -%}">
    <div class="container">
	<h1 class="title has-text-centered" id="services-title">{% block section_titre %}
	{% endblock %}</h1>
      <h2 class="subtitle has-text-centered" id="bio-subtitle">
		{% block section_soustitre %}
        <blockquote class="tooltip">
          {% block section_soustitre_tip %}{% endblock %}
          <span class="tooltiptext">{% block section_soustitre_tiptext %}{% endblock %}</span>		  
        </blockquote>			
			
		{% endblock %}					  
      </h2>
	{% block section_contenu %}
	{% endblock %}
	</div>
</section>