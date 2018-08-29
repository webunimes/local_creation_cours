<?php

require_once("$CFG->libdir/formslib.php");

class annul_html_form extends moodleform {
	//Add elements to form
	public function definition() {
		global $CFG;
		global $USER;
		
		$mform = $this->_form; // Don't forget the underscore! 
		$mform->setRequiredNote('* = champs obligatoires');
		$mform->setJsWarnings('Erreur de saisie ','Veuillez corriger');

		$mform->addElement('static', 'title', '', 'Vous souhaitez annuler un cours cr&eacute;&eacute; par le formulaire de cr&eacute;ation sur la plateforme p&eacute;dagogique cours.unimes.fr.');

		// Premi�re partie : cr�ation d'un cours
		
		$mform->addElement('header', 'destination', 'G&eacute;n&eacute;ral');
		$mform->addElement('static', 'description', '', 'S&eacute;lectionnez le cours cr&eacute;&eacute; par erreur dans la liste d&eacute;roulante ci-dessous.');

		// On recup les cours deja crees
		$db = mysqli_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die("Cannot connect to database engine!");
		mysqli_select_db($db, $CFG->dbname) or die("Cannot connect to database!");

		mysqli_query ($db, "set names utf8");
		$sql = "SELECT c.idnumber, c.fullname, cat.name FROM mdl_user u, mdl_role_assignments r, mdl_context cx, mdl_course c, mdl_course_categories cat  WHERE u.id = r.userid  AND u.id = $USER->id  AND r.contextid = cx.id  AND cx.instanceid = c.id  AND r.roleid = '3' AND cat.id = c.category";
		$result = mysqli_query($db, $sql);

//print_r($sql) ;
//print_r($result) ;

		if (!$result) echo "Aucun cours disponible";
		else {
			$select_course = $mform->createElement( 'select', 'course', 'Liste des cours cr&eacute;&eacute;s :', null,array('onchange' => 'setTextField(this,\'tcourse\');'));
			$select_course->addOption( 'Cours cr&eacute;&eacute; par erreur', '', array( 'disabled' => 'disabled', 'selected'=>'true' ) );
			while ($row = mysqli_fetch_assoc($result)) $select_course->addOption($row["fullname"].' ('.$row["idnumber"].') - '.$row["name"],$row["idnumber"]);
			$mform->addElement($select_course);
			$mform->addRule('course', 'Vous devez saisir une ligne', 'required', '', 'client');
			$mform->addElement('hidden', 'tcourse', '',array('id'=>'tcourse'));
		} 
		mysqli_close($db);
		
		$this->add_action_buttons($cancel = true, $submitlabel='Demander la suppression du cours');
	}
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
	
}

?>
