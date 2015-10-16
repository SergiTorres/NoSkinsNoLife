<?php 
	include 'extras/apikey.php'; 
	include 'extras/OpenId.php';

	$OpenID = new LightOpenID("localhost");
	session_start();

	if(!$OpenID->mode){
		
		if(isset($_GET['login'])){
			$OpenID->identity = "http://steamcommunity.com/openid";
			header("Location: {$OpenID->authUrl()}");
		}
		
		if(!isset($_SESSION['T2SteamAuth'])){
			$login = "<a id='login' href='?login'></a>";
			function cuerpo(){
				
			}
		}
		
	}elseif($OpenID->mode == "cancel"){
		echo "El usuario ha cancelado la autenticación.";
	} else{
		if(!isset($_SESSION['T2SteamAuth'])){
			
			$_SESSION['T2SteamAuth'] = $OpenID->validate() ? $OpenID->identity : null;
			$_SESSION['T2SteamID64'] = str_replace("http://steamcommunity.com/openid/id/", "", $_SESSION['T2SteamAuth']);
			
			if($_SESSION['T2SteamAuth'] !== null){
				
				$Steam64 = str_replace("http://steamcommunity.com/openid/id/", "", $_SESSION['T2SteamAuth']);
				
				$profile = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$api}&steamids={$Steam64}");
				$buffer = fopen("cache/{$Steam64}.json", "w+");
				fwrite($buffer, $profile);
				fclose($buffer);

				$inventory = file_get_contents("http://steamcommunity.com/profiles/{$Steam64}/inventory/json/730/2");
												

				$buffer = fopen("cache/inventory{$Steam64}.json", "w+");
				fwrite($buffer, $inventory);
				fclose($buffer);
				
				$allItems = file_get_contents("http://api.steampowered.com/IEconItems_730/GetSchema/v2/?key={$api}");
				$buffer = fopen("cache/allItems{$Steam64}.json", "w+");
				fwrite($buffer, $allItems);
				fclose($buffer);
			
//	http://api.steampowered.com/IEconService/GetTradeOffers/w001/?key={$api}&get_sent_offers={}&get_received_offers={}&get_descriptions={}&language={}&active_only{}&historical_only={}&time_historical_cutoff={}
//bool//get_sent_offers==Solicitar el listado de ofertas enviadas
//bool//get_received_offers == Solicite la lista de las ofertas recibidas
//bool//get_descriptions == Si se activa, los datos de visualización tema de las partidas incluidas en las ofertas de comercio devueltos también serán devueltos.
//string//language	== El idioma a utilizar durante la carga de datos de visualización artículo.
//bool//active_only == Indica sólo debemos volver ofertas que todavía están activos, o las ofertas que han cambiado en el estado ya que el time_historical_cutoff
//bool//historical_only == Indica sólo debemos volver ofertas que no sean activos.
//uint32//time_historical_cutoff == Cuando se establece active_only, ofrece actualizado ya que también se devolverá este momento
			}
			
			header("Location: Index.php");
			
		}
	}
	if(isset($_GET['logout'])){
		unset($_SESSION['T2SteamAuth']);
		unset($_SESSION['T2SteamID64']);
		header("Location: Index.php");
	}
	
	
	
	if(isset($_SESSION['T2SteamAuth'])){
		
		$IDprofile = json_decode(file_get_contents("cache/{$_SESSION['T2SteamID64']}.json"));
		foreach ($IDprofile->response->players as $player){
			$Alias = $player->personaname;
			$MiniAvatar = $player->avatarmedium;
		}
		$login = "<div id='logout'><img class='alineado' src='$MiniAvatar'/><a href=''>$Alias</a> | <a href='?logout'>Cerrar sesion</a></div>";	
		
		/*
		$IDarma = json_decode(file_get_contents("cache/{$_SESSION['T2SteamID64']}.json"));
		echo $IDarma->response->players[0]->personaname. "<br>";
		
		$IMGarma = json_decode(file_get_contents("cache2/{$_SESSION['T2SteamID64']}.json"));
		echo $IMGarma->response->players[0]->personaname. "<br>";
		
		echo "<img src=\"{$steam->response->players[0]->profileurl}\"inventory/json/753/1/>";
		*/
		/*
		function cuerpo(){
			
			echo  "<center>JSON PROFILE</center>";
			$IDprofile = json_decode(file_get_contents("cache/{$_SESSION['T2SteamID64']}.json"));
			foreach ($IDprofile->response->players as $player){
				echo "
					<br/><span style='color:gray;'>Player ID: </span>$player->steamid
					<br/><span style='color:gray;'>Player Name: </span>$player->personaname
					<br/><span style='color:gray;'>Profile URL: </span>$player->profileurl
					<br/><u style='color:gray;'>SmallAvatar</u><br/> <img src='$player->avatar'/> 
					<br/><u style='color:gray;'>MediumAvatar</u><br/> <img src='$player->avatarmedium'/> 
					<br/><u style='color:gray;'>LargeAvatar</u><br/> <img src='$player->avatarfull'/> 
				";
				var_dump($player);
			}
			
			echo "<hr>";
			echo "<center>JSON INVENTORY</center>";
			$IDweapon = json_decode(file_get_contents("cache/inventory{$_SESSION['T2SteamID64']}.json"));
			foreach($IDweapon->rgInventory as $e){
				//var_dump($e);
				echo "
					</br><span style='color:gray;'>id: </span>$e->id 
					</br><span style='color:gray;'>classid: </span>$e->classid
					</br><span style='color:gray;'>instanceid: </span>$e->instanceid
				"; 
			}

			echo "</br><u style='color:gray;'>icon_url</u><br>";
			foreach($IDweapon->rgDescriptions as $e){
				echo "</br><img src=http://steamcommunity-a.akamaihd.net/economy/image/$e->icon_url><br>"; 
				var_dump($e);
			}
			
		}
		*/
	}
	
echo "<header>" . $login . "</header>";
//echo "<main>" . cuerpo() . "</main>";

?>