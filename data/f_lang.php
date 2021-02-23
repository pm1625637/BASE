<?php
namespace French\fra;
function addtolang($control,$fields)
{
	$lang=array();
	$lang[$control.'_all_title'] = $control;
	$lang[$control.'_add_title'] = 'ajouter '.rtrim($control,'s');
	$lang[$control.'_edit_title'] = 'modifier '.rtrim($control,'s');
	foreach($fields as $i=>$field)
	{
		$lang[$control.'_add_'.$field.'_help'] = '';
		$lang[$control.'_add_'.$field.'_valid'] = 'valide !';
		$lang[$control.'_edit_'.$field.'_help'] = '';
		$lang[$control.'_edit_'.$field.'_valid'] = 'valide !';
		$lang[$field] = $field;
	}
	$lang[$control.'_add_message'] = 'vous avez ajout&eacute; ['.$control.'] : %s';
	$lang[$control.'_add_denied'] = 'vous n&#146;avez pas le droit d&#146;ajouter ['.$control.']';
	$lang[$control.'_edit_message'] = 'vous avez chang&eacute; ['.$control.'] : %s';
	$lang[$control.'_edit_denied'] = 'vous n&#146;avez pas le droit de modifier ['.$control.']';
	$lang[$control.'_delete_message'] = 'vous avez supprim&eacute ['.$control.'] : %s';
	$lang[$control.'_delete_denied'] = 'vous n&#146;avez pas le droit de supprimer ['.$control.']';
	$lang['user_denied'] = 'vous n&#146;avez pas le droit de modifier le détail d\'un autre utilisateur.';
	return $lang;
}
/**************************************************************************************/
//-- common
/**************************************************************************************/
$lang['user_id'] = 'user.id';
$lang['date']= 'date';
$lang['entreprise']= 'entreprise';
$lang['name']= 'nom';
$lang['email']= 'courriel';
$lang['phone']= 't&eacute;l&eacute;phone';
$lang['level']= 'niveau';
$lang['add_submit'] = 'Ajouter';
$lang['add_publish'] = 'Publier';
$lang['edit_submit'] = 'Modifier';
$lang['soumettre'] = 'Soumettre';
$lang['add'] = 'ajouter';
$lang['edit']='modifier';
$lang['delete']='supprimer';
$lang['email'] = 'courriel';
$lang['mobile'] = 'mobile';
$lang['performance'] = 'performance';
$lang['position'] = '#';
$lang['access_denied'] = 'vous n&#146;avez pas le droit d&#146;acc&eacute;der au module  : %s';
$lang['add_denied'] = 'vous n&#146;avez pas le droit d&#146;ajouter dans le module  : %s';
$lang['edit_denied'] = 'vous n&#146;avez pas le droit de modifier dans le module  : %s';
$lang['delete_denied'] = 'vous n&#146;avez pas le droit de supprim&eacute; dans le module  : %s';
$lang['access_but_denied'] = 'vous avez acc&egrave;s, mais vous n&#146;avez pas le droit d&#146;ajouter, modifier ou supprimer; dans le module  : %s';
$lang['note']='note';
$lang['piedscarre'] = 'unit&eacute;s';
$lang['prix'] = 'prix unitaire';
$lang['plage'] = 'plage';
$lang['texte'] = 'texte';
$lang['text'] = 'text';
$lang['quantite'] = 'quantit&eacute;';
$lang['item_id'] = 'item.id';
//$lang['sous_total'] = 'sous-total';
//voiture ou adresse
$lang['adresse']='adresse';
$lang['ville_id'] = 'ville';
$lang['solde'] = 'solde';
$lang['job_id'] = 'job';
$lang['id_job'] = 'id.job';
$lang['debut'] = 'd&eacute;but';
$lang['fin'] = 'fin';
$lang['servicepour'] = 'mise en service effectu&eacute; pour:';
$lang['agent'] = 'commissionnaire';
$lang['autorisepar'] ='autoris&eacute; par:';
$lang['language'] ='langue';
$lang['impression'] ='impression';
$lang['unites']='unit&eacute;s';
$lang['sommedu']='somme dû';
$lang['tempsde']='feuille de temps de';
$lang['periodedu']='pour la p&eacute;riode du';
$lang['periodeduau']='au';
$lang['signature'] ='signature';
$lang['etat']='État de compte';
$lang['correction']='correction';
$lang['transport']='d&eacute;placements';
$lang['kilometrage']='kilométrage';
$lang['kilomEtrage']='kilomÉtrage';
$lang['admin']='administration';
$lang['avance'] = 'avance';
$lang['salairenet']='salaire net';
$lang['impot']='impôt';
$lang['vacance']='vacance';
$lang['impotvacance']='impôt + vacance';
$lang['partieemployeur']='partie employeur';
$lang['connecte']='connect&eacute; en tant que';
$lang['annee']='ann&eacute;e';
$lang['modpass']='modifier mon mot de passe';
$lang['track']='rail';
$lang['french']='français';
$lang['english']='english';
$lang['sortie']='sortie';
/**************************************************************************************/
//-- bilan
/**************************************************************************************/
$lang['bilan']='bilan';
$lang['revenus']='revenus';
$lang['revenu']='revenu';
$lang['dividendes'] = 'dividendes';
$lang['dividende'] = 'dividende';
$lang['chaffaire']='chiffre d&#146;affaire';
$lang['recevable']='recevable';
$lang['depensetotale'] = 'dépense totale';
$lang['depeli'] ='d&eacute;penses &eacute;ligibles et redressement';
$lang['resultat']='r&eacutesultat';
$lang['tpspercu']='tps percue';
$lang['tvqpercu']='tvq percue';
$lang['tpsdu']='tps due';
$lang['tvqdu']='tvq due';
$lang['tpspaye']='tps pay&eacutee';
$lang['tvqpaye']='tvq pay&eacutee';
$lang['taxesapaye'] = 'taxes &agrave; pay&eacute;es';
$lang['taxespaye'] = 'taxes pay&eacutees';
$lang['liquidite'] = 'liquidit&eacute;';
/**************************************************************************************/
//-- configuration
/**************************************************************************************/
$lang['listeusers'] = 'liste des utilisateurs';
$lang['listedroitsusers'] = 'liste des droits des utilisateurs';
/**************************************************************************************/
//-- bureau
/**************************************************************************************/
$fields = array('bureau','profil','motdepasse','nas','id_salarie','salarie','naissance','adresse','mobile','metier','taux','user_id');
$lang['salarie'] = 'salarié';
$lang += addtolang('bureau',$fields);
//--fields
$lang['banque'] = 'Dû à un actionnaire'; 
//--add
$lang['bureau_add_title'] = 'Fiche salarié';
$lang['bureau_add_nas_help'] = 'format (000-000-000)';
$lang['bureau_add_naissance_help'] = 'format (aaaa-mm-jj)';
//--edit
$lang['bureau_edit_title'] = 'Modifier fiche salarié';
$lang['bureau_edit_nas_help'] = 'format (000-000-000)';
$lang['bureau_edit_naissance_help'] =  'format (aaaa-mm-jj)';
//--special visiteur
$lang['bureau_edit_denied'] = 'Appelez-nous pour une évaluation.';
/**************************************************************************************/
//-- users
/**************************************************************************************/
$fields = array('id_user','no_user','username','email','pwd','language','pwd_id_user','pwd_pwd','pwd_email','pwd_language','mobile','group_id');
$lang += addtolang('users',$fields);
//--fields labels
$lang['id_user'] = 'id';
$lang['username'] = 'utilisateur';
$lang['pwd'] = 'mot de passe';
$lang['group_id'] = 'niveau';
$lang['no_user'] = 'no.user';
//--all
$lang['users_all_title'] = 'utilisateurs';
//--add
$lang['users_add_title'] = 'ajouter un utilisateur';
$lang['users_add_username_help'] = 'créer un username';
$lang['users_add_pwd_help'] = 'créer un mot de passe';
$lang['users_add_email_help'] = 'entrez le courriel';
$lang['users_add_mobile_help'] =  'ajouter le téléphone';
//--edit
$lang['users_edit_title'] = 'modifier un utilisateur';
$lang['users_edit_username_help'] = 'changer le username';
$lang['users_edit_pwd_title'] = 'changer votre mot de passe';
$lang['users_edit_pwd_help'] = 'changer votre mot de passe';
$lang['users_edit_pwd'] = 'vous avez changer votre mot de passe';
$lang['users_edit_pwd_email_help'] = 'le courriel ne peut pas être changé';
$lang['users_edit_pwd_pwd_help'] = 'le mot de passe doit contenir que des lettres et chiffres';
$lang['users_edit_message'] = 'vous avez changer l´utilisateur: %s';
$lang['users_edit_email_help'] = 'le courriel ne peut pas être changer à moins d´en faire la demande à l´administrateur';
$lang['users_edit_denied'] = 'denied! user: %s';
/**************************************************************************************/
//-- clients
/**************************************************************************************/
$fields = array('client','id_client','no_client','adresse','email','mobile','neq','rbq','fax','responsable');
$lang += addtolang('clients',$fields);
//--fields labels
$lang['no_client'] = 'no.client';
$lang['responsable'] = 'personne ressource';
/**************************************************************************************/
//-- facture
/**************************************************************************************/
$fields = array('facture','id_facture','no_facture','date','sous_total','tps','tvq','escompte','total','client_id','p1','p2','envoyer','info','solde');
$lang += addtolang('factures',$fields);
//--fields labels
$lang['facture'] = 'facture';
$lang['id_facture'] = 'id.facture';
$lang['no_facture'] = 'num.facture';
$lang['datefacture'] = 'Date de facture ';
$lang['sous_total'] = 'sous-total';
$lang['tps'] = 'tps';
$lang['tvq'] = 'tvq';
$lang['total'] = 'total';
$lang['escompte'] = 'escompte';
$lang['client_id'] = 'client.id';
$lang['p1'] = 'paiement 1';
$lang['p2'] = 'paiement 2';
$lang['envoyer'] = 'e';
$lang['recevables'] = 'recevables';
//--all
$lang['factures_all_title'] = 'factures';
//--add
$lang['factures_add_title'] = 'ajouter un facture';
//--edit
$lang['factures_edit_title'] = 'modifier le facture';
$lang['factures_envoyer_message'] = 'vous ne pouvez pas ajouter, modifier ou supprimer, la facture %s a été envoyée au client.';
/**************************************************************************************/
//-- job
/**************************************************************************************/
$fields = array('job','id_job','date_debut','date_fin','adresse','ville_id','sous_total','facture_id','secteur_id','forlist');
$lang += addtolang('jobs',$fields);
$lang['date_debut'] = 'd&eacute;but';
$lang['date_fin'] = 'fin';
/**************************************************************************************/
//-- details job
/**************************************************************************************/
$fields = array('id_detail_job','details_job','job_id','quantite','item_id','piedscarre','prix','note','sous_total');
$lang += addtolang('details_job',$fields);
$lang['id_detail_job'] = 'id.detail.job';
//--all
$lang['details_job_all_title'] = 'Détails de jobs';
/**************************************************************************************/
//-- villes
/**************************************************************************************/
$fields = array('id_ville','ville');
$lang += addtolang('villes',$fields);
/**************************************************************************************/
//-- postes
/**************************************************************************************/
$fields = array('id_poste','poste');
$lang += addtolang('postes',$fields);
//--fields labels
$lang['id_poste'] = 'id.post';
$lang['poste'] = 'poste';
$lang['post'] = 'post';
/**************************************************************************************/
//-- items
/**************************************************************************************/
$fields = array('id_item','item','piedcarre');
$lang += addtolang('items',$fields);
/**************************************************************************************/
//-- salaries
/**************************************************************************************/
$fields = array('id_salarie','no_salarie','nas','salarie','salaries','adresse','naissance','metier','taux','mobile','user_id','actif');
$lang += addtolang('salaries',$fields);
//--fields labels
$lang['no_salarie'] = 'no.salarié';
//--all
$lang['salaries_all_title'] = 'salariés';
//--add
$lang['salaries_add_title'] = 'ajouter un salarié';
/**************************************************************************************/
//-- heures
/**************************************************************************************/
$fields = array('id_heure','salarie_id','date_debut','date_fin','performance','nbr_heure','tarif','administration','solde_temp','ajustement','note','depense','kilometrage','solde');
$lang += addtolang('heures',$fields);
//--fields labels
$lang['salarie_id'] = 'salarié';
$lang['heure'] = 'heure';
$lang['listeheures']='liste des heures';
//--fields labels sum
$lang['s_heure'] = 'heure';
$lang['s_performance'] = 'performance';
$lang['s_nbr_heure'] = 'heures';
$lang['s_salaire_brut'] = 'salaire brut';
$lang['s_employeur'] = 'partie employeur';
$lang['s_administration'] = 'cout';
$lang['s_solde_temp'] = 'profit';
$lang['s_ajustement'] = 'ajustement';
$lang['s_depense'] = 'd&eacute;penses';
$lang['s_kilometrage'] = 'kilom&eacutetrage';
$lang['roulant'] = 'profit';
$lang['heures_info_message'] = 'vous n&#146avez aucune heure!';
//--delete
/**************************************************************************************/
//-- rights
/**************************************************************************************/
$fields = array('id_right','controller_id','user_id','add','edit','delete','controller');
$lang += addtolang('rights',$fields);
$lang['rights'] = 'droits';
$lang['rights_all_title'] = 'droits';
$lang['rights_add_title'] = 'ajouter un droit';
$lang['rights_edit_title'] = 'modifier un droit';
/**************************************************************************************/
//-- depenses
/**************************************************************************************/
$fields = array('id_depense','no_cheque','date','depense','sous_total','tps','tvq','total','tps_e','tvq_e','poste_id');
$lang += addtolang('depenses',$fields);
//--feilds labels
$lang['memo'] = 'memo';
$lang['payable'] = 'payable';
//--title
$lang['sous_total'] = 'montant';
$lang['depenses_all_title'] = 'd&eacute;penses';
$lang['depenses_add_title'] = 'ajouter une d&eacute;pense';
$lang['depenses_edit_title'] = 'modifier une d&eacute;pense';
/**************************************************************************************/
//-- temps
/**************************************************************************************/
$fields = array('id_temp','date','job_id','timesheet','debut','pause','fin','heures','salarie_id','temps_double','travaux');
$lang += addtolang('temps',$fields);
//--fields labels
$lang['id_temp'] = 'id.temps';
$lang['pause'] = 'pause';
$lang['heures'] = 'hrs.reg';
$lang['temps_double'] = 'hrs.sup';
$lang['travaux'] = 'Réf.';
//--all
$lang['temps_all_title'] = 'feuilles de temps';
$lang['timesheet'] = 'feuille de temps';
//--add
$lang['temps_add_title'] = 'ajouter une journée à ma feuille de temps';
$lang['temps_add_date_help'] = 'date du jour travaillé. format (year-month-day)';
$lang['temps_add_debut_help'] = 'example: 13:00';
$lang['temps_add_fin_help'] = 'example: 21:00';
$lang['temps_add_job_id_help'] = 'jobs en cours...';
$lang['temps_add_travaux_help'] = 'Numéro de feuilles de temps (copie papier signé par une personne autorisée)';
//--edit
$lang['temps_edit_title'] = 'edit a timesheet';
$lang['temps_edit_date_help'] = 'format (year-month-day)';
$lang['temps_edit_debut_help'] = 'example: 13:00';
$lang['temps_edit_fin_help'] = 'example: 21:00';
$lang['temps_edit_travaux_help'] = 'Numéro de feuilles de temps';
/**************************************************************************************/
//-- kilometrages
/**************************************************************************************/
$fields = array('id_kilometrage','date','pointdepart','destination','km','montant','salarie_id');
$lang += addtolang('kilometrages',$fields);
$lang['frais_deplacement'] = 'frais de d&eacute;placement';
/**************************************************************************************/
//-- gestion
/**************************************************************************************/
$fields = array('id_gestion','facture_no','date','affaire','cout','profit','client_id','info','username');
$lang += addtolang('gestions',$fields);
//--fields labels
$lang['gestion'] = 'gestion';
$lang['id_gestion'] = 'gestion';
$lang['facture_no'] = 'num.facture';
$lang['affaire'] = 'projet';
/**************************************************************************************/
//-- gjob
/**************************************************************************************/
$fields = array('id_gjob','debut','fin','voiture','ville_id','gestion_id','secteur_id','track','note','mac');
$lang += addtolang('gjobs',$fields);
//--title
$lang['id_gjob'] = 'gjob';
$lang['gjobs_all_title'] = 'gjobs';
$lang['gjobs_add_title'] = 'ajouter une gjob';
$lang['gjobs_edit_title'] = 'modifier une gjob';
/**************************************************************************************/
//-- gdetails job
/**************************************************************************************/
$fields = array('id_gdetail_job','username','passed','item_id','note','gjob_id');
$lang += addtolang('gdetails_job',$fields);
//--fields labels
$lang['gdetails_job_all_title'] = 'add all details';
/**************************************************************************************/
//-- semaines
/**************************************************************************************/
$fields = array('id_semaine','semaine_id','semaine','debut','fin','plage','texte','text');
$lang += addtolang('semaines',$fields);
/**************************************************************************************/
//-- access
/**************************************************************************************/
$fields = array('id_access','access_id','access','controller_id','level');
$lang += addtolang('access',$fields);
//--title
$lang['access_all_title'] = 'accès';
$lang['access_add_title'] = 'ajouter un accès';
$lang['access_edit_title'] = 'modifier un accès';
/**************************************************************************************/
//-- controleurs
/**************************************************************************************/
$fields = array('id_controller','controller_id','controller','controleur');
$lang += addtolang('controleurs',$fields);;
/**************************************************************************************/
//-- secteurs
/**************************************************************************************/
$fields = array('id_secteur','secteur','code');
$lang += addtolang('secteurs',$fields);
//--all
$lang['id_secteur'] = 'id.service';
$lang['secteur'] = 'service';
$lang['secteurs'] = 'services';
$lang['code'] = 'secteur';
$lang['secteurs_all_title'] = 'Services';
$lang['secteurs_add_title'] = 'ajouter un service';
$lang['secteurs_edit_title'] = 'modifier un service';
/**************************************************************************************/
//-- cars
/**************************************************************************************/
$fields = array('id_car','car_id','fleetname','cartype','prefer','cculanip','ccuglobalip','ap01ip','ap02ip','ap03ip','ap04ip','sw01ip','sw02ip','wcb','ap03mac','ap04mac');
$lang['car_id'] = 'car';
$lang['id_car'] = 'car';
$lang += addtolang('cars',$fields);
/**************************************************************************************/
//-- times
/**************************************************************************************/
$fields = array('id_time','date','jobg_id','debut','pause','fin','heures','salarie_id','temps_double','travaux');
$lang += addtolang('times',$fields);
/**************************************************************************************/
//-- news
/**************************************************************************************/
$fields = array('news','dateprevue','id_new','new_id','note','user_id');
$lang += addtolang('news',$fields);
$lang['news'] = 'Répartition';
//--all
$lang['news_all_title'] = 'Répartition';
$lang['news_add_title'] = 'publier une assignation';
$lang['news_edit_title'] = 'modifier une assignation';
/**************************************************************************************/
//-- groups
/**************************************************************************************/
$fields = array('id_group','group');
$lang += addtolang('groups',$fields);
$lang['group'] = 'niveau';
/**************************************************************************************/
//-- status
/**************************************************************************************/
$fields = array('id_status','status');
$lang += addtolang('status',$fields);
$lang['id_status'] = 'id.status';
$lang['status_id'] = 'status.id';
/**************************************************************************************/
//-- secteurs
/**************************************************************************************/
$fields = array('id_secteur','secteurs');
$lang += addtolang('secteurs',$fields);
/**************************************************************************************/
//-- configs
/**************************************************************************************/
$fields = array('id_config','no_config','config','valeur');
$lang += addtolang('configs',$fields);