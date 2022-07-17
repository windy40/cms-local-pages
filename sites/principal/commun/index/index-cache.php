<?php 







//if conditions for optimized build not met, revert to index.php 
if (empty($config_site['build']['optimize_build']) ||
    (!(is_dir ( ($cache_dir=$config_site['build']['optimize_build_options']['cache_dir'] )) || mkdir ( $cache_dir )))  ||
    (!empty($config_site['build']['optimize_build_options']['use_git_ftp_info'])
     && !(file_exists ( './.git-ftp.log' ) 
         && file_exists ( $config_site['install']['path_commun'] . '../.git-ftp.log' )))) {
    include ($config_site['install']['path_commun'] . 'index/index.php');
    exit ();
}

session_start ();

    /*
 * setup for Twig engine
 */
require_once $config_site['install']['path_commun'] . './../vendor/autoload.php';


include ($config_site['install']['path_commun'] . 'index/cache.php');
include ($config_site['install']['path_commun'] . 'index/build.php');

// if log set clear all log files
if ($config_site['install']['log'] and is_dir ( './log' )) {
    $files_to_del = [
        './log/config_cache.json'
    ];
    foreach ( $files_to_del as $f )
        if (file_exists ( './log/' . $f ))
            unlink ( './log/' . $f );
}

$build= new BuildAll( $config_site);
$build->build_all();


?>