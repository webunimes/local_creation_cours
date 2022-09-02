<?php
error_reporting(E_ALL) ;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/enrol/meta/lib.php');
require_once($CFG->dirroot.'/mod/url/lib.php');
require_once($CFG->dirroot.'/course/lib.php');

require_login();
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('base');
$PAGE->set_url('/local/creation_cours/creation_cours.php');

echo $OUTPUT->header();
?>

<?php
$datejour = date('d/m/Y');
$djour = explode("/", date('d/m/Y')); 
$auj = $djour[2].$djour[1].$djour[0]; 

$uid = $USER->username;
$idnumber = $USER->idnumber;
$nom = fullname($USER, true);

//moodleform
//require_once($CFG->dirroot.'/local/creation_cours/form_creation_cours.php');
include_once($CFG->dirroot.'/local/creation_cours/form_creation_cours.php');

if (strpos($USER->email,'@etudiant.unimes.fr') == false) { 

?>
<style type="text/css">
#grid { 
  display: grid; 
  grid-gap: 2%;
}
.unimes {
color: #E3007B;
}
figure img {
height: 300px;
object-fit: contain;
top: 0;
}

@media(max-width: 768px) {
  #grid { grid-template-columns: 50%;} 
}
@media(min-width: 768px) {
  #grid { grid-template-columns: 30% 30% 30%;} 
}
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-magnify/0.3.0/css/bootstrap-magnify.min.css" integrity="sha512-87oRirL4+UGU1hJaVeIATDDK5Jls/qE3sTFmAyc4zj+DdcJJmEgWwO4JhWaybNkz8jhNbMesbBnlg73YM5tadQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script type="text/javascript" src="js/jquery.chained.js"></script>
<script type="text/javascript" src="js/util.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-magnify/0.3.0/js/bootstrap-magnify.min.js" integrity="sha512-n1dSnMZ7YxhSyddGMrwME3dwjFV9KpBYAg8Xlkm19rdSMFEmQ4F4tAVzRETkOP9jljMy5s1+lXlZ/6ktLS0SNg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script type="text/javascript">
function setTextField(ddl, id) {
	document.getElementById(id).value = ddl.options[ddl.selectedIndex].text;
}
</script>

<div>
<h1>Cr&eacute;er votre espace de cours en ligne</h1>
</div>
<br/>

<?php
if (isset($courscree)) {
	echo "<div class='span12 success'>";
	echo "<br/>Votre cours " . $coursText . " a bien &eacute;t&eacute; cr&eacute;&eacute;.<br/><br/>";
	echo "Nom de l'enseignant : $nom <br/><br/>";
	echo '<a href="'.$CFG->wwwroot.'/local/creation_cours/creation_cours.php">Cliquez-ici pour effectuer une nouvelle cr&eacute;ation de cours</a><br/><br/>';
	echo '<a href="'.$CFG->wwwroot.'/course/view.php?idnumber='.$coursId.'" target="_blank">Cliquez-ici pour aller dans l\'espace de votre cours</a><br/><br/>';
	if (isset ($mutualises)) {
		echo "Il s'agissait d'un cours mutualis&eacute;, voici la liste des espaces créés :<br/>";
		foreach(array_keys($mutualises) as $idCours) {
			echo ' - dans '.$mutualises[$idCours]. ' référencé <a href="'.$CFG->wwwroot.'/course/view.php?idnumber='.$idCours.'" target="_blank">'.
			$idCours.'</a><br/><br/>';
		}
	}
	echo "</div></div>";
} else {
	echo "Bonjour $nom <br/><br/>";

	//Instantiate simplehtml_form
	$mform = new simplehtml_form();

	//Form processing and displaying is done here
	if ($mform->is_cancelled()) {
		//Handle form cancel operation, if cancel button is present on form
		//Set default data (if any)
		$mform->set_data($mform);
		//displays the form
		$mform->display();
	} else if ($fromform = $mform->get_data()) {
		//In this case you process validated data. $mform->get_data() returns data posted in form.
		$formdata = $mform->get_data();

		$niveau1 = $formdata->niveau1;
		$tniveau1 = $formdata->tniveau1;
		$niveau2 = $formdata->niveau2;
		$tniveau2 = $formdata->tniveau2;
		$niveau3 = $formdata->niveau3;
		$tniveau3 = $formdata->tniveau3;
		$niveau4 = $formdata->niveau4;
		$tniveau4 = $formdata->tniveau4;

		$template = $formdata->template;

		// $model = __DIR__."/templates/$template.mbz";

		// On recherche les fichiers template en base moodle : 
		$sel_tpl = "select filename, contenthash from mdl_files where filename in ('hyb_them.mbz','hyb_tuiles.mbz','presenrichi_them.mbz','presenrichi_tuiles.mbz','standard_them.mbz','standard_tuiles.mbz') and filearea='content';";

		$files = $DB->get_records_sql_menu($sel_tpl);
		// print_r($files);
		$filedir = '/data2/moodle/2022/filedir/';
		
		$model = $filedir . substr($files[$template.'.mbz'],0,2) .'/'. substr($files[$template.'.mbz'],2,2) .'/'. $files[$template.'.mbz'];
		$backup = "";
	
		//
		// guillaume adaptation postgres
		//
	
		if (isset($formdata->oldcourse) && !empty($formdata->oldcourse)) {

			/*
			$oldb = mysqli_connect ($CFG->old_mysql,$CFG->old_user,$CFG->old_passwd) or die ('ERREUR '.mysqli_error($oldb));
			mysqli_select_db ($oldb, $CFG->old_database) or die ('ERREUR '.mysqli_error($oldb));
			mysqli_query ($oldb, "set names utf8");
			*/

        	        $oldmoodle_conn_string = "host=$CFG->dbhost port=5432 dbname=$CFG->old_database user=$CFG->dbuser password=$CFG->dbpass options='--client_encoding=UTF8'";
	                $oldb = pg_connect($oldmoodle_conn_string) or die("Cannot connect to database engine!");			
			
			$oldcourse = $formdata->oldcourse;

			$requete = "SELECT fullname, timecreated FROM mdl_course WHERE id = '".$oldcourse."'";
			$resultat = pg_query ($oldb, $requete); 
			$ligne = pg_fetch_assoc($resultat);
			
			if(count($ligne) > 0) {
				//			$nameFile = str_replace(" ","_",$ligne['fullname']);
				//			$nameFile = str_replace("'","",$nameFile);
				$fichier = "backup-moodle2-course-".$oldcourse."-";
				$command = "ls -t $CFG->old_backup | grep '".$fichier."'";
				exec($command,$array);
				if(count($array) > 0)
				$backup = $CFG->old_backup.$array[0];
				else { // le fichier peut être nommé sauvegarde-moodle2-course-
					$fichier = "sauvegarde-moodle2-course-".$oldcourse."-";
					$command = "ls -t $CFG->old_backup | grep '".$fichier."'";
					exec($command,$array);
					if(count($array) > 0) $backup = $CFG->old_backup.$array[0];
				}
			}	
			
			pg_close($oldb);

		} // fin restauration

		//On écrit dans un fichier csv le cours a ajouté
		$fichierCours = "cours.csv";
		$fic = fopen($fichierCours,'w+');
		$ch = "fullname;shortname;category_path;idnumber;summary;backupfile;format\n";
		fwrite($fic,$ch);
		// On définit la catégorie de destination
		if ($tniveau1 === $tniveau2) {
			$category = $tniveau1.' / '.$tniveau3;
			$coursId = $niveau4;
			$coursue = $tniveau4;
		}	
		else {
			$category = $tniveau1.' / '.$tniveau2.' / '.$tniveau3;
			$coursId = $niveau4;
			$coursue = $tniveau4;
		}
		$ecue = explode(' '.$coursId, $coursue, 2);
		$coursText = $ecue[0];
		// TODO : Category créée avant ? : oui sauf semestres
		// 
		$format = 'topics';
		if (strpos($template,'tuiles') !== false) $format = 'tiles';

		if (isset($formdata->oldcourse) && !empty($formdata->oldcourse) && !empty($backup)) { // on blinde le test !
			$cours = ucfirst(strtolower($coursText)).";".ucfirst(strtolower($coursText))."-".trim($coursId).";".$category.";".trim($coursId).";".strtolower($coursText).";".$backup.";".$format."\n";
			fwrite($fic,$cours);
		}

		$cours = ucfirst(strtolower($coursText)).";".ucfirst(strtolower($coursText))."-".trim($coursId).";".$category.";".trim($coursId).";".strtolower($coursText).";".$model.";".$format."\n";
		fwrite($fic,$cours);

		// $connect = ocilogon($CFG->si_user,$CFG->si_pass,$CFG->si_url_base);
		$connect = oci_connect($CFG->si_user,$CFG->si_pass,$CFG->si_url_base, 'AL32UTF8');
		$sql_query = "SELECT COUNT(niveau4.libelle) AS NUMBER_OF_ROWS FROM mdl_niveau3 niveau3, mdl_niveau4 niveau4 where niveau4.code = '" . $coursId . "' and niveau3.code = niveau4.id";
		$stmt= oci_parse($connect, $sql_query);
		oci_define_by_name($stmt, 'NUMBER_OF_ROWS', $number_of_rows);
		oci_execute($stmt);
		oci_fetch($stmt);

		if ($number_of_rows > 1) {

//		if (substr($coursId, 0, 3) === "MUT") {
			// On liste les differents emplacements :
			$req = "select distinct niveau3.path, niveau4.libelle
		from mdl_niveau1 niveau1, mdl_niveau2 niveau2, mdl_niveau3 niveau3, mdl_niveau4 niveau4
		where niveau4.code = '" . $coursId . "'
		and niveau3.code = niveau4.id";
			$stmt = ociparse($connect,$req);
			ociexecute($stmt,OCI_DEFAULT);
			$num = 0;
			while (ocifetch($stmt)){ //On parcourt les résultats
				$newcategory = ociresult($stmt,1);
				$newText = ociresult($stmt,2);
				if ($newcategory != $category) {
					$num++;
					$cours = ucfirst(strtolower($newText)).";".ucfirst(strtolower($newText))."-".trim($coursId)."-".$num.";".$newcategory.";".trim($coursId)."-".$num.";".strtolower($newText).";;singleactivity\n";
					fwrite($fic,$cours);
					$mutualises[trim($coursId)."-".$num] = $newcategory . " / " . $newText;
				}
			}
		}

		fclose($fic);

		//On exécute le script pour ajouter un cours
		$commande = "/usr/bin/php ".$CFG->dirroot."/admin/tool/uploadcourse/cli/uploadcourse.php --mode=createorupdate --updatemode=dataonly --file=".__DIR__."/".$fichierCours." --delimiter=semicolon ";
		exec($commande,$outhy);
		$southy = implode("\n",$outhy);

		// On ne peut gérer les meta cours qu'après création effective des cours : c'est fait ... 
		if (isset ($mutualises)) {
			// On ajoute la méthode d'inscription "Meta" :
			$course = $DB->get_record('course',array('idnumber' => $coursId));
			// Pour que les cours avec une cle ne soient pas ouverts on ne peut pas faire de lien meta
 			// $metalplugin = enrol_get_plugin('meta');

			foreach(array_keys($mutualises) as $idCours) {
				$test = $DB->get_record('course',array('idnumber' => $idCours));
				if (isset($test->id)) {
					$module = $DB->get_record("modules", array("name" => "url"));

					// Pour que les cours avec une cle ne soient pas ouverts on ne peut pas faire de lien meta
 					// $metalplugin->add_instance($course, array('customint1'=>$test->id));
					// TEST Contruction de l'objet URL
					$data = new stdClass();
					$data->course = $test->id;
					$data->coursemodule = ''; //get_coursemodule_from_instance('url', $id);
					$data->name = 'Lien vers le cours';
					$data->intro = '';
					$data->introformat = 1;
					$data->externalurl = $CFG->wwwroot . '/course/view.php?idnumber='.$niveau4;
					$data->timemodified = time();
					$data->display = 0;
					$data->displayoptions = 'a:1:{s:10:"printintro";i:1;}';
					url_add_instance($data, null);

					$section0 = $DB->get_record("course_sections", array('course' => $test->id, 'section' => 0));

					$mod = new stdClass();
					$mod->course = $test->id;
					$mod->idnumber = '';
					$mod->module = $module->id;
					$mod->instance = $data->id; // l'id du mod url
					$mod->section = $section0->id;
					$newcmid = add_course_module($mod);
					course_add_cm_to_section($mod->course,$newcmid,0);

					context_module::instance($newcmid);
				}
			}
		}

		global $DB;
		// $sql = "SELECT c.id FROM {course} c WHERE c.idnumber='$coursId'";

		$sql = "SELECT c.id as cid, e.id as eid FROM mdl_course c, mdl_enrol e WHERE e.courseid=c.id and e.enrol='self' and c.idnumber='$coursId'";

		try {
        		$keys = $DB->get_records_sql($sql);
        		//print_r($keys);

        		foreach ($keys as $id => $record) {
        		        $idcours = $record->cid;
        		        $idenrol = $record->eid;
        		}
		 // $idcours = array_keys($DB->get_records_sql($sql))[0];
		} catch (Exception $e) {
		  echo '<center><br/><span style="padding:10px; color: white;background-color:red">',  $e->getMessage(), "</span><br/><br/>\n";
		  echo '<span style="margin:20px;"><a class="btn btn-primary" href="'.$CFG->wwwroot.'/local/creation_cours/creation_cours.php">Cr&eacute;er un nouveau cours</a></span>';
		  echo '<span style="margin:20px;"><a class="btn btn-primary" href="'.$CFG->wwwroot.'/my/index.php">Retrouver tous mes cours</a></span></center>';
		  exit;
		}
		//print_r(array_keys($idcours)[0]);
		//var_dump(get_object_vars($idcours));
		//echo "res : ".array_keys($idcours)[0];

		echo "<div class='span12 success'>";
		echo "<br/>Votre cours " . $coursText . " a bien &eacute;t&eacute; cr&eacute;&eacute;.<br/><br/>";
		echo "Nom de l'enseignant : $nom <br/><br/>";
		echo "<div id='grid'>";
		// echo '<span style="text-align: center;"><a class="btn btn-primary" href="'.$CFG->wwwroot.'/local/creation_cours/edit_self_enrol.php?courseid='.$idcours.'">Ajouter une cl&eacute; d\'inscription</a></span>';
		echo '<span style="text-align: center;"><a class="btn btn-primary" href="'.$CFG->wwwroot.'/enrol/editinstance.php?courseid='.$idcours.'&id='.$idenrol.'&type=self">Cr&eacute;er la cl&eacute; d\'inscription</a></span>';
		echo '<span style="text-align: center;"><a class="btn btn-primary" href="'.$CFG->wwwroot.'/course/view.php?idnumber='.$coursId.'" target="_blank">Acc&eacute;der à mon cours</a></span>';
		echo '<span style="text-align: center;"><a class="btn btn-primary" href="'.$CFG->wwwroot.'/local/creation_cours/creation_cours.php">Cr&eacute;er ou supprimer un cours</a></span>';
		// echo '<span><a class="btn btn-primary" href="'.$CFG->wwwroot.'/local/creation_cours/creation_cours.php">Cr&eacute;er un nouveau cours</a></span>';
		// echo '<span><a class="btn btn-primary" href="'.$CFG->wwwroot.'/my/index.php">Retrouver tous mes cours</a></span>';
		// echo '<span><a class="btn btn-primary" href="'.$CFG->wwwroot.'/local/creation_cours/annuler_creation_cours.php">Suppression d\'un cours cr&eacute;&eacute; par erreur</a></span>';
		echo "</div>";
		if (isset ($mutualises)) {
			echo "Il s'agissait d'un cours mutualis&eacute;, voici la liste des espaces créés :<br/>";
			foreach(array_keys($mutualises) as $idCours) {
				echo ' - dans '.$mutualises[$idCours]. ' référencé <a href="'.$CFG->wwwroot.'/course/view.php?idnumber='.$idCours.'" target="_blank">'.
				$idCours.'</a><br/><br/>';
			}
		}
		echo "</div></div>";
		
		// On va ensuite essayer de renseigner l'url des cours mutualises

		$southy = $southy . "\n\nServeur : " . $_SERVER['HTTP_HOST'] . " - " . $_SERVER['SERVER_ADDR'];
		$subject = "Création automatique du cours ".$coursText." (".$coursId.") pour ".$uid ;
		

		//On écrit le second fichier qui permet d'enroller les enseignants
		//echo "ecriture dans enroll.csv";
		$fichierCours = "enroll.csv";
		$fic = fopen($fichierCours,'a+');
		$ch = "add,editingteacher,".$idnumber.",".$coursId."\n";
		fwrite($fic,$ch);
		//	echo "ecriture dans enroll.csv : $ch ... done";
		exec('/usr/bin/php '.$CFG->dirroot.'/enrol/flatfile/cli/sync.php');
		$courscree = true;

                foreach ($CFG->adm_dest_mail as &$email) {

                        $emailuser = new stdClass();
                        $emailuser->email = $email;
                        $emailuser->id = -99;

                        ob_start();
                        $success = email_to_user($emailuser, $USER, $subject, $southy, '', $CFG->dirroot ."/local/creation_cours/cours.csv", "cours.csv");
                        $smtplog = ob_get_contents();
                        ob_end_clean();
                }

                unset($email);

	} else {
		// this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
		// or on the first display of the form.

		//Set default data (if any)
		$mform->set_data($mform);
		//displays the form
		$mform->display();
	}

	?>

	<br/><br/>

	<script>$("#id_niveau2").chained("#id_niveau1");</script>
	<script>$("#id_niveau3").chained("#id_niveau2");</script>
	<script>$("#id_niveau4").chained("#id_niveau3");</script>

	<br/><br/>

	<?php
} // Fin if (!isset(courscree)) 


echo $OUTPUT->footer();

} else echo "Les &eacute;tudiants n'ont pas acc&egrave;s &agrave; cette page.";
?>

</body>
</html>
