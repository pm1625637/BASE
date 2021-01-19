# DATPHP
<pre>
DATPHP est un système de gestion de fichiers de données.

Je présente ce projet bien humblement, et souhaite trouver des programmeurs enthousiasmes 
pour m'aider à faire de ce projet, un open source respectable.

Structure des données
Toutes les données sont stockées dans un tableau tridimensionnel. 
Donc, pour chaque donnée, une coordonnée [table][ligne][colonne].

Mais où stocker les noms de table dans ce cas ? 
Les noms de table sont stockés aux indices [0][0][n] par exemple :
$data[0][0][1]='tableUn';
$data[0][0][2]='tableDeux';

Les noms de colonnes sont stockés à la ligne [n][0][n] par exemple:
$data[1][0][1]='colonne1';
$data[1][0][2]='colonne2';
$data[2][0][1]='colonne1';
$data[2][0][2]='colonne2';

Exemple concret d'un fichier de données en php...
$data[0][0][1]='personnes';
$data[1][0][1]='nom';
$data[1][0][2]='prenom';
$data[1][1][1]='trump';
$data[1][1][2]='donald';
$data[1][2][1]='obama';
$data[1][2][2]='barack';
Par convention les noms des tables seront alphabétiques et aux pluriels,
les noms des colonnes seront alphanumériques et aux singuliers.

ACCÈS AUX DONNÉES
Plusieurs fonctions existent pour travailler avec les tableaux en PHP. 
Cependant j'ai créé une classe Model qui travaille avec un tableau tridimensionnel. 
Elle permet entre autres, d'ajouter, modifier ou supprimer une table, une colonne ou un enregistrement. 
Notez que le fichier de données est nommé data.php pour votre compréhension, mais n'importe quel nom de fichier 
peut être utilisé et vous pouvez même vous connecter à des fichiers différents dans un même contrôleur. 

Exemple de connexion à la base de données. Format de fichier (.php)
Le contrôleur principal Controller charge entre autres le model: Get
Le modèle Get est l'extension de Model. 
Essayez cet exercise

class Note extends Controller
{
	function __construct()
	{
		parent::__construct('data','php');
		// <HEAD>
		$this->data['title'] =' Notes';
		$this->data['head'] = $this->Template->load('head',$this->data,TRUE);
	}
 	function index()
	{
		parent::index();
	}
	function line($url)
	{
		$i_table = $this->Get->get_id_table('notes');
		$i_line = $url[2];
		$i_column =  $this->Get->get_id_column($i_table,'note');
		
		//function get_cell($x,$y,$z)
		echo ($this->Get->get_cell($i_table,$i_line,$i_column));
	
		//function get_record($strTable,$line)		
		$record = $this->Get->get_record('notes',$i_line);
		//$record est un tableau : array(3) { ["id_note"]=> string(1) "1" ["note"]=> string(6) "note 1" ["user_id"]=> string(1) "1" }
		$obj = (json_decode(json_encode($record)));
		echo $obj->note;
	}
}
Dans votre navigateur :
http://localhost/datphp/note/line/1
</pre>

<pre>
Fonctions appartenant à la classe <strong>Model</strong>
class Get extends Model
connect - Charge le fichier de données.
get_version - Retourne la version de la classe Model.
get_data - Retourne le tableau de données.
set_data - Charge le tableau de données.
count_tables - Retourne le nombre de tables dans le fichier de données.
count_columns - Retourne le nombre de colonnes d'une table.
count_max_columns - Retourne le nombre de colonnes de la table qui en compte le plus.
count_lines - Retourne le nombre d'enregistrements d'une table.
count_max_lines - Retourne le nombre d'enregistrements de la table qui en compte le plus.
export - Créer ou écrase un fichier de données avec les données courantes du tableau.
import - Importe le fichier de données dans l'objet instancié de la classe Model.
serialize - Créer 3 fichiers de format (.php, .json, .ser) avant l'exportation.
escape - Remplace les simple quote et les < et > dans le fichier de données.
unescape - Effectue le contraire de escape.
verif_alpha - Vérifie si une valeur est alphabétique.
verif_alpha_num - Vérifie si une valeur est alphanumérique.

Les tables

add_table - Ajoute une table au fichier de données.
edit_table - Permet de renommer une table.
delete_table - Supprime une table.
get_id_table - Retourne la position d'une table dans le fichier de données.
get_table - Retourne un tableau contenant tous les enregistrements d'une table.
table_exists - Retourne vrai si le nom de la table existe.
get_table_name - Retourne le nom d'une table en fonction de l'indice.
get_tables - Retourne un tableau contenant tous les noms de table.
Les colonnes
add_column - Ajoute une colonne à une table.
delete_column - Supprime une colonne à une table.
set_column - Nommer une colonne d'une table.
get_column_name - Retourne le nom d'une colonne en fonction de son indice.
get_columns - Retourne un tableau de noms de colonnes d'une table.
column_exists - Retourne vrai si un nom de colonne existe pour une table.
filter_columns - Retourne un tableau contenant toutes les valeurs de array1 qui sont présentes dans array2. Notez que les clés sont préservées.
get_id_column - Retourne la position d'une colonne dans une table.

Les coordonnées

set_cell - Affecte une valeur à une coordonnée ($int,$int,$int).
get_cell - Retourne une valeur à une coordonnée ($int,$int,$int).
del_cell - Supprime une valeur à une coordonnée ($int,$int,$int).
set_line - Sauvegarde un enregistrement.
get_line - Retourne un enregistrement.
add_line - Ajoute un enregistrement.
del_line - Supprime un enregistrement.
get_real_id - Retourne la ligne d'un enregistrement.
get - Retourne une valeur à une coordonnée ($string,$int,$string).
combine - Joint un tableau de colonnes à un tableau de valeur.

Requête sur les données

get_where_unique - Retourne un enregistrement dont la valeur d'une colonne est unique.
get_where_multiple - Retourne des enregistrements dont la valeur d'une colonne est multiple.
get_columns_of - Retourne un tableau contenant les noms de colonnes d'une table.
get_field_value_where_unique - Retourne une valeur d'une cellule en passant en paramètre un nom de colonne dont la valeur est unique.
get_record - Retourne un enregistrement sous forme de tableau associatif colonne=>valeur.
select - Retourne un enregistrement en fonction d'un choix de colonnes.
select_where - Retourne un enregistrement en fonction d'un choix de colonnes et de la valeur d'une des colonnes.

Et bien d'autres...
</pre>
