{% extends "section-modele-par.php" %} 

{% set section_layout='section-layout-default.php' %}
{% set content_layout='content-layout-default.php' %}


{# section blocks #}
{%- block section_id -%}
exemple_widget
{%- endblock -%}

{% block section_title %}
Exemple widget 
{% endblock %}

{% block section_subtitle %}
pour afficher des données
{% endblock %}

{# section blocks #}
{# WARNING list of content blocks needs to be defined in variable content_blocks_list to be passed to embedded content-layout template #}
{% set content_blocks_list= ["box1", "box2"] %}

{% block box1 %}
<h3>box 1</h3>Affiche de données 
{% endblock %}
		
{% block box2 %}
	<h3>box 2</h3>		
	<div class="content">
		{% include('@exemple-widget/partials/exemple-widget-block.php') %}
	</div>
{% endblock %}
