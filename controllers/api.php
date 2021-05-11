<?php
class Api extends Controller
{
	function __construct()
	{
		parent::__construct('data','php');
		// <HEAD>
		$this->data['title'] =' API';
		$this->data['head'] = $this->Template->load('head',$this->data,TRUE);
	}
	function index()
	{
		parent::index();
	}
	function get($url)
	{
		// Headers requis
		header("Access-Control-Allow-Origin: *");
		header("Content-Type: application/json; charset=UTF-8");
		header("Access-Control-Allow-Methods: GET");
		header("Access-Control-Max-Age: 3600");
		header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

		// On vérifie que la méthode utilisée est correcte
		// On vérifie que la clé du user existe : get_field_value_where_unique($strTable,$strColumn,$unique,$strField)
		$key = $this->Sys->get_field_value_where_unique('users','password',$url[VALUE],'password'); 
		if( $_SERVER['REQUEST_METHOD'] == 'GET' && $key)
		{
				$record = $this->Get->get_record($url[TABLE],$url[INDEX]);
				if($record)
				{
						// On envoie le code réponse 200 OK
						http_response_code(200);

						foreach($record as $k=>$text)
						{
							 $this->Get->unescape($text); 
							 $record[$k] = $text;
						}
						// On encode en json et on envoie
						echo json_encode($record,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
				}
				else
				{
						// On gère l'erreur
						http_response_code(405);
						echo json_encode(["message" => "Enregistrement introuvable!"],JSON_UNESCAPED_UNICODE);	
				}
		}
		else
		{
			// On gère l'erreur
			http_response_code(405);
			echo json_encode(["message" => "La méthode n'est pas autorisée"],JSON_UNESCAPED_UNICODE);
		}
	}
}
?>