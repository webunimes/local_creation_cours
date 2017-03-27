<?php


require_once("$CFG->libdir/formslib.php");

class simplehtml_form extends moodleform {
	//Add elements to form
	public function definition() {
		global $CFG;
		global $USER;
		
		$mform = $this->_form; // Don't forget the underscore! 
		$mform->setRequiredNote('* = champs obligatoires');
		$mform->setJsWarnings('Erreur de saisie ','Veuillez corriger');

		$mform->addElement('static', 'title', '', 'Bienvenue sur le formulaire de cr&eacute;ation de votre espace de cours pour l\'ann&eacute;e 2017/2018 sur la plateforme 
p&eacute;dagogique cours.unimes.fr.');

		// Première partie : création d'un cours
		
		$mform->addElement('header', 'destination', 'G&eacute;n&eacute;ral');
		$mform->addElement('static', 'description', '', 'S&eacute;lectionnez votre cours dans les listes d&eacute;roulantes ci-dessous ...');

		// On stocke les cours deja crees
		$db = mysqli_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die("Cannot connect to database engine!");
		mysqli_select_db($db, $CFG->dbname) or die("Cannot connect to database $CFG->dbname!");

		mysqli_query ($db, "set names utf8");
		$sql = "SELECT c.idnumber, concat(u.firstname,' ', u.lastname) enseignant FROM mdl_user u, mdl_role_assignments r, mdl_context cx, mdl_course c  WHERE u.id = r.userid  AND 
r.contextid = cx.id  AND cx.instanceid = c.id  AND r.component = 'enrol_flatfile'";
		$result = mysqli_query($db, $sql);
		$courscrees = array();
		while($row = mysqli_fetch_array($result)) {
			$courscrees[$row['idnumber']] = $row['enseignant']; 
		}
		mysqli_close($db);
		
		$connect = ocilogon($CFG->si_user,$CFG->si_pass,$CFG->si_url_base);
		
		// Le niveau 1 
		$req = "select * from mdl_niveau1";
		$stmt = ociparse($connect,$req);
		ociexecute($stmt,OCI_DEFAULT);
		$select_niveau1 = $mform->createElement( 'select', 'niveau1', 'Niveau 1 :', null, array('onchange' => 'setTextField(this,\'tniveau1\');'));
		$select_niveau1->addOption( 'Domaines / DU / UE libres', '', array( 'disabled' => 'disabled', 'selected'=>'true' ) );
		while (ocifetch($stmt)) $select_niveau1->addOption(ociresult($stmt,2),ociresult($stmt,1));
		$mform->addElement($select_niveau1);
		$mform->addRule('niveau1', 'Vous devez saisir une ligne dans "Domaines / DU / UE libres"', 'required', '', 'client');
		$mform->addElement('hidden', 'tniveau1', '',array('id'=>'tniveau1'));

		// Le niveau 2
		$req = "select * from mdl_niveau2";
		$stmt = ociparse($connect,$req);
		ociexecute($stmt,OCI_DEFAULT);
		$select_niveau2 = $mform->createElement( 'select', 'niveau2', 'Niveau 2 :', null, array('onchange' => 'setTextField(this,\'tniveau2\');'));
		$select_niveau2->addOption( 'Dipl&ocirc;me / mention', '', array( 'disabled' => 'disabled', 'selected'=>'true' ) );
		while (ocifetch($stmt)) $select_niveau2->addOption(ociresult($stmt,2),ociresult($stmt,1),array('class'=>ociresult($stmt,3)));
		$mform->addElement($select_niveau2);
		$mform->addRule('niveau2', 'Vous devez saisir une ligne dans "Dipl&ocirc;me / mention"', 'required', '', 'client');
		$mform->addElement('hidden', 'tniveau2', '',array('id'=>'tniveau2'));
		
		// Le niveau 3
		$req = "select * from mdl_niveau3 where code in (select distinct id || '' from mdl_niveau4) or CODE like 'UEL%'";
		$stmt = ociparse($connect,$req);
		ociexecute($stmt,OCI_DEFAULT);
		$select_niveau3 = $mform->createElement( 'select', 'niveau3', 'Niveau 3 :', null, array('onchange' => 'setTextField(this,\'tniveau3\');'));
		$select_niveau3->addOption( 'Semestre / Parcours', '', array( 'disabled' => 'disabled', 'selected'=>'true' ) );
		while (ocifetch($stmt)) $select_niveau3->addOption(ociresult($stmt,2),ociresult($stmt,1),array('class'=>ociresult($stmt,3)));
		$mform->addElement($select_niveau3);
		$mform->addRule('niveau3', 'Vous devez saisir une ligne dans "Semestre / Parcours"', 'required', '', 'client');
		$mform->addElement('hidden', 'tniveau3', '',array('id'=>'tniveau3'));
		
		// Le niveau 4
		$req = "select * from mdl_niveau4";
		$stmt = ociparse($connect,$req);
		ociexecute($stmt,OCI_DEFAULT);
		$select_niveau4 = $mform->createElement( 'select', 'niveau4', 'Niveau 4 :', null, array('onchange' => 'setTextField(this,\'tniveau4\');'));
		$select_niveau4->addOption( 'Cours', '', array( 'disabled' => 'disabled', 'selected'=>'true' ) );
		while (ocifetch($stmt)) {
			if (in_array(ociresult($stmt,1),array_keys($courscrees)))
			$select_niveau4->addOption(ociresult($stmt,2),ociresult($stmt,1),array('disabled' => 'disabled', 'class'=>ociresult($stmt,3)));
			else $select_niveau4->addOption(ociresult($stmt,2),ociresult($stmt,1),array('class'=>ociresult($stmt,3)));
		}
		$mform->addElement($select_niveau4);
		$mform->addRule('niveau4', 'Vous devez saisir une ligne dans "Cours"', 'required', '', 'client');
		$mform->addElement('hidden', 'tniveau4', '',array('id'=>'tniveau4'));
		
		// Seconde partie : Restauration de cours 
		$mform->addElement('header', 'source', 'R&eacute;cup&eacute;ration d\'un cours de l\'an dernier');
		$mform->closeHeaderBefore('source');
		$mform->addElement('static', 'description', '', 'Il est possible de restaurer un cours de l\'ancienne plateforme dans un cours nouvellement cr&eacute;&eacute; sur la plateforme 2017.');
		$mform->addElement('static', 'description', '', 'Choisir le cours parmi les cours auxquels vous aviez acc&egrave;s sur l\'ancienne plateforme');

		$db = mysqli_connect($CFG->old_mysql, $CFG->dbuser, $CFG->dbpass) or die("Cannot connect to database engine!");
		mysqli_select_db($db, $CFG->old_database) or die("Cannot connect to database $CFG->dbname !");
		mysqli_query ($db, "set names utf8");
		$sql = "SELECT distinct c.id courseid, c.fullname coursename, c.shortname shortname FROM mdl_user u, mdl_role_assignments r, mdl_context cx, mdl_course c WHERE u.id = 
r.userid AND r.contextid = cx.id AND cx.instanceid = c.id AND r.roleid in (2,3) AND cx.contextlevel =50 ";

		if ($USER->username != 'admin') $sql .= "AND u.username = '".$USER->username."'";
		$result = mysqli_query($db, $sql) or die(mysqli_error($db));

		if (!$result) echo "Aucun cours disponible";
		else {
			
			$select_oldcourse = $mform->createElement( 'select', 'oldcourse', 'Ancien cours :', null);
			$select_oldcourse->addOption( 'Ancien cours', '', array( 'disabled' => 'disabled', 'selected'=>'true' ) );
			
			while ($row = mysqli_fetch_assoc($result)) $select_oldcourse->addOption($row["coursename"] . '(' .$row["shortname"] .')',$row["courseid"]);
			$mform->addElement($select_oldcourse);
			// $mform->addRule('oldcourse', 'Vous devez saisir une ligne dans "Ancien cours"', 'required', '', 'client');
		} 
		mysqli_close($db);
		
		$this->add_action_buttons();
	}
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}

?>
