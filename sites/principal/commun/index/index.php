<?php
/* Moteur du CMS LOOSE PAGES 
Le CMS permet de gérer des multisites statiques monopage. 
La page d'un site est construite par assemblage de sections.
Une section est du code html pur ou un gabarit codé avec la syntaxe utilisée par le moteur de template Twig.
Les sites partagent des ressources (css, images, scripts, gabarits et sections) pour simplifier la création de sites et partager un même "look and feel".
 */

// ajouté pour permettre l'exécution du protocole OAuth2 
session_start();

// cache uses the .git-ftp-log to determine if site 
// cache only sections which to not download external data !
// NOT IMPLEMENTED YET
// $cache= isset($config_site['global']['cache']) && !empty($config_site['global']['cache']) && $config_site['global']['cache']);


// --------------- setup Twig engine --------------
require_once $config_site['install']['path_commun'].'./../vendor/autoload.php';
use Twig\Extra\Intl\IntlExtension;

//$config_site['install']['path_local']='./local/';


// if log set clear all log files
if ($config_site['install']['log'] and is_dir ( './log' )) {
    $files_to_del = [
        './log/phpcode.php',
        './log/twig-css-gbt-dir.json',
        './log/twig-css-data.json',
        './log/style-widget.css',
        './log/twig-phpcode-gbt-dir.json',
        './log/twig-phpcode-data.json',
        './log/twig-html-gbt-dir.json',
        './log/twig-html-data.json'
    ];
    foreach ( $files_to_del as $f )
        if (file_exists ( './log/' . $f ))
            unlink ( './log/' . $f );
}

$twig_common_data = array();
$twig_common_data['global']=$config_site['global'];



// configure list of paths to use by twig loaders 
//  for phpcode
$twig_phpcode_gbt_dir=array();

// for html 
$twig_html_gbt_dir=array();
$twig_html_gbt_dir[]='./';
if (is_dir('./local'))
	$twig_html_gbt_dir[]=$config_site['install']['path_local'];
$twig_html_gbt_dir[]=$config_site['install']['path_commun'];
$twig_html_gbt_dir[]=$config_site['install']['path_commun']."index/";
$twig_html_gbt_dir[]=$config_site['install']['path_commun']."gbt/";

// for twig css
$twig_css_gbt_dir=array();
if (is_dir('./local/css'))
	$twig_css_gbt_dir[]=$config_site['install']['path_local'].'css/';
$twig_css_gbt_dir[]=$config_site['install']['path_commun'].'css/';
$twig_css_gbt_dir[]=$config_site['install']['path_commun']."index/"; // NE PAS SUPPRIMER

// 
$twig_widget_list=array();
$twig_phpcode_widget_list=array();
$twig_css_widget_list=array();
$twig_css_file_list=array();

// explore site file hierarchy
$sect_search_dir=array('./');
if (is_dir('./local')) 
		$sect_search_dir[]='./local/';
$sect_search_dir[]=$config_site['install']['path_commun'];

// define internal names for unnamed sections	
$_unnamed_section_ctr=0;
foreach ($config_site['sections'] as $key=>$section){
	if (!isset($section['name'])){
		$config_site['sections'][$key]['name']='_unnamed_'.$_unnamed_section_ctr;
		$_unnamed_section_ctr++;
	}
}

foreach ($config_site['sections'] as $section){
	foreach ($sect_search_dir as $dir){
		if (is_dir($wpath=$dir.$section['name'].'/')){
			if (!in_array($section['name'],$twig_widget_list)){
				$twig_widget_list[]=$section['name'];
				$twig_common_data['sections'][$section['name']]['config']=$section;
				$twig_common_data['sections'][$section['name']]['data']=array();
			}
			$twig_html_gbt_dir[]=['namespace'=>$section['name'], 'path'=>$wpath];
			if ((isset($section['view']) && $section['view']
			    && file_exists($viewf=$wpath.$section['view']))
			    ||(file_exists($viewf=$wpath.$section['name'].'.php'))
			    ||(file_exists($viewf=$wpath.'view.php'))){
			        $twig_common_data['sections'][$section['name']]['config']['view']='@'.$section['name'].'/'.basename($viewf);
			}
			if ((isset($section['controller']) && $section['controller'] 
				&& file_exists($ctrlf=$wpath.$section['controller'])) 
			    ||(file_exists($ctrlf=$wpath.'data_loader.php'))){
					if (!in_array($section['name'],$twig_phpcode_widget_list))
						$twig_phpcode_widget_list[]=$section['name'];
					$twig_phpcode_gbt_dir[]=['namespace'=>$section['name'], 'path'=>$wpath];

//					if (!(isset($section['controller']) && $section['controller']))
//						$twig_common_data['sections'][$section['name']]['config']['controller']='data_loader.php';
                    $twig_common_data['sections'][$section['name']]['config']['controller']='@'.$section['name'].'/'.basename($ctrlf);

			}
			if ((isset($section['css']) && $section['css'] 
				&& file_exists($wfile=$wpath.'css/'.$section['css'])) 
			||	(isset($section['view']) && $section['view'] && 
				(file_exists(
					$wfile=$wpath.'css/'		
					.substr($section['view'],0, strlen($section['view'])
					- strlen(pathinfo($section['view'], PATHINFO_EXTENSION))-1)
					.'.css')))
			|| (file_exists($wfile=$wpath.'css/'.$section['name'].'.css'))){
					if (!in_array($section['name'], $twig_css_widget_list))
						$twig_css_widget_list[]=$section['name'];
						$twig_css_gbt_dir[]=['namespace'=>$section['name'], 'path'=>$wpath.'css/'];
						$twig_css_file_list[]='@'.$section['name'].'/'.pathinfo($wfile,PATHINFO_FILENAME).'.'.pathinfo($wfile,PATHINFO_EXTENSION);
			}
		}elseif (file_exists($dir.$section['name'].'.php') ||
				file_exists($dir.$section['name'].'.html') ||
				isset($section['section_modele'])){
				$twig_common_data['sections'][$section['name']]['config']=$section;
				break;
		}
				
	}
}
	$twig_common_data['global']['widget_list']= $twig_widget_list;
	$twig_common_data['global']['style_widget_file']="";
	
	
// ------------------------ TWIG CSS  -----------------
	$twig_css_data=array();
//	$twig_css_data['css_widget_list']=$twig_css_widget_list;
	$twig_css_data['css_file_list']=array_reverse($twig_css_file_list);
	
// begin log  
if ($config_site['install']['log'] and is_dir('./log')){
	$twig_css_gbt_dir_str = json_encode($twig_css_gbt_dir);
	file_put_contents('./log/twig-css-gbt-dir.json', $twig_css_gbt_dir_str);
	$twig_css_data_str = json_encode($twig_css_data);
	file_put_contents('./log/twig-css-data.json', $twig_css_data_str);
}
// end log 	
    $css_loader = new \Twig\Loader\FilesystemLoader();
    foreach ($twig_css_gbt_dir as $lpath)
       if (is_array($lpath))
            $css_loader->addPath($lpath['path'], $lpath['namespace']);
       else
            $css_loader->addPath($lpath);
            
//	$css_loader = new \Twig\Loader\FilesystemLoader($twig_css_gbt_dir);
	$css_twig = new \Twig\Environment($css_loader);
	$css_gbt = $css_twig->load('index-css-gbt.php');
	$widget_css=$css_gbt->render($twig_css_data);

// begin log  	
if ($config_site['install']['log'] and is_dir('./log'))
	file_put_contents('./log/style-widget.css', $widget_css);
// end log 

	$css_path=(is_dir('./local/css') ?
			'./local/css/':
			$config_site['install']['path_commun'].'css/');
	file_put_contents($css_path.'style-widget.css', $widget_css);
	
	$twig_common_data['global']['style_widget_file']=
		(is_dir('./local/css/') ?
			$config_site['install']['Rep'].$css_path.'style-widget.css':
			$config_site['install']['Rep_commun'].'css/style-widget.css');
			




// ---------------------------- TWIG PHP DATA LOAD CODE  
// build and eval php code to be executed priot to displaying views, ie retrieve data necessary for TWIG rendered HTML


if (!empty($twig_phpcode_widget_list)){
	$twig_phpcode_data=$twig_common_data;
	$twig_phpcode_data['phpcode_widget_list']=$twig_phpcode_widget_list;

//	$twig_phpcode_gbt_dir[]="./";
	$twig_phpcode_gbt_dir[]=$config_site['install']['path_commun']."index";

// begin log  
if ($config_site['install']['log'] and is_dir('./log')){
$twig_phpcode_gbt_dir_str = json_encode($twig_phpcode_gbt_dir);
file_put_contents('./log/twig-phpcode-gbt-dir.json', $twig_phpcode_gbt_dir_str);
$twig_phpcode_data_str = json_encode($twig_phpcode_data);
file_put_contents('./log/twig-phpcode-data.json', $twig_phpcode_data_str);
}
//  end log 

$data=array();

$phpcode_loader = new \Twig\Loader\FilesystemLoader();
foreach ($twig_phpcode_gbt_dir as $lpath)
    if (is_array($lpath))
        $phpcode_loader->addPath($lpath['path'], $lpath['namespace']);
    else
        $phpcode_loader->addPath($lpath);
            
//$phpcode_loader = new \Twig\Loader\FilesystemLoader($twig_phpcode_gbt_dir);
$phpcode_twig = new \Twig\Environment($phpcode_loader);
$phpcode_gbt = $phpcode_twig->load('index-phpcode-gbt.php');
$phpcode=$phpcode_gbt->render($twig_phpcode_data);


//  begin log  
if ($config_site['install']['log'] and is_dir('./log')){
file_put_contents('./log/phpcode.php', '<?php '.$phpcode);
}
// end log 

eval($phpcode);
}

// ------------------ TWIG HTML -----------------
// configure arrays for twig css
$twig_html_data=$twig_common_data;

$twig_html_data['widget_list']=$twig_widget_list;


// rajouter les données data aux données utilisés pour le gabarit html
foreach ($twig_html_data['widget_list'] as $widget){
	$twig_html_data['sections'][$widget]['data']=$data[$widget]['data'];
}




//  begin log 
if ($config_site['install']['log'] and is_dir('./log')){
$twig_html_gbt_dir_str = json_encode($twig_html_gbt_dir);
file_put_contents('./log/twig-html-gbt-dir.json', $twig_html_gbt_dir_str);
$twig_html_data_str = json_encode($twig_html_data);
file_put_contents('./log/twig-html-data.json', $twig_html_data_str);
}
//  begin log S

$html_loader = new \Twig\Loader\FilesystemLoader();
foreach ($twig_html_gbt_dir as $lpath)
    if (is_array($lpath))
        $html_loader->addPath($lpath['path'], $lpath['namespace']);
    else
        $html_loader->addPath($lpath);
        
//$html_loader = new \Twig\Loader\FilesystemLoader($twig_html_gbt_dir);
$html_twig = new \Twig\Environment($html_loader);
$html_twig->addExtension(new IntlExtension());
$html_twig->addExtension(new \Twig\Extension\StringLoaderExtension());

$html_gbt = $html_twig->load('index-html-gbt.php');


echo $html_gbt->render($twig_html_data);


?>

