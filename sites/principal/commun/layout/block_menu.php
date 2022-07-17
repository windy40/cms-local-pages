{% block menu %}
<nav class="navbar is-primary has-shadow is-mesCouleurs is-fixed-top">

  <div class="navbar-brand">
     <a class="navbar-item">
				<img src="{{ menu.logo}}" >
    </a>
    <a class="navbar-item" href="{{ menu.title_ref }}">
      <h1 class="title">{{ menu.title|raw }}</h1>
    </a>
    <div class="navbar-burger burger" data-target="{{ menu.id }}">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </div>

  <div id="{{ menu.id }}" class="navbar-menu">
  {% if menu.end_items is defined %}
 <!-- start menu gauche -->
 <div class="navbar-start">
 {% for item in menu.start_items %}
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

{% if menu.end_items is defined %}
<!-- start menu droite -->
<div class="navbar-end">
 {% for item in menu.end_items %}
	 {% if item.subitems is not defined %}
		<a class="navbar-item nav-section" href="{{ item.ref}}">
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