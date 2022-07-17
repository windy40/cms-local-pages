<!doctype html>
<html lang="fr" class="has-navbar-fixed-top">
<head>
  <meta charset="utf-8">
  <title>{{global.MetaTitle}}</title>
  <meta name="description" content="{{global.MetaDesc}}">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	{% block favicon %}
  <link rel="icon" type="image/png" href="{{global.Rep_commun}}img/favicon.png" />
	{% endblock %}
	{% block header_img %}
	{% endblock %}
  <link rel="stylesheet" href="{{global.Rep_commun}}css/bulma.css">
	{% block header_link %}
	{% endblock %}

	{% block header_script %}
	<script>
/******************** nav-burger  *************/
document.addEventListener('DOMContentLoaded', () => {

  // Get all "navbar-burger" elements
  const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);

  // Add a click event on each of them
  $navbarBurgers.forEach( el => {
    el.addEventListener('click', () => {

      // Get the target from the "data-target" attribute
      const target = el.dataset.target;
      const $target = document.getElementById(target);

      // Toggle the "is-active" class on both the "navbar-burger" and the "navbar-menu"
      el.classList.toggle('is-active');
      $target.classList.toggle('is-active');

    });
  });

});
  	</script>
	{% endblock %}

	  
</head>
<body>

