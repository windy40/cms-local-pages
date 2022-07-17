$json = file_get_contents('{{(global.path_commun)~'exemple-widget/data.json'}}');
$data_local= json_decode($json,true);

/*  reformattage des données le cas échéant		
*/
$wdata=$data_local;

