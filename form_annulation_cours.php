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

		// Première partie : création d'un cours
		
		$mform->addElement('header', 'destination', 'G&eacute;n&eacute;ral');
		$mform->addElement('static', 'description', '', 'S&eacute;lectionnez le cours cr&eacute;&eacute; par erreur dans la liste d&eacute;roulante ci-dessous.');

		// On recup les cours deja crees
		defined('MOODLE_INTERNAL') || die();
                global $DB;

		$sql = "SELECT c.id, c.idnumber, c.fullname, cat.name FROM mdl_user u, mdl_role_assignments r, mdl_context cx, mdl_course c, mdl_course_categories cat  WHERE u.id = r.userid  AND u.id = $USER->id  AND r.contextid = cx.id  AND cx.instanceid = c.id  AND r.roleid = '3' AND cat.id = c.category";


                $courses = $DB->get_records_sql($sql, $params, 0, $limit);

		if (!$courses) echo "Aucun cours disponible";
		else {
			$select_course = $mform->createElement( 'select', 'course', 'Liste des cours cr&eacute;&eacute;s :', null,array('onchange' => 'setTextField(this,\'tcourse\');'));
			$select_course->addOption( 'Cours cr&eacute;&eacute; par erreur', '', array( 'disabled' => 'disabled', 'selected'=>'true' ) );
                	foreach ($courses as $course) {
				$select_course->addOption($course->fullname.' ('.$course->idnumber.') - '.$course->name,$course->id);
//                      	echo $course->idnumber . ' --> ' . $course->enseignant;
			}
			$mform->addElement($select_course);
			$mform->addRule('course', 'Vous devez saisir une ligne', 'required', '', 'client');
			$mform->addElement('hidden', 'tcourse', '',array('id'=>'tcourse'));
		
			$this->add_action_buttons($cancel = true, $submitlabel='Demander la suppression du cours');
		
		} 
		
	}
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
	
}

?>
