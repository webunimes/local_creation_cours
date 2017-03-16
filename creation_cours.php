<?
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/enrol/meta/lib.php');
require_once($CFG->dirroot.'/mod/url/lib.php');
require_once($CFG->dirroot.'/course/lib.php');

require_login();
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('base');

echo $OUTPUT->header();

$datejour = date('d/m/Y');
$djour = explode("/", date('d/m/Y')); 
$auj = $djour[2].$djour[1].$djour[0]; 

$uid = $USER->username;
$nom = fullname($USER, true);

//moodleform
require_once($CFG->dirroot.'/local/creation_cours/form_creation_cours.php');

?>


<script type="text/javascript" src="js/jquery.chained.js"></script>
<script type="text/javascript">
function setTextField(ddl, id) {
	document.getElementById(id).value = ddl.options[ddl.selectedIndex].text;
}
</script>

<div>
<h1>Cr&eacute;er votre espace de cours en ligne</h1>
</div>
<br/>

<?
if (isset($courscree)) {
	echo "<div class='span12 success'>";
	echo "<br/>Votre cours " . $coursText . " a bien &eacute;t&eacute; cr&eacute;&eacute;.<br/><br/>";
	echo "Nom de l'enseignant : $nom <br/><br/>";
	echo '<a href="/moodle/creation_cours.php">Cliquez-ici pour effectuer une nouvelle cr&eacute;ation de cours</a><br/><br/>';
	echo '<a href="https://bobbie-test.forpro.unimes.fr/moodle/course/view.php?idnumber='.$coursId.'" target="_blank">Cliquez-ici pour aller dans l\'espace de votre 
cours</a><br/><br/>';
	if (isset ($mutualises)) {
		echo "Il s'agissait d'un cours mutualis&eacute;, voici la liste des espaces créés :<br/>";
		foreach(array_keys($mutualises) as $idCours) {
			echo ' - dans '.$mutualises[$idCours]. ' référencé <a href="https://bobbie-test.forpro.unimes.fr/moodle/course/view.php?idnumber='.$idCours.'" target="_blank">'.
			$idCours.'</a><br/><br/>';
		}
	}
	echo "</div></div>";
} else {
	echo "Bonjour $nom <br/><br/>";

	echo "Si vous souhaitez annuler une demande : supprimer un cours créé par cette interface, <a href=\"annuler_creation_cours.php\">cliquez ici</a>.<br/><br/>";

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

		$backup = "";
		//On veut récupérer une sauvegarde de l'an passé
		if (isset($formdata->oldcourse) && !empty($formdata->oldcourse)) {
			$oldb = mysqli_connect ($CFG->old_mysql,$CFG->dbuser,$CFG->dbpass) or die ('ERREUR '.mysqli_error($oldb));
			mysqli_select_db ($oldb, $CFG->old_database) or die ('ERREUR '.mysqli_error($oldb));
			mysqli_query ($oldb, "set names utf8");
			$oldcourse = $formdata->oldcourse;

			$requete = "SELECT fullname, timecreated FROM mdl_course WHERE id = '".$oldcourse."'";
			$resultat = mysqli_query ($oldb, $requete); 
			$ligne = mysqli_fetch_assoc($resultat);
			
			if(count($ligne) > 0) {
				//			$nameFile = str_replace(" ","_",$ligne['fullname']);
				//			$nameFile = str_replace("'","",$nameFile);
				$fichier = "backup-moodle2-course-".$oldcourse."-";
				$command = "ls -t /data/moodle/moodlebackup/ | grep '".$fichier."'";
				exec($command,$array);
				if(count($array) > 0)
				$backup = "/data/moodle/moodlebackup/".$array[0];
				else { // le fichier peut être nommé sauvegarde-moodle2-course-
					$fichier = "sauvegarde-moodle2-course-".$oldcourse."-";
					$command = "ls -t /data/moodle/moodlebackup/ | grep '".$fichier."'";
					exec($command,$array);
					if(count($array) > 0)
					$backup = "/data/moodle/moodlebackup/".$array[0];
				}
			}	

		} // fin restauration

		//On écrit dans un fichier csv le cours a ajouté
		$fichierCours = "cours.csv";
		$fic = fopen($fichierCours,'w+');
		$ch = "fullname;shortname;category_path;idnumber;summary;backupfile;format\n";
		fwrite($fic,$ch);
		// On définit la catégorie de destination
		if (empty($niveau4)) {
			$category = $tniveau1.' / '.$tniveau2;
			$coursId = $niveau3;
			$coursue = $tniveau3;
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
		$cours = ucfirst(strtolower($coursText)).";".ucfirst(strtolower($coursText))."-".trim($coursId).";".$category.";".trim($coursId).";".strtolower($coursText).";".$backup.";\n";
		fwrite($fic,$cours);

		if (substr($coursId, 0, 3) === "MUT") {
			// On liste les differents emplacements :
			$req = "select distinct niveau3.path, niveau4.libelle
		from mdl_niveau1 niveau1, mdl_niveau2 niveau2, mdl_niveau3 niveau3, mdl_niveau4 niveau4
		where niveau4.code = '" . $coursId . "'
		and niveau3.code = niveau4.id";
			$connect = ocilogon($CFG->si_user,$CFG->si_pass,$CFG->si_url_base);
			$stmt = ociparse($connect,$req);
			ociexecute($stmt,OCI_DEFAULT);
			$num = 0;
			while (ocifetch($stmt)){ //On parcourt les résultats
				$newcategory = ociresult($stmt,1);
				$newText = ociresult($stmt,2);
				if ($newcategory != $category) {
					$num++;
					$cours = ucfirst(strtolower($newText)).";".ucfirst(strtolower($newText))."-".trim($coursId)."-".$num.";".$newcategory.";".trim($coursId)."-".$num.";".strtolower
					($newText).";;singleactivity\n";
					fwrite($fic,$cours);
					$mutualises[trim($coursId)."-".$num] = $newcategory . " / " . $newText;
				}
			}
		}

		fclose($fic);

		//On exécute le script pour ajouter un cours
		$commande = "/usr/bin/php /usr/local/apache2/htdocs/moodle/admin/tool/uploadcourse/cli/uploadcourse.php --mode=createorupdate --file=/usr/local/apache2/htdocs/moodle/local/creation_cours/".
		$fichierCours." --delimiter=semicolon";
		exec($commande,$outhy);
		$southy = implode("\n",$outhy);

		// On ne peut gérer les meta cours qu'après création effective des cours : c'est fait ... 
		if (isset ($mutualises)) {
			// On ajoute la méthode d'inscription "Meta" :
			$course = $DB->get_record('course',array('idnumber' => $coursId));
			$metalplugin = enrol_get_plugin('meta');

			foreach(array_keys($mutualises) as $idCours) {
				$test = $DB->get_record('course',array('idnumber' => $idCours));
				if (isset($test->id)) {
					$metalplugin->add_instance($course, array('customint1'=>$test->id));
					// TEST Contruction de l'objet URL
					$data = new stdClass();
					$data->course = $test->id;
					$data->name = 'Lien vers le cours';
					$data->intro = '';
					$data->introformat = 1;
					$data->externalurl = $CFG->wwwroot . '/course/view.php?idnumber='.$niveau4;
					$data->timemodified = time();
					$data->display = 0;
					$data->displayoptions = 'a:1:{s:10:"printintro";i:1;}';
					url_add_instance($data, null);

					$module = $DB->get_record("modules", array("name" => "url"));
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

		echo "<div class='span12 success'>";
		echo "<br/>Votre cours " . $coursText . " a bien &eacute;t&eacute; cr&eacute;&eacute;.<br/><br/>";
		echo "Nom de l'enseignant : $nom <br/><br/>";
		echo '<a href="/moodle/local/creation_cours/creation_cours.php">Cliquez-ici pour effectuer une nouvelle cr&eacute;ation de cours</a><br/><br/>';
		echo '<a href="https://bobbie-test.forpro.unimes.fr/moodle/course/view.php?idnumber='.$coursId.'" target="_blank">Cliquez-ici pour aller dans l\'espace de votre 
cours</a><br/><br/>';
		if (isset ($mutualises)) {
			echo "Il s'agissait d'un cours mutualis&eacute;, voici la liste des espaces créés :<br/>";
			foreach(array_keys($mutualises) as $idCours) {
				echo ' - dans '.$mutualises[$idCours]. ' référencé <a href="https://bobbie-test.forpro.unimes.fr/moodle/course/view.php?idnumber='.$idCours.'" target="_blank">'.
				$idCours.'</a><br/><br/>';
			}
		}
		echo "</div></div>";
		
		// On va ensuite essayer de renseigner l'url des cours mutualises

		$headers = "From: no-reply@unimes.fr\r\n";
		mail("brice.quillerie@unimes.fr",utf8_decode("création automatique du cours ".$coursText." (".$coursId.") pour ".$uid),$southy,$headers);
		mail("sophie.vessiere@unimes.fr",utf8_decode("création automatique du cours ".$coursText." (".$coursId.") pour ".$uid),$southy,$headers);

		//On écrit le second fichier qui permet d'enroller les enseignants
		$fichierCours = "enroll.csv";
		$fic = fopen($fichierCours,'a+');
		$ch = "add,editingteacher,".$uid.",".$coursId."\n";
		fwrite($fic,$ch);

		exec('/usr/bin/php /usr/local/apache2/htdocs/moodle/enrol/flatfile/cli/sync.php');

		$courscree = true;

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


	<?
} // Fin if (!isset(courscree)) 

echo $OUTPUT->footer();
?>


</body>
</html>