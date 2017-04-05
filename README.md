Plugin local_creation_cours
===========================

Ce plugin a pour but de proposer un formulaire de création de cours au enseignants.

Avant toute chose, l'arborescence est établie au moyen d'une extraction CSV depuis le système d'information (SI Cocktail). On y définit les 3 niveaux de base (“Domaines / DU / UE libres”, “Diplôme / mention”, “Semestre / Parcours”).

Le formulaire interroge directement le SI (vues sur base oracle), la plateforme moodle de l'année précédente et la plateforme moodle en cours. Outre ce que propose le formulaire de demande de cours de moodle, voici les fonctionnalités avancées :

  * La demande ne nécessite aucune validation manuelle, le statut de l'utilisateur est vérifié à la connexion,
  * La restauration de cours depuis la plateforme antérieure peut être effectuée dans un cours collant à la maquette,
  * Les cours mutualisés sont gérés au moyen de méta-cours à activité unique de type “URL” pointant sur le cours principal.

Pré requis 
----------

> Pour donner les droits, il faut activer “Fichier plat” dans “Gérer les plugins d'inscription” puis paramétrer : “Emplacement du fichier”.
> 
> Le numéro d'identification de l'utilisateur doit être l'UID.

Configuration
-------------

Ajout au fichier de config de moodle :
  * old_mysql : ancien serveur mysql (utilisé pour lister les cours restaurables)
  * old_database : ancien nom de la base de données,
  * si_user : nom d'utilisateur pour la connexion au SI (base oracle),
  * si_pass : mot de passe pour la connexion au SI
  * si_url_base : url d'accès à la base de la forme //server/sid



