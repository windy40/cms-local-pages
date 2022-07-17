{% extends "section-modele-par.php" %} 

{% set section_layout='section-layout-default.php' %}
{% set content_layout='content-layout-default.php' %}


{# section blocks #}
{%- block section_id -%}
exemple_section_modele
{%- endblock -%}

{% block section_title %}
Exemple de section 
{% endblock %}

{% block section_subtitle %}
créée dans exemple-section-mode.php
{% endblock %}

{# section blocks #}
{# WARNING list of content blocks needs to be defined in variable content_blocks_list to be passed to embedded content-layout template #}
{% set content_blocks_list= ["box1", "box2"] %}

{% block box1 %}
<h3>box 1</h3>Bonjour 
{% endblock %}
		
{% block box2 %}
<h3>box 2</h3><p>Ce gabarit définit un layout de section à deux niveaux : <ul><li>le layout global de la section(typiquement le titre, soustitre, contenu) </li><li> le layout du contenu de la section (typiquement organisation en blocs ey disposition des blocs du contenu)</li></ul>Pour cela il s'appuie ('extends' en Twig) sur le gabarit du layout global de la section  et incorpore ('embed' en Twig) le layout du contenu.Le choix du layout de section du layout de contenu sont paramétrables.</p>
{% endblock %}

		