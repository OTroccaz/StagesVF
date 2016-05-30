<?
header('Content-type: text/html; charset=UTF-8');
$urlsauv = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$urljava = "https://VegFrance.univ-rennes1.fr/GS_login.php?troli=ok";
$urlinit0 = "https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
$urlinit = "https://vegfrance.univ-rennes1.fr/carte.php";
?><script type="text/javascript">var pageinit = '<?echo($urlinit0);?>';//alert(pageinit);</script><?

//Cas annulation quand login
if (isset($_GET["useretour"]) && $_GET["useretour"] == "Annuler") {
	$troli = $_GET["troli"];
	$lon = $_GET["lon"];
	$lat = $_GET["lat"];
	$zoom = $_GET["zoom"];
	$page = $_GET["page"];
	$actionstop = '?troli='.$troli.'&lon='.$lon.'&lat='.$lat.'&zoom='.$zoom.'&page='.$page;
	header("Location: ".$page.$actionstop);
}

if (!isset($_POST["action"]) && !isset($_GET['troli'])) {
	$_SESSION = array();
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
		$params["path"], $params["domain"],
		$params["secure"], $params["httponly"]
	);
}
session_destroy();
header('Location:'.$urlsauv.'?troli=ok');
}
session_start();
if (isset($_SESSION["regime"])) {$regime = $_SESSION["regime"];}
if (isset($_SESSION["milieu"])) {$milieu = $_SESSION["milieu"];}
if (isset($_POST["qui"])) {$qui = $_POST["qui"];}
include("../../config/_connexionpgVF.php");
?>
<html>
<head>
	<?
	if (isset($_POST["largeur"])) {
		$_SESSION["largeur"] = $_POST["largeur"];
		$largeur = $_POST["largeur"];
		$hauteur = round((500/800)*$largeur);
	}else{
		if (isset($_SESSION["largeur"])) {
			$largeur = $_SESSION["largeur"];
			$hauteur = round((500/800)*$largeur);
		}else{
			$largeur = 800;
			$hauteur = 512;
		}
	}

	if ($largeur <= 600) {?><script type="text/javascript">self.moveTo(0,0);self.resizeTo(700,750);</script><?}
	if (($largeur > 600) && ($largeur <= 800)) {?><script type="text/javascript">self.moveTo(0,0);self.resizeTo(900,850);</script><?}
	if (($largeur > 800) && ($largeur <= 1000)) {?><script type="text/javascript">self.moveTo(0,0);self.resizeTo(1100,950);</script><?}
	if (($largeur > 1000) && ($largeur <= 1200)) {?><script type="text/javascript">self.moveTo(0,0);self.resizeTo(1300,1000);</script><?}
	if ($largeur > 1200) {?><script type="text/javascript">self.moveTo(0,0);self.resizeTo(screen.availWidth,screen.availHeight);</script><?}

	//Identification ok > accès libre aux couches détaillées
	if ((isset($_POST["identification"]) && $_POST["identification"] == "ok") || $_SESSION["identification"] == "ok") {
		$_SESSION["identification"] = "ok";
		?><script type="text/javascript">var identification = 'ok';</script><?
	}else{
		?><script type="text/javascript">var identification = '';</script><?
	}

	if ((isset($_POST["action"]) && $_POST["action"] == "Ok")) {
		$_SESSION["action"] = "ok";
		//Création des tables
		$query = '
		DROP TABLE IF EXISTS troli;
		CREATE TABLE troli(
		id serial,
		plot_id varchar(11),
		modif_par varchar(100),
		the_geom geometry,
		regime varchar(1),
		PRIMARY KEY(id)
		)';
		pg_query($link, $query);

		$query = '
		DROP TABLE IF EXISTS troli3;
		CREATE TABLE troli3(
		id serial,
		plot_id varchar(11),
		modif_par varchar(100),
		scname varchar(200),
		the_geom geometry,
		milieu varchar(100),
		pratique varchar(100),
		regime varchar(1),
		PRIMARY KEY(id)
		)';
		pg_query($link, $query);

		//Construction de la requête
		$where = ' WHERE id IS NOT NULL';

		if (isset($_POST["regime"])) {
			$_SESSION["regime"] = $_POST["regime"];
			$regime = $_POST["regime"];
			switch($regime) {
				case "les2":
				break;
				case "sans":
				$where .= ' AND regime LIKE \'1\'';
				break;
				case "selon":
				$where .= ' AND regime LIKE \'2\'';
				break;
			}
		}else{
			if (isset($_SESSION["regime"])) {
				$regime = $_SESSION["regime"];
			}else{
				$regime = "les2";
			}
		}

		if (isset($_POST["milieu"])) {
			$_SESSION["milieu"] = $_POST["milieu"];
			$milieu = $_POST["milieu"];
			switch($milieu) {
				case "tous":
				break;
				case "aqua":
				$where .= ' AND milieu LIKE \'%quatique\'';
				break;
				case "bois":
				$where .= ' AND milieu LIKE \'%oisement\'';
				break;
				case "cana":
				$where .= ' AND milieu LIKE \'%anal\'';
				break;
				case "chem":
				$where .= ' AND milieu LIKE \'%hemin\'';
				break;
				case "haie":
				$where .= ' AND milieu LIKE \'%aie\'';
				break;
				case "mare":
				$where .= ' AND milieu LIKE \'%are\'';
				break;
				case "peup":
				$where .= ' AND milieu LIKE \'%eupleraie\'';
				break;
				case "prai":
				$where .= ' AND milieu LIKE \'%rairie\'';
				break;
				case "ripi":
				$where .= ' AND milieu LIKE \'%ipisylve\'';
				break;
				case "rose":
				$where .= ' AND milieu LIKE \'%oseli%\'';
				break;
			}
		}else{
			if (isset($_SESSION["milieu"])) {
				$milieu = $_SESSION["milieu"];
			}else{
				$milieu = "tous";
			}
		}

		$query = 'SELECT * FROM troli2'.$where;
		$result = pg_query($link, $query);
		if (!$result) {
			echo "Une erreur s'est produite pour la première requête sur troli2.\n";
			exit;
		}
		$i=1;
		while ($row = pg_fetch_row($result)) {
			$query = "INSERT INTO troli3(id, plot_id, modif_par, scname, the_geom, milieu, pratique, regime)
			VALUES('$row[0]','$row[1]', '$row[2]', '$row[3]', '$row[4]', '$row[5]', '$row[6]', '$row[7]')";
			pg_query($link, $query);
			$i++;
		}
		$nbres = $i-1;

		$query = 'SELECT DISTINCT plot_id, modif_par, the_geom, regime FROM troli2'.$where;
		$result = pg_query($link, $query);
		if (!$result) {
			echo "Une erreur s'est produite pour la deuxième requête sur troli2.\n";
			exit;
		}
		$i=1;
		while ($row = pg_fetch_row($result)) {
			$query = "INSERT INTO troli(plot_id, modif_par, the_geom, regime)
			VALUES('$row[0]', '$row[1]', '$row[2]', '$row[3]')";
			pg_query($link, $query);
			$i++;
		}
		$_SESSION["pg"] = "ok";
		$query = 'SELECT DISTINCT plot_id FROM troli3';
		$result = pg_query($link, $query);
		$nbreleve = pg_num_rows($result);
		if (isset($_POST["regime"]) && $_POST["milieu"] == "tous" && ($_POST["regime"] == "selon" || $_POST["regime"] == "les2")) {$nbreleve -= 1;}
	}else{
		$query = 'SELECT DISTINCT plot_id FROM troli2';
		$result = pg_query($link, $query);
		$nbreleve = pg_num_rows($result)-1;
	}
	if ($_SESSION["identification"] == "ok") {
		if ($_SESSION["action"] == "ok") {
			$query = 'SELECT DISTINCT plot_id FROM troli3';
			$result = pg_query($link, $query);
			$nbreleve = pg_num_rows($result);
			if (isset($_SESSION["regime"]) && $_SESSION["milieu"] == "tous" && ($_SESSION["regime"] == "selon" || $_SESSION["regime"] == "les2")) {$nbreleve -= 1;}
		}else{
			$query = 'SELECT DISTINCT plot_id FROM troli2';
			$result = pg_query($link, $query);
			$nbreleve = pg_num_rows($result);
			$nbreleve -= 1;
		}
	}
	?>
	<style type="text/css">
	body {
		font-family: corbel;
	}
	div#viewerContainer {
		width: <?=$largeur;?>px;
		height: <?=$hauteur;?>px;
		border: 1px solid black;
	}
	a {
		text-decoration: none;
		color: #99CC33;
		font-weight: bold;
		font-size: 0.8em;
	}

	#chicken{text-align:center;}
	#chicken_contentDiv, #popup_content{min-width:120px;}
	#chicken_contentDiv{overflow:scroll;overflow: hidden;}
	#chicken_FrameDecorationDiv_0, #chicken_FrameDecorationDiv_2{min-width:120px;}
	div#popup_content {
		font-size: 0.9em;
	}
	div#popup_head {
		color: #597950;
	}
	#boiteInfo {
		width:200px;
		padding:4px;
		font:12px corbel;
		left:50%;
		top:50%;
		margin-top: -12px;
		margin-left: -100px;
		display:none;
		text-align:center;
		border:#d0d0d0 2px solid;
		background:#fff;
		vertical-align:middle;
	}
	div#profil {
		position:absolute;
		left:100px;
		top:600px;
		background-color: #FFFFFF;
		text-align:center;
		color:#5E5E5E;
		width:460px;
		height: 240px;
		padding-top: 10px;
		padding-bottom: 15px;
		padding-right: 20px;
		padding-left: 20px;
		border: 1px solid #000000;
		font-family: Arial, Helvetica, sans-serif;
	}
	div#coords_title {
		height: 25px;
		border-bottom: 1px dotted #5E5E5E;
		font-weight:bold;
		font-size: 0.9em;
		margin-bottom: 20px;
	}
	div#coords {
		height:195px;
	}
	a[title="JavaScript charts"] {
		display: none !important;
	}
	/* surcharge du theme */
	.olLayerDiv path {
		stroke-width: 5px;
		stroke: #F1960B;
		fill: #F1960B;
	}
	.olLayerDiv polyline {
		stroke-width: 5px;
		stroke: #F1960B;
	}
	.olLayerDiv polygon {
		stroke: #F1960B;
		fill: #F1960B;
	}

	</style>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<title>VegFrance - Approche cartographique</title>
	<script src="https://maps.google.com/maps?file=api&v=2&key=ABQIAAAA3Y3mcLkqgnOV-NLoHlrGGRQRzTS7dT7OBmxnnMbfW1lxM-aPexTxhoHt8j8wZrb6ziaFeBfCALPAiw"></script>
	<script type="text/javascript" src="https://ecobio-mapserver.univ-rennes1.fr/GS/js/geoportalMap_popups.js"></script>
	<script type="text/javascript"><!--//--><![CDATA[//><!--
		function isset(val) {
			if (typeof val !== 'undefined' && val != null) {
				return true;
			}
			return false;
		}

		function afficheMessage() {
			if (isset(document.getElementById('boiteInfo'))) {
				document.getElementById('boiteInfo').style.position = 'absolute';
				document.getElementById('boiteInfo').style.display = 'block';
			}
		}
		afficheMessage();

		/**
		* Function: displayChart()
		*  Fonction de mise en forme des données retournées par le service
		*  de profil altimétrique.
		*  Utilise les composants de Amchart
		*  cf. https://live.amcharts.com/
		*/
		/**
		* Function: displayProfile()
		*  Fonction de mise en forme des donnÃ©es retournÃ©es par le service
		*  de profil altimÃ©trique.
		*  Utilise les composants de Amchart
		*  cf. https://live.amcharts.com/
		*  @argument {Array} datas ...
		*  @returns  {undefined}
		*/
		function displayProfile(datas) {

			// affichage des elements html
			document.getElementById("profil").style.display = "block";

			chart = AmCharts.makeChart("coords",
			{
				"type": "serial",
				"pathToImages": "https://cdn.amcharts.com/lib/3/images/",
				"autoMarginOffset": 0,
				"marginRight": 10,
				"marginTop": 10,
				"startDuration": 0,
				"color": "#5E5E5E",
				"fontSize": 10,
				"theme": "light",
				"thousandsSeparator": "",
				"categoryAxis": {
					"gridPosition": "start",
					"color": "#FFFFFF",
					"startOnAxis": true,
					"tickPosition": "start",
					"ignoreAxisWidth": true
				},
				"chartCursor": {
					"animationDuration": 0,
					"bulletsEnabled":true,
					"bulletSize": 10,
					"categoryBalloonEnabled": false,
					"cursorColor": "#FF0000",
					"zoomable": false
				},
				"trendLines": [],
				"graphs": [
					{
						"balloonColor": "#CCCCCC",
						"balloonText": "<span class='altiPathValue'>[[title]] : [[value]]m</span><br/><span class='altiPathCoords'>(lat: [[lat]] / lon:[[lon]])</span>",
						"bullet": "round",
						"bulletBorderAlpha": 1,
						"bulletBorderColor": "#FFFFFF",
						"bulletBorderThickness": 1,
						"bulletColor": "#CC3300",
						"bulletSize": 4,
						"hidden": false,
						"id": "AmGraph-1",
						"fillAlphas": 0.25,
						"fillColors": "#F1960B",
						"lineAlpha": 1,
						"lineColor":"#F1960B",
						"lineThickness": 2,
						"title": "Altitude",
						"type": "smoothedLine",
						"valueField": "z"
					}
				],
				"guides": [],
				"valueAxes": [
					{
						"id": "ValueAxis-1",
						"minimum": 0,
						"minVerticalGap": 20,
						"title": "Altitude"
					}
				],
				"allLabels": [],
				"balloon": {
					"borderColor": "#CCCCCC",
					"borderThickness": 1,
					"fillColor": "#FFFFFF",
					"showBullet": true
				},
				"titles": [],
				"dataProvider": datas
			}
		);
	};

	/**
	* Function: eventOnControlProfile()
	*  Gestion des evenements sur le graphique amCharts
	*  ...
	*  Utilise les composants de Amchart
	*  cf. https://live.amcharts.com/
	*  cf. https://docs.amcharts.com/3/javascriptcharts/AmCoordinateChart
	* @returns {undefined}
	*/
	function eventOnControlProfile() {

		chart.addListener("changed", function(event) {

			var obj = event.chart.dataProvider[event.index];
			var lon = obj.lon;
			var lat = obj.lat;

			var pointGeometry = new OpenLayers.Geometry.Point (lon,lat);
			pointGeometry.transform(OpenLayers.Projection.CRS84, iViewer.getViewer().getMap().getProjection());
			var pointFeature  = new OpenLayers.Feature.Vector(
				pointGeometry,
				{
					name: "name",
					description:"description"
				},
				{
					fillColor : '#F1960B',
					fillOpacity : 1,
					strokeColor : "#F1960B",
					strokeOpacity : 0.5,
					strokeWidth : 15,
					pointRadius : 5
				});

				posLayer.removeAllFeatures();
				posLayer.addFeatures([pointFeature]);

			});
		};

		/**
		* Function: eventOnDivProfile()
		*  Gestion des evenements sur l'emplacement du curseur
		*  sur le profil graphique.
		*  ...
		* @returns {undefined}
		*/
		function eventOnDivProfile() {

			function isOutside(evt, parent) {

				// portabilité ...
				var elem = evt.relatedTarget || evt.toElement || evt.fromElement;

				while ( elem && elem !== parent) {
					elem = elem.parentNode;
				}

				if ( elem !== parent) {
					return true;
				}
			}

			// div du profil graphique...
			var coords = document.getElementById('coords');

			// event sur la div du profil graphique
			coords.onmouseover = coords.onmouseout = function(e) {
				e = e || event;

				var visibility = true;
				(isOutside(e, this)) ? visibility = false : visibility = true;
				iViewer.setLayerVisibility('posLayer', visibility);
				eventOnControlProfile();
			}
		};

		/**
		* Property: iViewer
		* {<Geoportal.InterfaceViewer>} The Geoportal API viewer interface.
		*/
		iViewer= null;
		/**
		* Function: init
		* Load the map. Called when "onload" event is fired.
		*/
		function init() {
			//console.log("window.onload() debut");
			iViewer= Geoportal.load(
				// map's div id - identifiant de la div de la carte :
				"viewerContainer",
				// API's keys - clefs API :
				["hm86wlfk7g3fclxfa6h66iuc"],
				{
					// center in WGS84G - centre en WGS84G
					lon:2.555274398,
					lat:46.630832557999984
				},
				// zoom level (0-20) - niveau de zooms (0 à 20) :
				5,
				{   // various options :
					// viewer default controls options overloads :
					componentsOptions:{},
					//proxyUrl : {String} : The URL of the proxy to use
					proxyUrl : "https://ecobio-mapserver.univ-rennes1.fr/GS/proxy.php?url=",
					// Geoportal's layers to load (when none, all contracts' layers are loaded) :
					layers:["LANDCOVER.SYLVOECOREGIONS.ALLUVIUM:WMTS","ORTHOIMAGERY.ORTHOPHOTOS2000-2005:WMTS","LANDCOVER.SYLVOECOREGIONS:WMTS","ORTHOIMAGERY.ORTHOPHOTOS:WMTS","ADMINISTRATIVEUNITS.BOUNDARIES:WMTS","PROTECTEDAREAS.ZPS:WMTS","PROTECTEDAREAS.SIC:WMTS","GEOGRAPHICALNAMES.NAMES:WMTS","LANDCOVER.FORESTINVENTORY.V2:WMTS","PROTECTEDAREAS.RN:WMTS","GEOGRAPHICALGRIDSYSTEMS.PLANIGN:WMTS","ELEVATION.LEVEL0:WMTS","GEOGRAPHICALGRIDSYSTEMS.MAPS:WMTS","CADASTRALPARCELS.PARCELS:WMTS","PROTECTEDAREAS.RB:WMTS","PROTECTEDAREAS.PN:WMTS","PROTECTEDAREAS.ZNIEFF2:WMTS","PROTECTEDAREAS.PNM:WMTS","LANDCOVER.FORESTAREAS:WMTS","ELEVATION.SLOPES:WMTS","PROTECTEDAREAS.ZNIEFF1:WMTS","PROTECTEDAREAS.PNR:WMTS","PROTECTEDAREAS.RAMSAR:WMTS","BUILDINGS.BUILDINGS:WMTS","HYDROGRAPHY.HYDROGRAPHY:WMTS","PROTECTEDAREAS.RNC:WMTS","TRANSPORTNETWORKS.ROADS:WMTS","PROTECTEDAREAS.BIOS:WMTS","LANDCOVER.FORESTINVENTORY.V1:WMTS","PROTECTEDAREAS.RNCF:WMTS"],
					// Geoportal's layers options :
					layersOptions:{
						"LANDCOVER.SYLVOECOREGIONS.ALLUVIUM:WMTS":{"opacity":1,"visibility":false},
						"ORTHOIMAGERY.ORTHOPHOTOS2000-2005:WMTS":{"opacity":1,"visibility":false},
						"LANDCOVER.SYLVOECOREGIONS:WMTS":{"opacity":1,"visibility":false},
						"ORTHOIMAGERY.ORTHOPHOTOS:WMTS":{"opacity":1,"visibility":true},
						"ADMINISTRATIVEUNITS.BOUNDARIES:WMTS":{"opacity":1,"visibility":false},
						"PROTECTEDAREAS.ZPS:WMTS":{"opacity":1,"visibility":false},
						"PROTECTEDAREAS.SIC:WMTS":{"opacity":1,"visibility":false},
						"GEOGRAPHICALNAMES.NAMES:WMTS":{"opacity":1,"visibility":false},
						"LANDCOVER.FORESTINVENTORY.V2:WMTS":{"opacity":1,"visibility":false},
						"PROTECTEDAREAS.RN:WMTS":{"opacity":1,"visibility":false},
						"GEOGRAPHICALGRIDSYSTEMS.PLANIGN:WMTS":{"opacity":1,"visibility":false},
						"ELEVATION.LEVEL0:WMTS":{"opacity":1,"visibility":false},
						"GEOGRAPHICALGRIDSYSTEMS.MAPS:WMTS":{"opacity":0,"visibility":true},
						"CADASTRALPARCELS.PARCELS:WMTS":{"opacity":1,"visibility":false},
						"PROTECTEDAREAS.RB:WMTS":{"opacity":1,"visibility":false},
						"PROTECTEDAREAS.PN:WMTS":{"opacity":1,"visibility":false},
						"PROTECTEDAREAS.ZNIEFF2:WMTS":{"opacity":1,"visibility":false},
						"PROTECTEDAREAS.PNM:WMTS":{"opacity":1,"visibility":false},
						"LANDCOVER.FORESTAREAS:WMTS":{"opacity":1,"visibility":false},
						"ELEVATION.SLOPES:WMTS":{"opacity":1,"visibility":false},
						"PROTECTEDAREAS.ZNIEFF1:WMTS":{"opacity":1,"visibility":false},
						"PROTECTEDAREAS.PNR:WMTS":{"opacity":1,"visibility":false},
						"PROTECTEDAREAS.RAMSAR:WMTS":{"opacity":1,"visibility":false},
						"BUILDINGS.BUILDINGS:WMTS":{"opacity":1,"visibility":false},
						"HYDROGRAPHY.HYDROGRAPHY:WMTS":{"opacity":1,"visibility":false},
						"PROTECTEDAREAS.RNC:WMTS":{"opacity":1,"visibility":false},
						"TRANSPORTNETWORKS.ROADS:WMTS":{"opacity":1,"visibility":false},
						"PROTECTEDAREAS.BIOS:WMTS":{"opacity":1,"visibility":false},
						"LANDCOVER.FORESTINVENTORY.V1:WMTS":{"opacity":1,"visibility":false},
						"PROTECTEDAREAS.RNCF:WMTS":{"opacity":1,"visibility":false}},
						// External or user's layers :
						overlays:{
							"WMS":[
								{
									"name":"Corela WMS",
									"url":"http://www.osuris.org/geoserver/wms?",
									"options":{
										"params":{
											"layers":"Ecobio:corela",
											//"opacity":1,
											//"singleTile":false,
											"format":"image/png",
											"transparent":true,
											"visibility":true,
											//"hover":true
										},
										"options":{
											"visibility":false,
										},
									}
								}
							]
						},
						// callback to use before returning (after centering) :
						onView:function() {
							viewer=iViewer.getViewer();
							ajoutwfs();

							var rwbodyStyle= new OpenLayers.StyleMap({
								"default": new OpenLayers.Style({
									strokeColor:'#0000ff',
									strokeWidth:3
								}),
								"select": new OpenLayers.Style({
									strokeColor:'#3399ff',
									strokeWidth:3
								})
							});

							//Récupération de la barre d'outils
							var tbx= viewer.getMap().getControlsByClass("Geoportal.Control.ToolBox")[0];
							var panel= new Geoportal.Control.Panel({
								div:OpenLayers.Util.getElement(tbx.id+"_search")
							});

							viewer.getMap().addControls([panel]);

							//Ajout du contrôle d'impression
							var nv= viewer.getMap().getControlsByClass("Geoportal.Control.NavToolbar")[0];
							nv.addControls([new Geoportal.Control.PrintMap()]);

							//Ajout du contrôle des mesures et altimétrie
							var measurebar= new Geoportal.Control.MeasureToolbar({
								div: OpenLayers.Util.getElement(tbx.id+"_measure"),
								displaySystem:(viewer.getMap().getProjection().getProjName()=="longlat"?"geographic":"metric"),
								targetElement: OpenLayers.Util.getElement(tbx.id+"_meares"),
								// useful parameters to overload the component
								elevationOptions : {
									// active : true|false
									active: true,
									// targetElement: {DOMHtml}|null
									//    - si 'targetElement' est null, on affiche une liste de mesures dans un controle flottant
									//    - si 'targetElement' est commenté, on doit capturer les mesures afin des les afficher dans un controle personnalisé
									//    - sinon affichage des resultats dans une div... : {DOMHtml}
									targetElement: null
								},
								elevationPathOptions : {
									// active : true|false
									active: true,
									//    - active/desactive l'affichage du controle
									//    - la gestion des droits sur le service (clef API) peut activer/desactiver le controle...
									// targetElement: {DOMHtml}|null
									//    - si 'targetElement' est null, on affiche une liste de mesures dans un controle flottant
									//    - si 'targetElement' est commenté, on doit capturer les mesures afin des les afficher dans un controle personnalisé
									//    - sinon affichage des resultats dans une div... : {DOMHtml}
									//targetElement: null,
									// sampling: 0 par defaut
									sampling : 50
								}
							});
							viewer.getMap().addControl(measurebar);

							// ajout du layer d'affichage de l'element profile
							posLayer = new OpenLayers.Layer.Vector("posLayer",
							{
								displayInLayerSwitcher:false,
								style : {
									visibility:true
								}
							});
							viewer.getMap().addLayer(posLayer);

							// gestion des evenements sur la div du profil
							eventOnDivProfile();

							// capture events
							var ctrlelevationpath = viewer.getMap().getControlsByClass('Geoportal.Control.Measure.ElevationPath')[0];
							ctrlelevationpath.events.on({"measure": displayProfile});

							//Ajout de la LayerToolbar
							var tOpts= {div: OpenLayers.Util.getElement(tbx.id+"_addlyr")};
							tOpts= OpenLayers.Util.extend(tOpts,{
								addVectorLayerOptions:{
									supportedClasses:[
										"OpenLayers.Format.KML",
										"Geoportal.Format.GPX",
										"OpenLayers.Format.OSM",
										"OpenLayers.Layer.GeoRSS",
										"OpenLayers.Layer.WFS"
									],
									styleMapTemplates:{
										"OpenLayers.Layer.GeoRSS":new OpenLayers.StyleMap(
											new OpenLayers.Style(
												OpenLayers.Util.applyDefaults({
													"graphic":true,
													"externalGraphic":Geoportal.Util.getImagesLocation()+"xy-target.gif",
													"graphicOpacity":1.0,
													"graphicWidth":25,
													"graphicHeight":25,
													"graphicXOffset":-12.5,
													"graphicYOffset":-12.5
												},OpenLayers.Feature.Vector.style["default"])
											)
										)
									},
									layerVectorOptions:{
										global:{
										}
									}
								},
								addImageLayerOptions:{
									layerImageOptions:{
										singleTile:false
									}
								}

							});
							var LayerToolbar= new Geoportal.Control.LayerToolbar(tOpts);
							viewer.getMap().addControl(LayerToolbar);

							//Accès au GéoCatalogue
							var CSW= new Geoportal.Control.CSW(
								OpenLayers.Util.extend(
									{
										title:"gpControlCSW.title"
									},
									{
										cswUrl:"http://www.geocatalogue.fr/api-public/servicesRest?"
									}
								))
								panel.addControls([CSW]);

								//Ajout du contrôle de recherche par noms de lieux
								var GeoNames= new Geoportal.Control.LocationUtilityService.GeoNames(
									new Geoportal.Layer.OpenLS.Core.LocationUtilityService(
										"PositionOfInterest:OPENLS;Geocode",//layer name
										{
											maximumResponses:100,
											formatOptions: {
											}
										}
									),{
										drawLocation:true,
										setZoom: Geoportal.Control.LocationUtilityService.GeoNames.setZoomForBDNyme,
										// turn filters on
										//filtersOptions: {},
										// turn filters off
										filtersOptions: null,
										//autoCompleteOptions: null,
										// turn auto-complete on => no filters
										autoCompleteOptions: {}
									}
								);
								panel.addControls([GeoNames]);

								//Ajout du contrôle de recherche par adresses
								var Geocode= new Geoportal.Control.LocationUtilityService.Geocode(
									new Geoportal.Layer.OpenLS.Core.LocationUtilityService(
										"StreetAddress:OPENLS;Geocode",//layer name
										{
											maximumResponses:100,
											formatOptions: {
											}
										}
									),{
										drawLocation:true,
										setZoom: Geoportal.Control.LocationUtilityService.GeoNames.setZoomForBDNyme,
										//filtersOptions: {},
										// turn filters off
										filtersOptions: null,
										autoCompleteOptions: null,
										// turn auto-complete on => no filters
										//autoCompleteOptions: {}
									}
								);
								panel.addControls([Geocode]);

								//Ajout du contrôle de recherche par parcelle cadastrale
								var cadastre= new Geoportal.Control.LocationUtilityService.CadastralParcel(
									new Geoportal.Layer.OpenLS.Core.LocationUtilityService(
										'CadastralParcel:OPENLS;Geocode',//layer name
										{
											maximumResponses:100,
											formatOptions: {
											},
										}
									), {
										// force drawLocation
										drawLocation:true,
										// tooltip
										uiOptions:{title: 'gpControlLocationUtilityService.cadastralparcel.title'},
										// turn filters on
										//filtersOptions: {},
										// turn filters off
										filtersOptions: null,
										// turn auto-completion off => can use filters !
										autoCompleteOptions: null
										// turn auto-complete on => no filters
										//autoCompleteOptions: {}
									});
									panel.addControls([cadastre]);

								},
								// class of viewer to use :
								language:"fr",
								//proxyUrl:config.proxy,
								//geormUrl:config.serverUrl,
								viewerClass:"Geoportal.Viewer.Default",
							}
						);
						//console.log("window.onload() fin");
					}

					var viewer = null;
					var map = null;
					var wfsLayer = null;

					function ajoutwfs() {
						//console.log("ajoutwfs() début");
						var scnameStyle = new OpenLayers.Style({
							label:"${nombre}",
							fontColor: "#FFFFFF",
							fontOpacity: 0.9,
							fontFamily: "Corbel",
							fontSize: 13,
							fontWeight: "bold",
							pointRadius: "10",
							strokeColor: "999966",
							strokeWidth: 1,
							strokeOpacity: 0.9,
							fillColor: "#FF8000",
							fillOpacity: 0.7
						}, {
							context: {
								nombre: function(feature) {
									return feature.attributes.count;
								}
							}
						});
						var clusterStyle = new OpenLayers.Style({
							label:"${nombre}",
							fontColor: "#333333",
							fontOpacity: 0.9,
							fontFamily: "Corbel",
							fontSize: 13,
							fontWeight: "bold",
							pointRadius: "${radius}",
							strokeColor: "999966",
							strokeWidth: 1,
							strokeOpacity: 0.9,
							fillOpacity: 0.7,
							fillColor: "#CCFF33"
						}, {
							context: {
								nombre: function(feature) {
									if(feature.attributes.count>=2)
									return feature.attributes.count;
									else
									return "1";
								},
								radius: function(feature) {
									return Math.max((feature.attributes.count*60/1949)+9.97, 10);
									//return feature.attributes.count/50;
								}
							}
						});
						var clusterCat = new OpenLayers.Strategy.Cluster();
						clusterCat.shouldCluster = function(cluster,feature)
						{
							if(cluster.cluster[0].attributes.type != feature.attributes.type)
							{
								return false;
							}
							var cc = cluster.geometry.getBounds().getCenterLonLat();
							var fc = feature.geometry.getBounds().getCenterLonLat();
							var distance = (Math.sqrt(Math.pow((cc.lon - fc.lon), 2) + Math.pow((cc.lat - fc.lat), 2)) / this.resolution);
							return (distance <= 25);
						}
						map = viewer.getMap();
						wfsUrl = "https://ecobio-mapserver.univ-rennes1.fr/geoserver/wfs?";

						// Add WFS layer détails
						wfsde = new OpenLayers.Layer.Vector("VF détails", {
							projection: 'EPSG:4326',
							strategies: [
								new OpenLayers.Strategy.BBOX(),
								new OpenLayers.Strategy.Cluster({distance:1}),
							],
							projection: new OpenLayers.Projection("EPSG:4326"),
							extractAttributes: true,
							protocol: new OpenLayers.Protocol.HTTP({
								url: wfsUrl,
								params: {
									request: 'GetFeature',
									service: 'WFS',
									version: '1.0.0',
									typeName: <?if (isset($_SESSION["pg"])) {echo("'troli3'");}else{echo("'troli2'");}?>,
									srsName: 'EPSG:4326'
								},
								format: new OpenLayers.Format.GML({
									featureNS: 'http://www.opengeospatial.net/cite',
									geometryName: 'the_geom'
								})
							}),
							styleMap: new OpenLayers.StyleMap({
								default: scnameStyle,
								select: {fillColor: '#8aeeef'}
							}),
							visibility: true,
							displayInLayerSwitcher:true,
							minZoomLevel:15,
							maxZoomLevel:21,
							originators: [{
								logo: 'VegFrance',
								pictureUrl: 'https://ecobio-mapserver.univ-rennes1.fr/GS/logo_VF.jpg',
								url: 'https://vegfrance.univ-rennes1.fr/',
								//"attribution":"Circuit distribué par TouriLoire"}],
								visibility:true
							}]
						});
						viewer.getMap().setProxyUrl('proxy.php?url=');
						viewer.getMap().addLayer(wfsde);

						function handleZoom(event) {
							var map = event.object;
							if (map.getZoom() >= 14) {
								var zoom = map.getZoom();
								//var extent = "'"+map.getExtent().transform(map.projection, map.displayProjection)+"'";
								//var diffext = extent.split(',');
								//var extent1 = diffext[0].replace("'", "");
								var center = "'"+map.getCenter()+"'";
								center = center.replace(",", "&");
								var re = new RegExp("'", "g");
								center = center.replace(re, "");
								var page = document.location.href;
								page = page.replace("?troli=ok", "");
								page = page.substring(page.lastIndexOf("/")+1);
								//var url = 'validation.php&trolisess=<?echo($trolisess);?>&'+center+'&zoom='+zoom+'&page='+page;
								//var url = 'validation.php&'+center+'&zoom='+zoom+'&page='+page;
								var url = '<?echo($urljava);?>&'+center+'&zoom='+zoom+'&page='+page;
								if (identification != 'ok') {
									self.location = url;
								}
								//alert('toto : '+url);
							}
						}
						viewer.getMap().events.register('zoomend', wfsde, handleZoom);

						//popup options
						var j = 0;
						var contselval = new Array();
						var contexpval = new Array();
						var selectedFeature, selectControl;

						popup_wfsde = new OpenLayers.Control.SelectFeature(wfsde, {
							hover:false,
							multiple:true,
							toggle:true,
							box:false,
							clickout:true,
							onSelect: function openPopup(f){
								var nbrelsel = wfsde.selectedFeatures.length-1;
								//console.debug(wfsde.selectedFeatures[nbrelsel]["id"]);
								viewer.getMap().panTo(f.geometry.getBounds().getCenterLonLat());
								//f.popup= new OpenLayers.Popup.AnchoredBubble(
								f.popup= new OpenLayers.Popup.FramedCloud(
									"chicken",//identifiant
									f.geometry.getBounds().getCenterLonLat(),
									null, // taille nulle pour qu'elle s'adapte automatiquement
									'<div id="popup_content"><div id="popup_head">'+
									'<b>N° relevé : '+f.cluster[0].attributes.plot_id+'<\/b><\/div>'+
									'<div id="popup_body">Modifié par : '+f.cluster[0].attributes.modif_par+
									'<br>Milieu : '+f.cluster[0].attributes.milieu+
									'<br>Pratique : '+f.cluster[0].attributes.pratique+
									'<br>Régime : '+f.cluster[0].attributes.regime+
									'<\/div>',  //message dans la popup
									null,
									false, // Booléen pour activer la closeBox
									//onPopupClose() //fonction appelée lors du click de la close box
									closeBoxCallback = function(){ // fonction appelée à l'appui de la closeBox
									viewer.getMap().removePopup(f.popup);
								}
							);
							viewer.getMap().addPopup(f.popup);
							//f.popup.closeOnMove = true;
							//console.debug(f.popup);
							//console.debug(f.attributes.count);
							var contsel = '<center><table style="border:1px solid white;"><tr><td align="center"><b>Id</b></td>'+
							'<td align="center"><b>N° relevé</b></td><td align="center"><b>Modifié par<b></td><td align="center"><b>Espèces<b></td>'+
							'<td align="center"><b>Milieu</b></td><td align="center"><b>Pratique<b></td><td align="center"><b>Régime<b></td>'+
							'</tr>';
							var contexp = 'Id;N° relevé;Modifié par;Espèces;Milieu;Pratique;Régime\n';
							var contref = '';
							for (i = 0; i < f.attributes.count; i++) {
								contselval[j] = '<tr><td>'+Number(i+1)+'</td><td>'+f.cluster[i].attributes.plot_id+'</td>'+
								'<td>'+f.cluster[i].attributes.modif_par+'</td><td><i>'+f.cluster[i].attributes.scname+'</i></td>'+
								'<td>'+f.cluster[i].attributes.milieu+'</td><td>'+f.cluster[i].attributes.pratique+'</td>'+
								'<td>'+f.cluster[i].attributes.regime+'</td>'+
								'</tr>';
								contexpval[j] = Number(i+1)+';'+f.cluster[i].attributes.plot_id+';'+
								f.cluster[i].attributes.modif_par+';'+f.cluster[i].attributes.scname+';'+
								f.cluster[i].attributes.milieu+';'+f.cluster[i].attributes.pratique+';'+
								f.cluster[i].attributes.regime+'\n';
								contref += f.cluster[i].attributes.modif_par+',';
								j += 1;
							}
							//Fichier des statistiques
							$.post("GS_stats.php", {
								qui: <?if (isset($qui)) {echo('"'.$qui.'"');}else{echo('"inconnu"');}?>,
								numreleve: f.cluster[0].attributes.plot_id,
								modifpar: f.cluster[0].attributes.modif_par,
								milieu: f.cluster[0].attributes.milieu,
								pratique: f.cluster[0].attributes.pratique,
								regime: f.cluster[0].attributes.regime
							});
							for (k = 0; k < contselval.length; k++) {
								if (contselval[k]) {contsel += contselval[k];}
								if (contexpval[k]) {contexp += contexpval[k];}
							}
							contsel += '</table>';
							contsel += '<br><br><a target="_blank" href="https://vegfrance.univ-rennes1.fr/GS_ref_VF.txt">Exporter la référence sous forme de fichier texte</a>';
							contsel += ' - <a href="https://vegfrance.univ-rennes1.fr/GS_export.csv">Exporter sous forme de tableau CSV <i>(référentiel taxonomique : TaxRef 5.0)</i></a><br><br></center>';
							//Fichier export
							$.post("GS_export.php", {
								chaine: contexp
							});
							$.post("GS_ref_VF.php", {
								chaine: contref
							});
							document.getElementById("select").innerHTML = contsel;
						},
						onUnselect : function closePopup(f) {
							var nbrelsel = wfsde.selectedFeatures.length;
							//console.debug(wfsde.selectedFeatures[nbrelsel]["id"]);
							//console.debug(wfsde.features[4].id);
							//console.debug(f.id);
							Geoportal.Control.unselectFeature(f);
							//document.getElementById("select").innerHTML = '';
							contselval.splice(0,k);
							j = 0;
							//console.debug(nbrelsel);
							if (nbrelsel != 0) {
								var contsel = '<center><table style="border:1px solid white;"><tr><td align="center"><b>Id</b></td>'+
								'<td align="center"><b>N° relevé</b></td><td align="center"><b>Modifié par<b></td><td align="center"><b>Espèces<b></td>'+
								'<td align="center"><b>Milieu</b></td><td align="center"><b>Pratique<b></td><td align="center"><b>Régime<b></td>'+
								'</tr>';
								var contexp = 'Id;N° relevé;Modifié par;Espèces;Milieu;Pratique;Régime\n';
								var contref = '';
								//alert(wfsde.selectedFeatures[0].id);
								for (var ii = 0; ii < nbrelsel; ii++) {
									f = wfsde.selectedFeatures[ii];
									for (var i = 0; i < f.attributes.count; i++) {
										contselval[j] = '<tr><td>'+Number(i+1)+'</td><td>'+f.cluster[i].attributes.plot_id+'</td>'+
										'<td>'+f.cluster[i].attributes.modif_par+'</td><td><i>'+f.cluster[i].attributes.scname+'</i></td>'+
										'<td>'+f.cluster[i].attributes.milieu+'</td><td>'+f.cluster[i].attributes.pratique+'</td>'+
										'<td>'+f.cluster[i].attributes.regime+'</td>'+
										'</tr>';
										contexpval[j] = Number(i+1)+';'+f.cluster[i].attributes.plot_id+';'+
										f.cluster[i].attributes.modif_par+';'+f.cluster[i].attributes.scname+';'+
										f.cluster[i].attributes.milieu+';'+f.cluster[i].attributes.pratique+';'+
										f.cluster[i].attributes.regime+'\n';
										contref += f.cluster[i].attributes.modif_par+',';
										j += 1;
									}
								}
								for (k = 0; k < contselval.length; k++) {
									if (contselval[k]) {contsel += contselval[k];}
									if (contexpval[k]) {contexp += contexpval[k];}
								}
								contsel += '</table>';
								contsel += '<br><br><a target="_blank" href="https://vegfrance.univ-rennes1.fr/GS_ref_VF.txt">Exporter la référence sous forme de fichier texte</a>';
								contsel += ' - <a href="https://vegfrance.univ-rennes1.fr/GS_export.csv">Exporter sous forme de tableau CSV <i>(référentiel taxonomique : TaxRef 5.0)</i></a><br><br></center>';
							}else{
								contsel = "";
								contexp = "";
							}
							//Fichier export
							$.post("GS_export.php", {
								chaine: contexp
							});
							$.post("GS_ref_VF.php", {
								chaine: contref
							});
							document.getElementById("select").innerHTML = contsel;
						}
					});
					viewer.getMap().addControl(popup_wfsde);
					popup_wfsde.activate();

					// Add WFS layer points d'observation
					wfspo = new OpenLayers.Layer.Vector("VF observations", {
						typeNames: <?if (isset($_SESSION["pg"])) {echo('"troli"');}else{echo('"troli_init"');}?>,
						strategies: [
							new OpenLayers.Strategy.Fixed(),
							new OpenLayers.Strategy.Cluster(clusterCat)
						],
						projection: 'EPSG:4326',
						extractAttributes: true,
						protocol: new OpenLayers.Protocol.WFS({
							version: "2.0.0",
							url: wfsUrl,
							featureNS: "http://www.opengeospatial.net/cite",
							featureType: <?if (isset($_SESSION["pg"])) {echo('"troli"');}else{echo('"troli_init"');}?>,
							featurePrefix: "cite",
							geometryName: "the_geom",
						}),
						styleMap: new OpenLayers.StyleMap({
							default: clusterStyle,
							select: {fillColor: '#8aeeef'}
						}),
						visibility: true,
						displayInLayerSwitcher:true,
						//minZoomLevel:15,
						maxZoomLevel:14,
						hover:false,
						originators: [{
							logo: 'VegFrance',
							pictureUrl: 'https://ecobio-mapserver.univ-rennes1.fr/GS/logo_VF.jpg',
							url: 'https://vegfrance.univ-rennes1.fr/',
							//"attribution":"Circuit distribué par TouriLoire"}],
							visibility:true
						}],
					});
					viewer.getMap().addLayer(wfspo);
					//popup options
					var j = 0;
					var contselval = new Array();
					popup_wfspo = new OpenLayers.Control.SelectFeature(wfspo, {
						hover:false,
						multiple:true,
						toggle:true,
						box:false,
						clickout:true,
						onSelect: function openPopup(f){
							viewer.getMap().panTo(f.geometry.getBounds().getCenterLonLat());
							if(f.attributes.count <= 1) {// if not cluster
								//f.popup= new OpenLayers.Popup.AnchoredBubble(
								f.popup= new OpenLayers.Popup.FramedCloud(
									"chicken",//identifiant
									f.geometry.getBounds().getCenterLonLat(),
									null, // taille null pour qu'elle s'adapte automatiquement
									'<div id="popup_content"><div id="popup_head">'+
									'<b>N° relevé : '+f.cluster[0].attributes.plot_id+'<\/b><\/div>'+
									'<div id="popup_body">Modifié par : '+f.cluster[0].attributes.modif_par+'<\/div>',  //message dans la popup
									null,
									false // Booleen pour activer la closeBox
								);
								//alert(this.map.zoom);
								viewer.getMap().addPopup(f.popup);
							}else{
								Geoportal.Control.unselectFeature(f);
							}
							var contsel = '<center><table style="border:1px solid white;"><tr><td align="center"><b>Id</b></td><td align="center"><b>N° relevé</b></td><td align="center"><b>Modifié par<b></td></tr>';
							for (i = 0; i < f.attributes.count; i++) {
								contselval[j] = '<tr><td align="center">'+Number(i+1)+'</td><td align="center">'+f.cluster[i].attributes.plot_id+'</td><td align="center">'+f.cluster[i].attributes.modif_par+'</td></tr>';
								j += 1;
							}
							for (k = 0; k < contselval.length; k++) {
								if (contselval[k]) {contsel += contselval[k];}
							}
							contsel += '</table></center>';
							document.getElementById("select").innerHTML = contsel;
						},
						onUnselect : function closePopup(f) {
							var nbrelsel = wfspo.selectedFeatures.length;
							Geoportal.Control.unselectFeature(f);
							contselval.splice(0,k);
							j = 0;
							if (nbrelsel != 0) {
								var contsel = '<center><table style="border:1px solid white;"><tr><td align="center"><b>Id</b></td><td align="center"><b>N° relevé</b></td><td align="center"><b>Modifié par<b></td></tr>';
								for (var ii = 0; ii < nbrelsel; ii++) {
									f = wfspo.selectedFeatures[ii];
									for (var i = 0; i < f.attributes.count; i++) {
										contselval[j] = '<tr><td align="center">'+Number(i+1)+'</td><td align="center">'+f.cluster[i].attributes.plot_id+'</td><td align="center">'+f.cluster[i].attributes.modif_par+'</td></tr>';
										j += 1;
									}
								}
								for (k = 0; k < contselval.length; k++) {
									if (contselval[k]) {contsel += contselval[k];}
								}
								contsel += '</table></center>';
							}else{
								contsel = "";
							}
							document.getElementById("select").innerHTML = contsel;
						}
					});
					viewer.getMap().addControl(popup_wfspo);
					popup_wfspo.activate();

					function nettoyage() {//suppression du tableau et des popups lors d'un zoom
					//var map = event.object;
					//Geoportal.Control.unselectFeature();
					document.getElementById("select").innerHTML = "";
					//alert(viewer.getMap().popups.length);
					var contsel = "";
					var contexp = "";
					var k = contselval.length;
					contselval.splice(0,k);
					var k = contexpval.length;
					contexpval.splice(0,k);
					while(viewer.getMap().popups.length) {
						viewer.getMap().removePopup(viewer.getMap().popups[0]);
					}
					//console.log(wfsde.selectedFeatures.length);
					popup_wfsde.unselectAll();
					popup_wfspo.unselectAll();
				}
				viewer.getMap().events.register('zoomend', wfsde, nettoyage);

				<?
				if (isset($_GET["lon"]) && $_GET["lon"] != 0) {$lon = "lon=".$_GET["lon"];}
				if (isset($_GET["lat"]) && $_GET["lat"] != 0) {$lat = "lat=".$_GET["lat"];}
				if (isset($_GET["zoom"]) && $_GET["zoom"] != 0) {$zoom = $_GET["zoom"]+1;}
				//echo("alert(".$lon.");");
				if ($lon != '') {
					echo('viewer.getMap().setCenter(new OpenLayers.LonLat('.$lon.','.$lat.'),'.$zoom.')');
				}
				?>
			}
			window.onload= init;
			//--><!]]></script>
			<script type="text/javascript" charset="utf-8" src="https://api.ign.fr/geoportail/api/js/latest/GeoportalExtended.js"></script>
			<!-- amCharts javascript sources -->
			<script type="text/javascript" src="https://www.amcharts.com/lib/3/amcharts.js"></script>
			<script type="text/javascript" src="https://www.amcharts.com/lib/3/serial.js"></script>
			<script type="text/javascript" src="https://www.amcharts.com/lib/3/themes/patterns.js"></script>
			<script type="text/javascript" src="https://ecobio-mapserver.univ-rennes1.fr/GS/Openlayers/src/openlayers/lib/Openlayers/Format/WFSCapabilities/v2_0_0.js"></script>-->
			<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
		</head>
		<body onload="init()">
			<p align="center"><font size=5><b>VegFrance - Approche cartographique</b></font></p>
			<center><div id="viewerContainer"></div></center>
			<p align="center" style="font-family: Verdana;font-size: 70%"><b>
				<form action=<?echo($urlsauv);?> method="POST" name="VF" onSubmit="afficheMessage()">
					<p align="center">Sélectionnez la largeur désirée de la carte <?if (($largeur != '600') && ($largeur != '800') && ($largeur != '1000')) {echo (' (Appuyez sur F11 pour une meilleure visibilité)');}?>:
						<select size="1" name="largeur" style="font-family: corbel; font-size: 10pt; font-weight: bold;">
							<option value="600" <?if ($largeur == '600') {echo ('selected');}?>>600 pixels</option>
							<option value="800" <?if ($largeur == '800') {echo ('selected');}?>>800 pixels</option>
							<option value="1000" <?if ($largeur == '1000') {echo ('selected');}?>>1000 pixels</option>
							<option value="<?=$largeur_opt;?>" <?if (($largeur != '600') && ($largeur != '800') && ($largeur != '1000')) {echo ('selected');}?>>la mieux adaptée à votre écran<?if (($largeur != '600') && ($largeur != '800') && ($largeur != '1000')) {echo (' : '.$largeur.' pixels');}?></option>
						</select><br>
						<br><font size=4><b>Critères des requêtes :</b></font>
						<br>Régime d’accès :
						<select size="1" name="regime" style="font-family: corbel; font-size: 10pt; font-weight: bold;">
							<option value="les2" <?if ($regime == 'les2') {echo ('selected');}?>>Tous</option>
							<option value="sans" <?if ($regime == 'sans') {echo ('selected');}?>>Sans réserve</option>
							<option value="selon" <?if ($regime == 'selon') {echo ('selected');}?>>Selon accord de l'auteur</option>
						</select>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Milieu :
						<select size="1" name="milieu" style="font-family: corbel; font-size: 10pt; font-weight: bold;">
							<option value="tous" <?if ($milieu == 'tous') {echo ('selected');}?>>Tous</option>
							<option value="aqua" <?if ($milieu == 'aqua') {echo ('selected');}?>>Aquatique</option>
							<option value="bois" <?if ($milieu == 'bois') {echo ('selected');}?>>Boisement</option>
							<option value="cana" <?if ($milieu == 'cana') {echo ('selected');}?>>Canal</option>
							<option value="chem" <?if ($milieu == 'chem') {echo ('selected');}?>>Chemin</option>
							<option value="haie" <?if ($milieu == 'haie') {echo ('selected');}?>>Haie</option>
							<option value="mare" <?if ($milieu == 'mare') {echo ('selected');}?>>Mare</option>
							<option value="peup" <?if ($milieu == 'peup') {echo ('selected');}?>>Peupleraie</option>
							<option value="prai" <?if ($milieu == 'prai') {echo ('selected');}?>>Prairie</option>
							<option value="ripi" <?if ($milieu == 'ripi') {echo ('selected');}?>>Ripisylve</option>
							<option value="rose" <?if ($milieu == 'rose') {echo ('selected');}?>>Roselière</option>
						</select><br>
						<br>
						<?
						//if (isset($_POST["action"]) && $_POST["action"] == "Ok" && $nbres == 0) {echo("<font style='color: #F76C6C; font-weight: bold; font-size: 0.8em;'>Aucun résultat ...</font><br>");}else{echo("<br>");}
						if ($nbreleve == 0) {
							echo("<font style='color: #F76C6C; font-weight: bold; font-size: 0.8em;'>Aucun résultat ...</font><br>");
						}else{
							echo("<font style='color: #F76C6C; font-weight: bold; font-size: 0.8em;'>".$nbreleve." relevé(s)</font><br>");
						}
						?>
						<a href='<?echo($urlinit);?>'>Réinitialiser tous les critères</a>
						<br><br><input type="submit" name="action" value="Ok" style="font-family: Verdana; font-size: 8pt;"></p>
					</form>
				</b></p>
			</div><br>
			<div id="select"></div>
		</div>
		<div id="boiteInfo">Veuillez patienter... <img src="ajax-loader.gif"></div>
		<div id="profil" hidden="true">
			<div id="coords_title">Profil altimétrique</div>
			<div id="coords"></div>
		</div>
	</body>
	</html>
