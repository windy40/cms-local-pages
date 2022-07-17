{% for section in sections %}
	{% set vars = {'config':section['config'],'data': section['data'],'global': global, 'menu': menu} %}
	{% include 
		((section.config.view is defined) and (section.config.view is not empty))?
			section.config.view :
			(section.config.section_modele is defined ? 
				'section-modele-par.php' :
				(section.config.name)~'.php')
	with vars only %}
{% endfor %}