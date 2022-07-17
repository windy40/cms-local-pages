<?php
use Twig\Extra\Intl\IntlExtension;

const FULL_BUILD = 1;

const FIRST_BUILD = 2;

const OPTIMIZED_BUILD = 4;

const DATA_BUILD = 8;

class AppFSTree extends ArrayObject
{

    public $sect_search_dir;
    public $twig_tmpl_dirnames;

    public function __construct($context)
    {
        $this->sect_search_dir = $context['sect_search_dir'];
        $this->twig_tmpl_dirnames =$context['twig_tmpl_dirnames'];
        
        parent::__construct();
    }

    public function is_dir($name)
    {
        return ! empty($this['dir'][$name]);
    }

    public function file_exists($name)
    {
        return ! empty($this['file'][$name]);
    }

    public function dirnames($name, $namespace = "", $type = 'file')
    {
        if (! empty($this[$type][$name])) {
            $tmp1 = array_map(function ($info) use ($namespace) {
                if (! strcmp($namespace, "") || ! strcmp($info['namespace'], $namespace))
                    return $info['dirname'];
                else
                    return "";
            }, $this[$type][$name]);
            $tmp2 = array_filter($tmp1, function ($value) {
                return (! empty($value));
            });
            return $tmp2;
        } else
            return array();
    }
    public function dirinfos($name, $dirname="", $namespace = "", $type = 'file')
    {
        if (! empty($this[$type][$name])) {
            $tmp1 = array_map(function ($info) use ($namespace, $dirname) {
                if ((! strcmp($namespace, "") || ! strcmp($info['namespace'], $namespace))
                    && (! strcmp($dirname, "")
                        || ! strcmp($info['dirname'], $dirname)))
                    return $info;
                    else
                        return array();
            }, $this[$type][$name]);
                $tmp2 = array_filter($tmp1, function ($value) {
                    return (! empty($value));
                });
                    return $tmp2;
        } else
            return array();
    }
    public static function nspathinfo($fullpath){
        $res=array();
        if ($fullpath[0]=='@'){
            $pos=strpos($fullpath, '/');
            $res['namespace']=substr($fullpath, 1, $pos-1);
        }
        $res['dirname']=pathinfo(substr($fullpath, $pos+1),PATHINFO_DIRNAME).'/';
        $res['extension']=pathinfo(substr($fullpath, $pos+1),PATHINFO_EXTENSION);
        $res['basename']=pathinfo(substr($fullpath, $pos+1),PATHINFO_BASENAME);
        $res['filename']=pathinfo(substr($fullpath, $pos+1),PATHINFO_FILENAME);
        return $res;        
    }
    public function get_file_info($fullpath){
        $nspi= self::nspathinfo($fullpath);
        
        return $this->dirinfos($nspi['basename'], $nspi['dirname'],$nspi['namespace'], $type = 'file');

    }
    
    public function scan_fs_tree($prev_fs_info = array())
    {
        // look for widgets in the filesystem tree in the order specified by $sect_search_dir
        foreach ($this->sect_search_dir as $dir) {
            $directory = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::UNIX_PATHS | FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO);
            $filter = new \RecursiveCallbackFilterIterator($directory, function ($current, $key, $iterator) {
                // Skip hidden files and directories.
                /*
                 * if (!($current->getFilename() === '.'))
                 * return FALSE;
                 */
                if ($current->isDir()) {
                    // Only recurse into intended subdirectories.
                    return ! (in_array($current->getPathname(), [
                        './.',
                        './local',
                        './commun',
                        './vendor',
                        './cache',
                        './log'
                    ]));
                } else {
                    // Only consume files of interest.
                    // return strpos($current->getFilename(), 'wanted_filename') === 0;
                    return (! (($current->getFilename()[0] === '.') || in_array($current->getFilename(), [
                        'composer.json',
                        'composer.lock',
                        'config-install.json',
                        'config-site.json'
                    ])));
                }
            });
            $iterator = new \RecursiveIteratorIterator($filter);

            $in_widget = false;
            $wname="";
            $in_twig_tmpl = false;
            $tname="";
            foreach ($iterator as $info) {
                $this['traversal'][] = $info->getPathname();

                if ($info->getFilename() === '.') {
                    $dpathname = pathinfo($info->getPathname(), PATHINFO_DIRNAME);
                    $dfilename = pathinfo($dpathname, PATHINFO_FILENAME);
                    $ddirname = pathinfo($dpathname, PATHINFO_DIRNAME);

                    $dsize = $info->getSize();
                    $dmtime = $info->getMTime();

                    if ($in_twig_tmpl) { // tmpl dir are assumed not to be nested
                        $in_twig_tmpl=false;
                        $tname= "";
                    }
                    
                    if (in_array($dfilename,$this->twig_tmpl_dirnames) )
                    {
                        $in_twig_tmpl = true;
                        $tname= $dfilename;
                    }
                    if (! (strcmp($dir, $ddirname . '/')) && ! (in_array($dfilename, [
                        'img',
                        'js',
                        'css',
                        'index'
                    ])) 
                        && ! (in_array($dfilename,$this->twig_tmpl_dirnames))) {
                        $in_widget = true;
                        $wname = $dfilename;
                    } elseif (! strcmp($dir, $ddirname . '/') || ! strcmp($dir, $dpathname . '/')) {
                        $in_widget = false;
                        $wname = "";
                    }
                    $this['dir'][$dfilename][] =  array(
                        'namespace' => (empty($wname) ? '__main__' : $wname),
                        'dirname' => $dpathname . '/',
                        'size' => $dsize,
                        'mtime' => $dmtime
                    );
                } elseif (! ($info->getFilename() === '..')) {
                    $fname = $info->getFilename();
                    $fdirname = pathinfo($info->getPathname(), PATHINFO_DIRNAME);
                    $fsize = $info->getSize();
                    $fmtime = $info->getMTime();
                    
                    if(!strcmp($fdirname.'/',$dir)){
                        $in_widget = false;
                        $wname="";
                        $in_twig_tmpl = false;
                        $tname="";
                    }

                    $this['file'][$fname][] = $f_info = array(
                        'namespace' => (empty($wname) ? '__main__' : $wname),
                        'dirname' => $fdirname . '/',
                        'size' => $fsize,
                        'mtime' => $fmtime
                    );
                    if ($in_twig_tmpl)
                        $this['dir'][$tname][array_key_last($this['dir'][$tname])]['tmpl_files'][$fname]=$f_info;
                }
            }
        }
    }
}

class BuildSection extends ArrayObject
{

    public function __construct($section_info = array())
    {
        parent::__construct($section_info);
    }

    public function init_section($section_config, $context)
    {
        $section_name = $section_config['name'];

        $this['name'] = $section_name;
        $this['build']['config'] = $section_config;

        if ($context['fs_info']->is_dir($section_name)) {
            $this['build']['data'] = array();
            $this['build']['is_widget'] = true;
            $this['build']['namespace'] = ($namespace = $section_name);

            // resolve view/controller/css file pathname for widget section
            $default_filenames = [
                'view' => [
                    'field' => 'view',
                    'default' => [
                        "\"\$section_name.php\"",
                        "\"view.php\""
                    ]
                ],
                'controller' => [
                    'field' => 'controller',
                    'default' => [
                        "\"data_loader.php\""
                    ]
                ],
                'css' => [
                    'field' => 'css',
                    'default' => [
                        "substr(\"\$view\",0,-4).'.css'"
                    ]
                ]
            ];

            foreach ($default_filenames as $type => $type_df) {
                ${$type_df['field']} = "";
                if (! empty($section_config[$type_df['field']]) && $context['fs_info']->file_exists($section_config[$type_df['field']])) {
                    ${$type_df['field']} = $section_config[$type_df['field']];
                } elseif (! empty($type_df['default'])) {
                    foreach ($type_df['default'] as $dname) {
                        eval("\$fname=" . $dname . ";");
                        if ($context['fs_info']->file_exists($fname)) {
                            ${$type_df['field']} = $fname;
                            break;
                        }
                    }
                }
                if (${$type_df['field']} !== "") {
                    $dirinfo =$context['fs_info']->dirinfos(${$type_df['field']},"", $namespace)[0]; 
                    $this['build'][$type]['filepath'] =$dirinfo['dirname'] . ${$type_df['field']};
                    $this['build'][$type]['size'] =$dirinfo['size'];
                    $this['build'][$type]['mtime'] =$dirinfo['mtime'];
                    $this['build'][$type]['namespace'] =$namespace;
                }
            }

            // resolve view/controller/css loader path
            // $this['build']['view']['twig_loader'] = array_merge($context['fs_info']->dirnames($section_name, $section_name, 'dir'), $context['fs_info']->dirnames('partials', $section_name, 'dir'));
            $this['build']['view']['twig_loader'] = $context['fs_info']->dirnames($section_name, $section_name, 'dir');

            if (! empty($this['build']['controller']))
                $this['build']['controller']['twig_loader'] = $context['fs_info']->dirnames(basename($this['build']['controller']['filepath']), $section_name, 'file');
            if (! empty($this['build']['css']))
                $this['build']['css']['twig_loader'] = $context['fs_info']->dirnames(basename($this['build']['css']['filepath']), $section_name, 'file');
        } else {
            $namespace = '__main__';
            if ($context['fs_info']->file_exists($file = $section_name . '.php') || $context['fs_info']->file_exists($file = $section_name . '.html')){
                $dirinfos=$context['fs_info']->dirinfos($file,"", $namespace);
                $dirinfo=$dirinfos[array_key_first($dirinfos)];
                $this['build']['view']['filepath'] = $dirinfo['dirname'] . $file;
            }
            elseif (isset($section_config['section_modele'])){
                $dirinfos=$context['fs_info']->dirinfos('section-modele-par.php', "", $namespace);
                $dirinfo=$dirinfos[array_key_first($dirinfos)];
                $this['build']['view']['filepath'] =  $dirinfo['dirname']  . 'section-modele-par.php';
            }
            $this['build']['view']['namespace']=$namespace;
            $this['build']['view']['size'] =$dirinfo['size'];
            $this['build']['view']['mtime'] =$dirinfo['mtime'];
            $this['build']['view']['twig_loader'] = $context['default_twig_loader_path'];
        }
        
        // init section specific dependencies
        foreach(['view','controller'] as $type)
        if (!empty($this['build']['config'][$type.'_depends_on'])){
            foreach ($this['build']['config'][$type.'_depends_on'] as $dep){
                $dep_nspi=APPFSTree::nspathinfo($dep);
                if (isset($context['default_tmpl_dep'][$dep_nspi['namespace']][$dep_nspi['dirname']][$dep_nspi['basename']])
                    && !empty($dep_info=$context['default_tmpl_dep'][$dep_nspi['namespace']][$dep_nspi['dirname']][$dep_nspi['basename']]))
                    $this['build'][$type]['depends_on'][$dep]=$dep_info;
                elseif (count($finfo=$context['fs_info']->get_file_info($dep)) == 1)
                    $this['build'][$type]['depends_on'][$dep]=$finfo[array_key_first($finfo)];
                
            }
        }
    }

    public function log_msg($context, $format, ...$elts)
    {
        if (! empty($context['log_file'])) {
            $str = sprintf($format, ...$elts);
            file_put_contents($context['log_file'], $str, FILE_APPEND);
        }
    }

    public function render_view($context)
    {
        $this->log_msg($context, "[section %s]\n", $this['name']);
        if (($context['build_type'] == DATA_BUILD)||(
            ($context['build_type'] == OPTIMIZED_BUILD) && !$this->dependencies_modified($context)) ) {
            if (empty($this['build']['is_widget']) 
                || (! empty($this['build']['is_widget']) 
                    && ! empty($this['build']['controller']) 
                && $this->controller_data_valid($context))) {
                 return $this->render_view_from_cache($context);
            }
        }

        if (! empty($this['build']['is_widget']) && ! empty($this['build']['controller']))
            $this->load_controller_data($context);
        return $this->render_view_with_twig($context);
    }

    public function render_view_from_cache($context)
    {
        $this->log_msg($context, "\tview rendered from cache\n");
        return $context['cache']->file_get_contents_from_cache($this['build']['view']['in_cache']);
    }

    public function render_view_with_twig($context)
    {
        $this->log_msg($context, "\tview rendered with twig\n");
        $this->log_msg($context, "\t\tnamespace : %s\n\t\tloader_path : [", $this['build']['view']['namespace']);
        $view_loader = new \Twig\Loader\FilesystemLoader();
        if (! empty($this['build']['is_widget'])) { 
            foreach ($this['build']['view']['twig_loader'] as $lpath) {
                $this->log_msg($context, " @%s/%s,", $this['build']['view']['namespace'], $lpath);
                $view_loader->addPath($lpath, $this['build']['view']['namespace']);
            }
        }
        foreach ($context['default_twig_loader_path'] as $lpath){
            if (is_dir($lpath)){
            $this->log_msg($context, " @%s/%s,", "__main__", $lpath);
            $view_loader->addPath($lpath, "__main__");
            }
        }
//        $path_prefix = "";
        $path_prefix = '@' . $this['build']['view']['namespace'] . '/';
        
        $this->log_msg($context, "]\n");
        $view_twig = new \Twig\Environment($view_loader);
        $view_twig->addExtension(new IntlExtension());
        $view_twig->addExtension(new \Twig\Extension\StringLoaderExtension());
        $twig_tmpl = $path_prefix . basename($this['build']['view']['filepath']);
        $this->log_msg($context, "\tuse twig template : %s\n", $twig_tmpl);
        $view_gbt = $view_twig->load($twig_tmpl);
        $rendered_view = $view_gbt->render([
            'config' => $this['build']['config'],
            'data' => (! empty($this['build']['data']) ? $this['build']['data'] : array()),
            'global' => $context['global']
        ]);

        if (! empty($context['optimize_build'])) {
            $this->log_msg($context, "\trendered view cached as %s\n", $this['name'] . '_view.txt');
            $this['build']['view']['in_cache'] = $this['name'] . '_view.txt';
            $context['cache']->file_put_contents_in_cache($this['build']['view']['in_cache'], $rendered_view, time());
        }
        return $rendered_view;
    }

    public function get_css_filepath()
    {
        if (! empty($this['build']['css']))
            return $this['build']['css']['filepath'];
        else
            return "";
    }

    public function controller_data_valid($context)
    {
        $cond_1 = ((!isset($this['build']['config']['controller_ttl'])) 
                    || ((! empty($this['build']['controller']['in_cache']))
                        && $context['cache']->has_valid_cache_entry($this['build']['controller']['in_cache'], time(), $this['build']['config']['controller_ttl'])));
        $cond_2 = ((!isset($this['build']['config']['controller_depends_on']))
                    || ( !$this->dependencies_modified($context, 'controller'))); // for the time being
        $this->log_msg($context, "\tcontroller data is valid %u\n", ($cond_1 && $cond_2));
        return ($cond_1 && $cond_2);
    }

    public function load_controller_data($context)
    {
        if (($context['build_type'] != FULL_BUILD) && $this->controller_data_valid($context)) {
            if (! empty($this['build']['controller']['in_cache'])) {
                $this->log_msg($context, "\ttry loading controller data from cache : ");
                $wdata_str = $context['cache']->file_get_contents_from_cache($this['build']['controller']['in_cache'], time(), $this['build']['config']['controller_ttl']);
                if (! is_null($resp)) {
                    $this->log_msg($context, " succeed\n ");
                    $this['build']['data'] = json_decode($wdata_str, true);
                    return;
                }
            }
        }

        $this->log_msg($context, "\tcontroller data built with twig\n\t\tnamespace : %s\n\t\tloader_path : [", $this['build']['namespace']);
        $wdata = array();
        $loader = new \Twig\Loader\FilesystemLoader();
        foreach ($this['build']['controller']['twig_loader'] as $lpath) {
            $this->log_msg($context, " %s,", $lpath);
            $loader->addPath($lpath, $this['build']['namespace']);
        }
        $this->log_msg($context, "]\n");
        $twig = new \Twig\Environment($loader);
        $twig_tmpl = '@' . $this['build']['namespace'] . '/' . basename($this['build']['controller']['filepath']);
        $this->log_msg($context, "\t\tuse twig template : %s\n", $twig_tmpl);
        $gbt = $twig->load($twig_tmpl);
        $phpcode = $gbt->render([
            'config' => $this['build']['config'],
            'data' => $this['build']['data'],
            'global' => $context['global']
        ]);
        eval($phpcode);
        $this['build']['data'] = $wdata;

        if (! empty($context['optimize_build'])) {
            $this->log_msg($context, "\t\tcontroller data stored in cache as %s\n", $this['name'] . '_ctrl_data.json');
            $this['build']['controller']['in_cache'] = $this['name'] . '_ctrl_data.json';
            $wdata_str = json_encode($wdata);
            $context['cache']->file_put_contents_in_cache($this['build']['controller']['in_cache'], $wdata_str, time());
        }
    }
    public function dependencies_modified($context, $type='view'){
        if (empty($context['prev_sections']) ||
            empty($prev_sect=$context['prev_sections'][$this['name']])||
            strcmp($prev_sect['build'][$type]['filepath'], $this['build'][$type]['filepath']) ||
           ($prev_sect['build'][$type]['size'] !=$this['build'][$type]['size'])||
            ($prev_sect['build'][$type]['mtime'] !=$this['build'][$type]['mtime'])
            )
            return true;
            
            if (!empty($this['build'][$type]['depends_on']) 
                && !empty($prev_sect['build'][$type]['depends_on'])){

                foreach($this['build'][$type]['depends_on'] as $dep => $dep_info){
                    if (empty($prev_sect['build'][$type]['depends_on'][$dep])
                        || (!empty ($prev_sect['build'][$type]['depends_on'][$dep]['mtime']) 
                            && ($prev_sect['build'][$type]['depends_on'][$dep]['mtime'] != $dep_info['mtime']))
                        || (!empty ($prev_sect['build'][$type]['depends_on'][$dep]['hashkey'])
                            && ($prev_sect['build'][$type]['depends_on'][$dep]['hashkey'] != $dep_info['hashkey'])))
                            return true;
                }
                $this['build'][$type]['in_cache']=$context['prev_sections'][$this['name']]['build'][$type]['in_cache'];
                return false;
            }
         if ((strcmp($this['build'][$type]['namespace'], "__main__") && 
             $context['default_tmpl_dep'][$this['build'][$type]['namespace']]['modified']) ||
             $context['default_tmpl_dep']['__main__']['modified'])
             return true;
         
         $this['build'][$type]['in_cache']=$context['prev_sections'][$this['name']]['build'][$type]['in_cache'];
         return false;
    }
    
    public function rebuild_status($context)
    {
    }
}

class BuildAll extends ArrayObject
{

    public $config_site;

    public function __construct($config_site)
    {
        $this->config_site = $config_site;
        parent::__construct();

        if (! empty($this->config_site['build']['optimize_build']))
            $cache = new Cache($this->config_site['build']['optimize_build_options']['cache_dir']); // start cache interface

        if (! empty($build_info_str = $cache->file_get_contents_from_cache(".build"))) {
            $build_info = json_decode($build_info_str, true);

            $this['context']['prev_context'] = $build_info['context'];
            foreach ($build_info['sections'] as $section)
                $this['context']['prev_sections'][$section['name']] = new BuildSection($section);
        }
        $this['context']['cache'] = $cache;
        $this->log_init();
    }


    public function save_build_info()
    {
        $build_info = array();
        foreach ([
            'build_duration',
            'build_type',
            'default_tmpl_dep', 
            'optimize_build'
        ] as $field) {
            if (isset($this['context'][$field]))
                $build_info['context'][$field] = $this['context'][$field];
        }

        if (! empty($this->config_site['build']['optimize_build_options']['use_git_ftp_info'])) {
            $build_info['context']['commit'] = $this['context']['commit'];
        }
        $build_info['context']['build_time'] = time();
        $build_info['sections'] = $this['sections'];
        $json_str = json_encode($build_info);
        $this['context']['cache']->file_put_contents_in_cache('.build', $json_str);
    }

    public function log_init()
    {
        if (! empty($this->config_site['build']['optimize_build_options']['log']) && is_dir('./log/'))
            $this['context']['log_file'] = './log/build.log';
    }
    
    public function log_msg($format, ...$elts)
    {
        if (! empty($this['context']['log_file'])) {
            $str = sprintf($format, ...$elts);
            file_put_contents($this['context']['log_file'], $str, FILE_APPEND);
        }
    }
    
    public function init_build_conf()
    {
        // explore site file hierarchy
        $this['context']['sect_search_dir'] = [
            './'
        ];
        if (is_dir('./local'))
            $this['context']['sect_search_dir'][] = './local/';
            $this['context']['sect_search_dir'][] = $this->config_site['install']['path_commun'];
        $fs_info = new AppFSTree($this['context']);
        $fs_info->scan_fs_tree();
        $this['context']['fs_info'] = $fs_info;

        // discover twig tmpl dirs and default dependencies
        foreach ($this['context']['fs_info']['dir'] as $name => $dirs) {
            if (in_array($name, $this['context']['twig_tmpl_dirnames'])) {
                foreach ($dirs as $dirinfo){
                $fullpath = '@' . $dirinfo['namespace'] . '/' . $dirinfo['dirname'];
                $tmpl_files_str=json_encode((empty($dirinfo['tmpl_files'])?array():$dirinfo['tmpl_files']));
                $hashkey = hash('sha1',$tmpl_files_str);
                $this['context']['default_tmpl_dep'][$dirinfo['namespace']][pathinfo($dirinfo['dirname'],PATHINFO_DIRNAME).'/'][$name]['hashkey'] = 
                $hashkey;
                $this['context']['default_tmpl_dep'][$dirinfo['namespace']][pathinfo($dirinfo['dirname'],PATHINFO_DIRNAME).'/'][$name]['modified']=
                    ((!empty($this['context']['prev_context'])&&
                    !empty($this['context']['prev_context']['default_tmpl_dep'])&&
                        ($this['context']['prev_context']['default_tmpl_dep'][$dirinfo['namespace']][pathinfo($dirinfo['dirname'],PATHINFO_DIRNAME).'/'][$name]['hashkey']
                        == $hashkey)) ? false : true);
                }
                unset($this['context']['fs_info']['dir'][$name]);
            }
        }
        if (!empty($this['context']['default_tmpl_dep'])){
            foreach($this['context']['default_tmpl_dep'] as $ns=>$ns_search_dirs){
                $mod=false;
                foreach($ns_search_dirs as $tmpl_dirs)
                    foreach($tmpl_dirs as $tmpl_dir)
                        if ($tmpl_dir['modified']) {
                            $mod=true; 
                        break;
                    }
                $this['context']['default_tmpl_dep'][$ns]['modified']=$mod;
            }
        }
        

        // define internal names for unnamed sections
        $_unnamed_section_ctr = 0;
        foreach ($this->config_site['sections'] as $key => $section) {
            if (! isset($section['name'])) {
                $this->config_site['sections'][$key]['name'] = '_unnamed_' . $_unnamed_section_ctr;
                $_unnamed_section_ctr ++;
            }
        }

        foreach ($this->config_site['sections'] as $section) {
            $section_name = $section['name'];
            $this['sections'][$section_name] = new BuildSection();
            $this['sections'][$section_name]->init_section($section, $this['context']);
        }
    }
    
    public function init_data_build_conf(){
        $this['sections']=$this['context']['prev_sections'];
        foreach ($this['sections'] as $section){
            if (!empty($section['build']['is_widget'])
                && ! empty($section['build']['controller'])
                && ! empty($section['build']['controller']['depends_on'])){
                    foreach ($section['build']['controller']['depends_on'] as $dep =>$dep_info)
                        $dep_nspi=AppFSTree::nspathinfo($dep);
                        if (file_exists($dep_nspi['dirname'].$dep_nspi['basename'])){
                            $dep_info['size']=filesize($dep_nspi['dirname'].$dep_nspi['basename']);
                            $dep_info['mtime']=filemtime($dep_nspi['dirname'].$dep_nspi['basename']);
                        }
            }
        }
        $this['context']['default_tmpl_dep']=$this['context']['prev_context']['default_tmpl_dep'];
    }
    public function build_context()
    {
        $this->log_msg("--- compute build context\n");
        $this['context']['default_twig_loader_path'] = $this->config_site['build']['default_twig_loader_path'];
        $this['context']['twig_tmpl_dirnames'] = $this->config_site['build']['twig_tmpl_dirnames'];
        $this['context']['global'] = $this->config_site['global'];
        if (! empty($this->config_site['build']['optimize_build_options']['use_git_ftp_info']) && file_exists('./.git-ftp.log'))
            $this['context']['commit'] = file_get_contents('./.git-ftp.log');

        if (! empty($this->config_site['build']['optimize_build']))
            $this['context']['optimize_build'] = $this->config_site['build']['optimize_build'];
        if (! empty($this->config_site['build']['optimize_build_options']['use_git_ftp_info']) && ! empty($this['context']['prev_context']['commit']) && ! strcmp($this['context']['prev_context']['commit'], $this['context']['commit'])) {

            $this['context']['build_type'] = DATA_BUILD; // build only parts using external data and data obsolete or modified
            $this->log_msg("\tgit-ftp : no files modified\n\tbuild type : DATA_BUILD\n");
            $this->init_data_build_conf();
            return;
        } 
        elseif (! empty($this->config_site['build']['optimize_build']) && ! empty($this['context']['prev_context']['build_time'])) {
            $this['context']['build_type'] = OPTIMIZED_BUILD;
            $this->log_msg("\tbuild type : OPTIMIZED_BUILD\n");
            $this->init_build_conf();
        } else {
            $this['context']['build_type'] = FULL_BUILD;
            $this->log_msg("\tbuild type : FULL_BUILD\n");
            if (! empty($this['context']['prev_context']['build_time']))
                foreach ([
                    'sections'
                ] as $field)
                    unset($this[$field]);
                    
            $this->init_build_conf();
        }
    }

    public function render_view()
    {
        $this->log_msg("--- rendering complete view\n");
        $view_str = "";
        foreach ($this['sections'] as $section) {
            $view_str .= $section->render_view($this['context']);
        }
        return $view_str;
    }

    public function build_all()
    {
        $this->log_msg("--- Start build at %s\n",date(DATE_RFC2822));
        $duration = - hrtime(true);
        $this->build_context();
        $this->build_widget_css();
        $view = $this->render_view();

        $duration += hrtime(true);
        $this['context']['build_duration'] = $duration / 1e+6;
        $this->log_msg("--- build duration %d (milliseconds)\n", $this['context']['build_duration']);

        // with some web servers (like Apache) cannot use __destruct() property
        $this->save_build_info();
        $this['context']['cache']->save_cache_info();

        echo $view;
    }

    public function build_widget_css()
    {
        $css_path = (is_dir('./local/css') ? './local/css/' : $this->config_site['install']['path_commun'] . 'css/');
        $css_filepath = $css_path . 'style-widget.css';

        $this['context']['global']['style_widget_file'] = (is_dir('./local/css/') ? $this->config_site['global']['Rep'] . $css_path . 'style-widget.css' : $this->config_site['global']['Rep_commun'] . 'css/style-widget.css');

        if (file_exists($css_filepath) &&
            ($this['context']['build_type'] == DATA_BUILD))
            return;

        $this->log_msg("--- rebuilding style_widget_file.css\n");
        if (file_exists($this['context']['global']['style_widget_file']))
            unlink($this['context']['global']['style_widget_file']);
        foreach ($this['sections'] as $section) {
            if (! empty($fpath = $section->get_css_filepath()))
                file_put_contents($css_filepath, file_get_contents($fpath), FILE_APPEND);
        }
        /* begin log */
        if ($this->config_site['install']['log'] and is_dir('./log'))
            file_put_contents('./log/style-widget.css', file_get_contents($css_filepath));
        /* end log */
    }
}

?>