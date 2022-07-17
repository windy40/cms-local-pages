# 
    Guide de l’utilisateur


## Fonctionnalités

Le CMS CMS Loose Pages (“Pages volantes”)  est un CMS (très) simplifié :



* il utilise l’architecture MCV Model-Controler-View
* il permet de créer simplement des sites (ou sous-sites) monopage par assemblage de sections et configuration de menu 
* un site monopage peut être soit un site web avec un nom de domaine propre ou un sous-domaine (d’un site disposant d’un nom de domaine)
* les sections et le menu sont configurables à partir d’un fichier de configuration du site
* ces sites partagent des ressources communes : images, css, js 
* les sites peuvent (ou non) ré-utiliser directement des sections partagées ou des gabarits préexistants partagés pour construire de nouvelles sections 
* les sites peuvent personnaliser le style des sections partagées réutilisées


## Architecture



* les différents sites doivent être sur le même serveur web (car partage de fichiers de code php exécuté sur le serveur)
* les ressources, sections et  gabarits partagés doivent être sur le même systèmes de fichier que celui des sites monopage, au sein d’un répertoire appelé commun
* les ressources partagées à charger par le navigateur doivent être accessibles par requête http ou https
* le CMS utilise le framework [bulma](https://bulma.io/documentation/)
* le CMS utilise le moteur de gabarit [Twig](https://twig.symfony.com/) 
* le CMS dispose de son propre système de cache. Les sections ne sont “recalculées” par twig que lorsque les fichiers ou les données sont modifiés


## Dépendances

Liste des packages php utilisés : Twig

Les packages sont gérés à l’aide de “composer’, celui-ci utilise le fichier composer.json qui définit les packages à charger et les dépendances. Les packages se trouvent dans le repertoire ‘./vendor’ du site monopage principal.

Composer peut être utilisé lorsqu’on développe des sections de type widget, notamment pour utiliser des api php d’accès aux données (par exemple Google api php pour accéder à un fichier Google Sheet)

Les packages doivent être chargés en utilisant “composer” lors de l’installation du CMS Loose Pages et à chaque fois qu’un nouveau package est requis par le code php (par exemple une api d’accès à des données)  d’une section de type widget :


```
Première installation :
composer install; 

Si de nouveaux packages sont requis : 
composer update ;
```


 

Note : si la commande “composer” (pas d’accès à un terminal SSH sur ‘espace de stockage du serveur web) n’est pas disponible sur le serveur d'hébergement, il faut télécharger direcment le répertoire ‘vendor’ à la racine du site monopage principale. 


## Construire son site avec CMS Loose Pages


### Etape 1 : mettre en place le site monopage “principal”

Le site principal comporte le répertoire “commun”, à la racine du site, avec les ressources, les sections et les gabarits partagés.

Le contenu minimal du répertoire est le suivant :

Définir la configuration d’installation pour le type de déploiement choisi : developpement en local ou production sur hébergement web, nom de domaine 


### Etape 2 : créer les sections à insérer dans la page unique du site

Une section peut être de deux types :  “simple” ou “widget”.


#### Section de type “simple” 



* une section simple définit le contenu d’un bloc section dans un fichier unique avec une extension .php
* si la section a vocation à être réutilisée, le fichier doit être mis dans “./commun’, sinon dans ‘./ ou '. /local' du site
* le moteur du CMS va chercher ce fichier successivement dans les répertoires suivants : ‘./’, ‘./local’ (s’il existe), ‘./commun’

II y a plusieurs manières de créer une section “simple” :



    * en écrivant du code html “standalone” entre des balises &lt;section> et &lt;/section>, qui sera inséré après le bloc menu du bloc body
    * sous forme de gabarit pour utiliser la puissance de l’approche des gabarits TWIG et faciliter la réutilisation et la personnalisation ultérieure de la section

Par exemple, la section simple hello-with-gbt étend le gabarit section-layout-simple.php


```
hello-with-gbt.php
{% extends ('section-layout-simple.php') %}

{% block section_id %}
hello
{% endblock %}

{% block section_contenu %}
Hello world !!!{% endblock %}

commun/layout/section-layout-simple.php
<section class="section" id="{% block section_id %}{% endblock %}">
    <div class="container">
	<h1 class="title has-text-centered" id="services-title">
{% block section_titre %}
	{% endblock %}</h1>
        <h2 class="subtitle has-text-centered" id="bio-subtitle">
		{% block section_soustitre %}
        <blockquote class="tooltip">
          		{% block section_soustitre_tip %}{% endblock %}
          		<span class="tooltiptext">
{% block section_soustitre_tiptext %}{% endblock %}</span>		  
        </blockquote>
		{% endblock section_soustitre %}					  
      </h2>
	{% block section_contenu %}
	{% endblock %}
	</div>
</section>

```



#### Section de type “widget”



* elle est définie par un ensemble de fichiers placés dans un même répertoire
* le répertoire d’une section de type widget est placé soit dans le répertoire ‘./commun’ soit  dans ‘./local’ si la section n’a pas vocation à être ré-utilisée par un autre site monopage.
* le moteur du CMS va chercher ce répertoire  successivement dans les répertoires suivants :  ‘./local’ (s’il existe), ‘./commun’. 

Une section “widget” comporte 



    * une ou plusieurs “vues”  \
Une vue est un gabarit (code html avec des balises twig) qui définit un bloc section qui sera inséré après le bloc menu du bloc body
    * le cas échéant, d’autres éléments de définition :
        * un controleur (appelé aussi “data_loader”) \
Un controleur est du code php qui est exécuté sur le serveur avant la génération de la page pour récupérer les données et controler l’affichage de la section.
        * un fichier css pour “styler” la (ou les vues) de la section \
Le fichier css n’est pas un gabarit  \
Le nom du fichier est explicité dans le fichier de configuration de site ou par défaut dénommé  nom_section_widget.css ou nom_view.php et placé dans un répertoire “css”, sous-repertoire du répertoire de la section_widget. Le moteur CMS va automatiquement<span style="text-decoration:underline;"> agréger l</span>es fichiers css des sections widgets dans un fichier style-widget.css qui est placé dans le répertoire ‘./commun/css’ ou ‘./local/css’ s’il existe, la balise &lt;link> est intégrée automatiquement dans le header.
        * Attention : Les scripts js et/ou les images spécifiques à la section sont à installer dans “./commun/js” ou “./local/js” et/ou “./commun/img” ou “./local/js”. Il faut également personnaliser le header en incluant les liens à ces ressources dans le block {% block header_script %} ou le block {% block header_img %} du fichier header.php.


#### Utiliser les gabarits pour définir le layout d’une section

Les gabarits peuvent être utilisés pour définir un layout global de section, ce dernier sera étendu au sens de Twig pour créer une section simple ou une vue d’une section widget.


### Etape 3 : définir les sections à intégrer dans le site monopage et le menu associé

Cela se fait à l’aide du fichier de configuration du site en format json :



* en définissant les sections à afficher et l’ordre d’affichage dans la rubrique “sections” 
* en définissant le menu de navigation affiché dans le haut de la page

pour la  section simple, définie dans le fichier “hello.php”, insérer :


```
		{
			"name": "hello"
		},
```


pour la section “exemple-widget” , définie dans le répertoire “exemple-widget”, insérer : 


```
		{
			"name": "exemple-widget",
			"view": "exemple-widget-with-section-modele.php",
			"controler": "data_loader_from_file.php"
		}
```


L’entrée “name” est obligatoire (ce nom doit être unique pour toutes les sections).

Si l’entrée “view” est omise, le moteur du CMS utilisera par défaut le fichier “name.php”.

Si l’entrée “controler” est omise, le moteur  CMS cherchera par défaut le fichier “data_loader.php”.

A COMPLETER

Les autres entrées correspondent à des valeurs qui serviront  à instancier ou contrôler le rendu des gabarits par Twig


### Etape 4 (optionnelle) : mettre en place les sites monopages secondaires

Le contenu minimal du répertoire est le suivant :

Définir la configuration d’installation pour le type de déploiement choisi : dev/prod, nom de domaine, accès aux ressources, sections et gabarits partagés


### Etape 5 (optionnelle) :réutiliser/personnaliser les sections partagées et  créer les sections spécifiques au site monopage 


#### Personnaliser une section simple partagée



* créer un fichier du même nom que la section simple partagée qui étend la section simple partagée au sens Twig  
* mettre le fichier dans le répertoire  ./local du site secondaire 
* s’il n’existe pas encore créer le fichier ./local/css/style.css qui étend le fichier ./commun/css/style.css et insérer le css dans entre les tags {% block style-local %} 

Personnaliser une section widget partagée



* créer un répertoire du nom du widget partagé dans le répertoire ./local du site secondaire
* possibilité de personnalisation 
    * créer le fichier de personnalisation de la “vue” par extension de la vue partagée ou créer un nouvelle vue des données 
    * créer un  fichier css personnalisé. \
ATTENTION Il faut lui donner un nom différent des fichiers css du répertoire de la section dans répertoire commun et le spécifier dans champ “css” de la section dans le fichier de configuration du site
    * personnaliser le data loader par extension (par exemple pour filtrer et ne garder qu’une partie des données)

La création des sections spécifiques se fait de la même façon qu’à l’étape 2


### Etape 6 : définir les sections à intégrer dans le site monopage et le menu associé

identique à l’étape 3


## Utiliser le gabarit prédéfini “section-modele-par” pour définir le layout global d’une section

C’est un gabarit paramétrable pour définir un layout global de section à deux niveaux : 



1. layout de la section (avec un titre de section et un contenu de section typiquement)  
2. layout du contenu de la section (une organisation du contenu en blocs et en  colonnes typiquement).

Le gabarit “section-modele-par” utilise deux gabarits 



1. un gabarit “section-layout…php” qui définit le layout de la section pour le bloc html &lt;section>...&lt;/section>. \
Ce gabarit comporte un tag Twig  {% block section_content %}
2. un gabarit “content-layout…php” qui définit le layout pour le bloc html correspondant à section_content

Exemple de gabarit “section-layout…php”


```
<section class="section" id="{% block section_id %}{% endblock %}">
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
```


Exemple de gabarit “content-layout…php”


```
<div class="columns">
    <div class="column is-1">
    </div>
    <div class="column is-4">
		{% block box1 %}
		{% endblock %}
    </div>
    <div class="column is-7 has-text-left has-text-justified">
		<div class="content">
		{% block box2 %}			
		{% endblock %}
		</div>

</div>
```


Le gabarit “section-modele-par” combine les deux layouts pour définir le layout global d’une section.

Il peut être utilisé de deux manières : 



1. en créant une section dans un fichier &lt;nom_section>.php

    ```
{% extends "section-modele-par.php" %} 

{% set section_layout='section-layout-default.php' %}
{% set content_layout='content-layout-default.php' %}


{# section blocks #}
{% block section_id %}
exemple
{% endblock %}

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
<h3>box 2</h3><p>Ce gabarit définit un layout de section à deux niveaux : <ul><li>le layout global de la section(typiquement le titre, soustitre, contenu) </li><li> le layout du contenu de la section (typiquement organisation en blocs et disposition des blocs du contenu)</li></ul>Pour cela il s'appuie ('extends' en Twig) sur le gabarit du layout global de la section  et incorpore ('embed' en Twig) le layout du contenu.Le choix du layout de section du layout de contenu sont paramétrables.</p>
{% endblock %}
```


2. en définissant le contenu de la section dans le champ ‘sections’ du  fichier config-site.json

    ```
		{
			"section_modele": {
				"section_layout": "section-layout-default.php",
				"content_layout": "content-layout-default.php",
				"section_blocks": {
					"section_id": "exemple",
					"section_title": "Exemple de section ",
					"section_subtitle": "créée avec config-site"
				},
				"content_blocks": {
					"box1": "<h3>box 1</h3>Bonjour ",
					"box2": "<h3>box 2</h3><p>Ce gabarit définit un layout de section à deux niveaux : <ul><li>le layout global de la section(typiquement le titre, soustitre, contenu) </li><li> le layout du contenu de la section (typiquement organisation en blocs ey disposition des blocs du contenu)</li></ul>Pour cela il s'appuie ('extends' en Twig) sur le gabarit du layout global de la section  et incorpore ('embed' en Twig) le layout du contenu.Le choix du layout de section du layout de contenu sont paramétrables.</p>"
				}
			}
		}
```




## Optimisation de la construction de la page

La construction de la page peut être optimisée en mettant en cache :



* les données résultant de l’exécution du controleur, lorsqu’il existe, qui servent au paramétrage de la vue de la section
* le résultat du “calcul” de la vue de la section effectué par le moteur de gabarit Twig

A la prochaine requête d’afficahge du site, la section “calculée” précedemment par Twig et cachée peut être directement utilisée pour construire la paage à condition que les fichiers de la section (c’est-à-dire les fichiers “view”, “controlleur s’il existe, <span style="text-decoration:underline;">et</span>  les gabarits, notamment ceux définissant le layout, utilisés par “view” et “controleur” s’il existe) n’ont pas été modifiés et les données sont toujours valides.

Différents mécanismes sont mis en oeuvre pour optimiser la construction de la page

Le CMS explore l’arborescence de fichiers du site secondaire, objet de la requête d’affichage,<span style="text-decoration:underline;"> et</span> du site principal pour déterminer si les fichiers “config-site”,  “view”, “controleur” et fichiers lus par controleur le cas échéant et les fichiers de gabarits ont été modifiées depuis la précédente requête.

Par défaut, si l’un des répertoires de gabarits, potentiellement utilisés par la section, a été modifié, la section doit être “recalculé”.

Directives de section dans le fichier de configuration de site



* la directive “controler_ttl” indique la durée de vie dans le cache des données, résultat de l’exécution du controleur d’une section \
Si la durée de vie est 0, le controleur doit être exécuté à chaque nouvelle requete d’affichage de la page
* les directives “view_depends_on” et “controler_depends_on” permettent de spécifier les fichiers ou répertoires (notamment les répertoires de gabarits) nécessaires pour “calculer” la section. \
Si l’un des fichiers ou répertoires de la liste est modifié, il est necessaire de recalculer la section.

    Lorsqu’un controleur lit des données dans un fichier, il convient d’utiliser la directive “controler_depends_on” pour recalculer la section que si le fichier de données est modifié


Options de configuration de l’optimisation de la constuction de la page :



* “use_git_ftp_info” : si “true”, le fichier .git-ftp.log qui contient le commit de la dernière version transférée par ftp est utilisée pour savoir si les fichiers ont été modifiées \
Attention : seuls les fichiers transférés par ftp qui ne sont pas ignorées par git (voir .git-ignore) ou par git ftp (voir .git-ftp-ignore ou exclude de l’action ftp de github) sont suivis en modification par ce dispositif. \
Si le numero de commit dans .git-ftp.log est inchangé par rapport à la précédente requête d’affichage, seule la validité des données, résutat de l’exécution du controleur d’une section, lorsqu’il existe, est vérifiée. Si les données sont valides, alors la section n’est pas recalculée.

 