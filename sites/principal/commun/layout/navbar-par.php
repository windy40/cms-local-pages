{% block menu %}
<nav class="navbar is-primary has-shadow is-mesCouleurs is-fixed-top"
role="navigation" aria-label="main navigation">

  <div class="navbar-brand">
     <a class="navbar-item" id="logo">
				<img src="{{ config.menu.logo}}" >
    </a>
    <a class="navbar-item" href="{{ config.menu.title_ref }}">
      <h1 class="title">{{ config.menu.title|raw }}</h1>
    </a>
	<a role="button" class="navbar-burger" data-target="navbar-menu" aria-label="menu" aria-expanded="false">
		<span aria-hidden="true"></span>
		<span aria-hidden="true"></span>
		<span aria-hidden="true"></span>
	</a>
  </div>

  <div id="navbar-menu" class="navbar-menu">
  {% if config.menu.start_items is defined %}
 <!-- start menu gauche -->
 <div class="navbar-start">
 {% for item in config.menu.start_items %}
	 {% if item.subitems is not defined %}
		<a class="navbar-item nav-section" href="{{ item.ref }}">
			{{ item.name }}
		</a>
	{% else %}
		<!-- dropdown-->		
        <div class="navbar-item has-dropdown is-hoverable">
            <a class="navbar-item navbar-link" href="{{ item.ref }}">
                {{ item.name}}
            </a>
            <div class="navbar-dropdown is-boxed">
			{% for subitem in item.subitems %}
                <a class="navbar-item" href="{{ subitem.ref}}">
                   {{ subitem.name }}
                </a>
			{% endfor %}
			</div>
		</div>
	<!-- Fin dropdown-->
	{% endif %}
{% endfor %}	
</div>
{% endif %}

{% if config.menu.end_items is defined %}
<!-- start menu droite -->
<div class="navbar-end">
 {% for item in config.menu.end_items %}
	 {% if item.subitems is not defined %}
		<a class="navbar-item" href="{{ item.ref}}">
			{{ item.name}}
		</a>
	{% else %}
		<!-- dropdown-->		
        <div class="navbar-item has-dropdown is-hoverable">
            <a class="navbar-item navbar-link" href="{{ item.ref}}">
                {{ item.name}}
            </a>
            <div class="navbar-dropdown is-boxed">
			{% for subitem in item.subitems %}
                <a class="navbar-item" href="{{ subitem.ref}}">
                   {{ subitem.name }}
                </a>
			{% endfor%}
			</div>
		</div>
	<!-- Fin dropdown-->
	{% endif %}
{% endfor %}
</div>
{% endif %}
	
  </div>
  
</nav>
{% endblock %}