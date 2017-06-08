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

		$mform->addElement('static', 'title', '', 'Vous souhaitez annuler un cours cr&eacute;&eacute; par le formulaire de cr&eacute;ation pour l\'ann&eacute;e 2017/2018 sur la plateforme p&eacute;dagogique cours.unimes.fr.');

		// Première partie : création d'un cours
		
		$mform->addElement('header', 'destination', 'G&eacute;n&eacute;ral');
		$mform->addElement('static', 'description', '', 'S&eacute;lectionnez votre cours dans la liste d&eacute;roulante ci-dessous ...');

		// On recup les cours deja crees
		$db = mysqli_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die("Cannot connect to database engine!");
		mysqli_select_db($db, $CFG->dbname) or die("Cannot connect to database!");

		mysqli_query ($db, "set names utf8");
		$sql = "SELECT c.idnumber, c.fullname, cat.name FROM mdl_user u, mdl_role_assignments r, mdl_context cx, mdl_course c, mdl_course_categories cat  WHERE u.id = r.userid  AND u.id = $USER->id  AND r.contextid = cx.id  AND cx.instanceid = c.id  AND r.component = 'enrol_flatfile' AND cat.id = cx.instanceid";
		$result = mysqli_query($db, $sql);
		
		if (!$result) echo "Aucun cours disponible";
		else {
			$select_course = $mform->createElement( 'select', 'course', 'Ancien cours :', null,array('onchange' => 'setTextField(this,\'tcourse\');'));
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
