<?php 



const NO_VALIDITY_COND=0;
const NOT_MODIFIED_COND=1;
const NOT_OBSOLETE_COND=2;

class cache {
    private $tlb;
    private $tkf; // array correspondance entre hash key et path 
    private $cache_dir;
    private $debug_level;

    public function __construct($cache_dir='./cache', $debug_level=1){
        $this->cache_dir = $cache_dir;
        $this->debug_level=$debug_level;

        if (!is_dir ( $this->cache_dir ))
            mkdir ( $this->cache_dir );
            if (file_exists($this->cache_dir.'.cache')){
                $cache_info_str = file_get_contents ( $this->cache_dir.'.cache');
                $cache_info=json_decode ( $cache_info_str, true );
                $this->cache_dir=$cache_info['cache_dir'];
                $this->debug_level=$cache_info['debug_level'];
                $this->tlb = new tlb($cache_info['tlb']);
                $this->tkf = $cache_info['tkf'];
                
        }
        else {
            $this->tlb= new tlb();
            $this->tkf=array();
        }
    }
    public function __destruct(){
   }
    public function save_cache_info(){
        $json=array(
            'cache_dir'=> $this->cache_dir,
            'debug_level'=>$this->debug_level,
            'tkf'=> $this->tkf,
            'tlb' => $this->tlb
        );
        
        $cache_str = json_encode ( $json, true );
        file_put_contents ( $this->cache_dir.'.cache', $cache_str);
        
    }
    private function cached_filename($key){
            return '_'.$key;
    }
    private function hash($str){
        $h_str=hash('sha1',$str);
        return $h_str;
    }
    public function add_cache_entry($path, $timestamp=0){
        $key=$this->hash($path);
        if ($this->debug_level>0)
            $this->tkf[$key]=$path;
        $this->tlb->add_tlb_entry($key, $timestamp);
    }
    public function has_valid_cache_entry($path, $timestamp=0, $ttl=0, $update=false) { 
        $key=$this->hash($path);
        if ($this->tlb->has_valid_tlb_entry($key,$timestamp, $ttl))
            return true;
        elseif ($this->tlb->tlb_entry_exists($key))
            $this->tlb->invalidate_tlb_entry($key);
        if($update)
            $this->add_cache_entry($path, $timestamp);
        return false;
}
    public function file_put_contents_in_cache($name, $contents, $timestamp=0) {
        $key=$this->hash($name);
        if ($this->debug_level>0)
            $this->tkf[$key]=$name;
        $this->add_cache_entry($name, $timestamp);
        file_put_contents($this->cache_dir.$this->cached_filename($key), $contents);
    }

    public function file_get_contents_from_cache($name, $timestp=0, $ttl=0){
        $key=$this->hash($name);
        if ($this->tlb->has_valid_tlb_entry($key,$timestp, $ttl))
            return file_get_contents($this->cache_dir.$this->cached_filename($key));
        elseif ($this->tlb->tlb_entry_exists($key))
            $this->tlb->invalidate_tlb_entry($key);
        return null;

    }
}
    
class tlb extends ArrayObject {
        public function __construct($content=array())
        {
            parent::__construct($content);
        }
       public function add_tlb_entry($key, $timestp=0){
                $this[$key]=$timestp; 
       }
       public function tlb_entry_exists($key){
           return (isset($this[$key]));
       }
       public function has_valid_tlb_entry($key, $timestp=0, $ttl=0){
           if ($this->tlb_entry_exists($key) && ($timestp <= ($this[$key]+$ttl)))
               return true;
           else 
               return false;
       }
       public function invalidate_tlb_entry($key){
           unset($this[$key]);
       }
}




?>