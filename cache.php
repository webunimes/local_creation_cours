<?php

/*
 * permet de recontruire les données du formulaire
 * si le cache est trop vieux
 *
 */

require_once(__DIR__ . '/../../config.php');

$cache_infos = apcu_cache_info() ;
$date_cache   = $cache_infos['start_time'] ;
$age_cache = abs(time()-$date_cache)/60/60;

echo "Cache créé le : ". date("Y-m-d H:i:s", $date_cache) . "<br />";
echo "age : ". $age_cache ;

$connect = oci_connect($CFG->si_user,$CFG->si_pass,$CFG->si_url_base, 'AL32UTF8');

//
// 168 heures = 7 jours
//

if($age_cache >= 0 && $connect == true)
{
	
	// on efface le cache
	apcu_clear_cache ();

	// Le niveau 1 
	if(!apcu_exists('niveaux1')){
		$req = "select * from mdl_niveau1";
		$stmt = ociparse($connect,$req);
		ociexecute($stmt,OCI_DEFAULT);
		$niveaux1 = array();
		while (($row = oci_fetch_array($stmt, OCI_BOTH)) != false) {
			$niveaux1[] = $row;
		}
		apcu_store('niveaux1', $niveaux1);
	}
	$niveaux1_cache = apcu_fetch('niveaux1');
		
	// Le niveau 2
	if(!apcu_exists('niveaux2')){
		$req = "select * from mdl_niveau2";
		$stmt = ociparse($connect,$req);
		ociexecute($stmt,OCI_DEFAULT);
		$niveaux2 = array();
		while (($row = oci_fetch_array($stmt, OCI_BOTH)) != false) {
			$niveaux2[] = $row;
		}
		apcu_store('niveaux2', $niveaux2);
	}
	$niveaux2_cache = apcu_fetch('niveaux2');
					
	// Le niveau 3
	if(!apcu_exists('niveaux3')){
		$req = "select * from mdl_niveau3 where code in (select distinct id || '' from mdl_niveau4) or CODE like 'UEO%'";
		$stmt = ociparse($connect,$req);
		ociexecute($stmt,OCI_DEFAULT);
		$niveaux3 = array();
		while (($row = oci_fetch_array($stmt, OCI_BOTH)) != false) {
			$niveaux3[] = $row;
		}
		apcu_store('niveaux3', $niveaux3);
	}
	$niveaux3_cache = apcu_fetch('niveaux3');
	
	// Le niveau 4
	if(!apcu_exists('niveaux4')){
		$req = "select * from mdl_niveau4";
		$stmt = ociparse($connect,$req);
		ociexecute($stmt,OCI_DEFAULT);
		$niveaux4 = array();
		while (($row = oci_fetch_array($stmt, OCI_BOTH)) != false) {
			$niveaux4[] = $row;
		}
		apcu_store('niveaux4', $niveaux4);
	}
	$niveaux4_cache = apcu_fetch('niveaux4');

	oci_free_statement($stmt);

}

oci_close($connect) ;


?>
