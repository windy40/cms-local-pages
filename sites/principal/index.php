<?php
// load install config file .JSON
$json = file_get_contents('./config-install.json');
$config_install= json_decode($json,true);

// load site config file .JSON
$json = file_get_contents('./config-site.json');
$config_site= json_decode($json,true);

$config_site['install']=$config_install['install'];
$config_site['build']=$config_install['build'];
foreach (['Rep_commun','Rep', 'path_commun', 'path_local'] as $field)
    if (isset($config_site['install'][$field]))
        $config_site['global'][$field]=$config_site['install'][$field];

// use adequate index.php file
if (!empty($config_site['build']['optimize_build']))
	include ($config_site['install']['path_commun'].'index/index-cache.php');
else
	include ($config_site['install']['path_commun'].'index/index.php');

