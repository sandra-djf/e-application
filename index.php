<?php
error_reporting(E_ERROR | E_PARSE);
function getDefinition($content)
{
    $regexD = '/(<def>|<DEF>).*/s';
    $array = explode("</def>", $content);
    $definition = array();
    preg_match($regexD, $array[0], $definition, PREG_OFFSET_CAPTURE, 0);
    if (array_key_exists(0, $definition)) {
        return preg_replace('/<br \/>\n/', '', $definition[0][0] . "</def>", 1);
    }
}
$entriesTrans = array(
    "info"=> "ce fichier contient les termes de type de relations utilisées",
    "definition"=> "",
);
$typeRelations =  array(
    "info"=> "ce fichier contient les termes de type de relations utilisées",
);
$relations =  array(
    "info"=> "ce fichier contient les termes de type de relations utilisées",
);

$terme="";
if(isset($_GET['terme'])){
    $terme=$_GET['terme'];
}
if(isset($_POST['terme'])){
    $terme=$_POST['terme'];
}

if(isset($terme)){
if (!file_exists($terme)) {
    mkdir($terme);
$url='http://www.jeuxdemots.org/rezo-dump.php?gotermsubmit=Chercher&gotermrel='.$terme.'&rel=';
$page = file_get_contents($url);
$page = utf8_encode($page);



$ligne = preg_split("/[\n]+/", $page);
$def = getDefinition($page);
$entriesTrans["definition"]=$def;


$e=0;
$r=0;
$rt=0;
$raff=0;
foreach( $ligne as $row => $value ) {
    $regexE = '/(e;[0-9]+;\'[a-zA-Z0-9 èîéôêâà>-]+\';[0-9]+;[0-9]+.*)/m';
    //$regexRT = '/(rt;[0-9]+;\'[a-zA-Z0-9 èîéôêâà>-_];\'[a-zA-Z0-9 èîéôêâà>-_];\'[a-zA-Z0-9 èîéôêâà>-_])/m';
    $regexRT= "#^rt;#";
    $regexR = '/(r;[0-9]+;[0-9]+;[0-9]+;[0-9]+;-?[0-9]+)/m';
    $regexRaff = "/'" . $terme . ">[a-zA-Z\'áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ.+>=&;:?!,() -][>[a-zA-Z\'áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ.+>=&;:?!,() -]+]?/m";
    $tabValue = preg_split("/;/", $value);
    if(preg_match($regexRaff, $value)){
        $MotRaff = $tabValue[5];
        $MotRaff = substr($MotRaff,1);
        $MotRaff = substr($MotRaff,0,-1);
        $entriesTrans["raffinement"][$raff] = $MotRaff;
        $raff++;
    }

    if(preg_match($regexE, $value)==1){
        //echo $value."<br />\n";
            if (strpos($tabValue[3], "200") === false) {
                $idWord = $tabValue[1];
                if (strpos($tabValue[2], '>') === false) {
                    $word = $tabValue[2];
                } else {                    
                    $word = $tabValue[5];

                }
                $word = substr($word, 1, strlen($word) - 2);
                $entriesTrans["donnee"][$e]= array(
                    "eid" => $tabValue[1],
                    "ename" => $word,
                );
                $e++;
            }
    }

    if(preg_match($regexRT, $value)==1){
        //.echo $value."<br>";
        $rname = $tabValue[3];
        $rname = substr($rname,1);
        $rname = substr($rname,0,-1);
        $typeRelations["donnee"][$rt]= array(
            "rtype" => $tabValue[1],
            "rname" => $rname,
            );
        $rt++;
    }

    if(preg_match($regexR, $value)){
         //echo $value."<br>";
        $relations["donnee"][$r]= array(
            "rid" => $tabValue[1],
            "node1" => $tabValue[2],
            "node2" => $tabValue[3],
            "type" => $tabValue[4],
            "poid" => $tabValue[5],
            );
        $r++;
    }


}


chmod($terme, 0777);
file_put_contents($terme.'/entries.json', json_encode($entriesTrans));
chmod($terme.'/entries.json', 0777);
file_put_contents($terme.'/typeRelations.json', json_encode($typeRelations));
chmod($terme.'/typeRelations.json', 0777);
file_put_contents($terme.'/relations.json', json_encode($relations));
chmod($terme.'/relations.json', 0777);

}else{
    $fileE = $terme."/entries.json";
    $dataE = file_get_contents($fileE);
    $entriesTrans = json_decode($dataE, true);

    $filetR = $terme."/typeRelations.json";
    $datatR = file_get_contents($filetR);
    $typeRelations = json_decode($datatR, true);

    $fileR = $terme."/relations.json";
    $dataR = file_get_contents($fileR);
    $relations = json_decode($dataR, true);

}




// affichage des relations

//les raffinements
$AffichageRaff="";
if(isset($entriesTrans["raffinement"])){
foreach ($entriesTrans["raffinement"] as $key => $value) {
    $urlRaff='http://www.jeuxdemots.org/rezo-dump.php?gotermsubmit=Chercher&gotermrel='.$value.'&rel=';
    $pageRaff = file_get_contents($urlRaff);
        $lienR= "<a href='#".$value."' class='raff'>".$value."</a><br>";

        $pageRaff = utf8_encode($pageRaff);

        $defRaff = getDefinition($pageRaff);
        if(isset($defRaff)){
        $entriesTrans["definition"].="<br>".$defRaff;
        $lienR = "<a href='https://indico-responsive.herokuapp.com/index.php?terme=".$value."' class='raff'>".$value."</a><br>";
    }
    $AffichageRaff .= $lienR;
}
}




//filter pour type de relations
$dropDownFilter = "";
if(isset($typeRelations["donnee"])){
foreach ($typeRelations["donnee"] as $key => $value) {
    $dropDownFilter .= '<option class="dropdown-item" role="presentation">'.$value["rname"].'</option>';
}
}
//relation sortantes
$tableauAffichage="
<table id='tableD' class=\"table table-striped table-bordered display responsive nowrap\" style=\"width:100%\" >
    <thead>
        <tr>
            <th>rid</th>
            <th>node1</th>
            <th>node2</th>
            <th>type</th>
            <th>poid</th>
        </tr>
    </thead>
    <tbody>
";
$tabEid="";
if(isset($relations["donnee"])){
foreach ($relations["donnee"] as $key => $value) {


    $tabEid = array_column($entriesTrans["donnee"], "eid");
    $tabRid = array_column($typeRelations["donnee"], "rtype");

    $indiceNode2 = array_search($value['node2'], $tabEid);
    $indiceNode1 = array_search($value['node1'], $tabEid);
    $indiceTypeR = array_search($value['type'], $tabRid);
    $relation = array(
        "rid"=>$value["rid"],
        "node1" => "",
        "node2" => "",
        "type" => "",
        "poid" => $value["poid"],
    );

    if(isset($indiceNode2)){
        $node2 = $entriesTrans["donnee"][$indiceNode2]["ename"];
        $relation["node2"]=$node2;
    }

    if(isset($indiceNode1)){
        $node1 = $entriesTrans["donnee"][$indiceNode1]["ename"];
        $relation["node1"]=$node1;
    }

    if(isset($indiceTypeR)){
        $type = $typeRelations["donnee"][$indiceTypeR]["rname"];
        $relation["type"]=$type;
    }
    $tableauAffichage .= "
        <tr>
            <td name = 'rid'>".$relation["rid"]."</td>
            <td name = 'node1'>".$relation["node1"]."</td>
            <td name = 'node2'>".$relation["node2"]."</td>
            <td name = 'type'>".$relation["type"]."</td>
            <td name = 'poid'>".$relation["poid"]."</td>
        </tr>
    ";
}
$tableauAffichage .="    </tbody>
</table>";
}
}else{
    $terme="no terme";
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Home - INDICO</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Script HTML-CSS Case de saisie avec propositions : Outils-web.com</title>
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.23/css/jquery.dataTables.min.css">
<script type="text/javascript" src="//cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
<script src="scr/index.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.6/css/responsive.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:300,400,700,300italic,400italic,700italic">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/Forum---Thread-listing-1.css">
    <link rel="stylesheet" href="assets/css/Forum---Thread-listing.css">
    <link rel="stylesheet" href="assets/css/Pretty-Search-Form.css">
    <link rel="stylesheet" href="assets/css/Search-Input-responsive.css">
    <link rel="stylesheet" href="assets/css/Table-With-Search-1.css">
    <link rel="stylesheet" href="assets/css/Table-With-Search.css">
</head>

<body>
    <nav class="navbar navbar-light navbar-expand bg-light navigation-clean">
        <div class="container"><a class="navbar-brand" href="#">INDICO</a><button class="navbar-toggler" data-toggle="collapse"></button></div>
    </nav>
    <header class="masthead text-white text-center" style="background:url('assets/img/bg2.jpg')no-repeat center center;background-size:cover;">
        <div class="overlay"></div>
        <div class="container">
            <div class="row">
                <div class="col-md-11 col-xl-9 offset-md-0 mx-auto">
                    <h1 class="mb-5 display-4 bg-dark">Le dictionnaire d'associations lexicales<br>contributif et libre de&nbsp;<a href="http://www.jeuxdemots.org/">JeuxDeMots</a></h1>
                </div>
                <div class="col-md-10 col-lg-8 col-xl-7 mx-auto">
                    <form class="search-form" action="index.php" method="POST">
                        <div class="input-group">
                            <input class="form-control" type="search" name="terme" id="terme" >
                            <div class="input-group-append"><button class="btn btn-light" type="submit" value="valider" type="button"><i class="fa fa-search"></i></button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </header>
    <section class="features-icons bg-light text-center">
    <h1 class="display-4"> <?php if(isset($terme)) echo $terme  ?> </h1>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div>
                        <ul class="nav nav-tabs">
                            <li class="nav-item"><a class="nav-link active" role="tab" data-toggle="tab" href="#tab-1">Définitions </a></li>
                            <li class="nav-item"><a class="nav-link" role="tab" data-toggle="tab" href="#tab-2">Relations</a></li>
                            <li class="nav-item"><a class="nav-link" role="tab" data-toggle="tab" href="#tab-3">Raffinement</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" role="tabpanel" id="tab-1">
                                <section id="definition">
                                    <h1 class="display-4 text-light bg-dark">Définitions</h1>
                                    <p>
                                     <?php if(isset($entriesTrans["definition"])) echo $entriesTrans["definition"];?>
                                    </p>    
                                </section>
                            </div>
                            <div class="tab-pane" role="tabpanel" id="tab-2">
                                <section id="affichageTable">
                                    <h1 class="display-4 text-light bg-dark">Relations</h1>
                                    <div class="dropdown btn-group" role="group">
                                            <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false" type="button" style="float: left;">realtions</button>
                                            <div class="dropdown-menu" role="menu" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 38px, 0px); overflow: auto; max-height: 200px">
                                                <?php echo $dropDownFilter; ?>
                                            </div>
                                    </div> 
                                    <div style="overflow:scroll;">
                                    <?php if(isset($tableauAffichage)) echo $tableauAffichage;?>
                                    </div>
                                </section>
                            </div>
                            <div class="tab-pane" role="tabpanel" id="tab-3">
                                <section id="affichageTable">
                                    <h1 class="display-4 text-light bg-dark">Raffinement</h1>
                                        <?php echo $AffichageRaff;?>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="call-to-action text-white text-center" style="background:url(&quot;assets/img/bg2.jpg&quot;) no-repeat center center;background-size:cover;">
        <div class="overlay"></div>
        <div class="container">
            <div class="row">
                <div class="col-xl-9 mx-auto">
                    <h3 class="mb-4 display-6 bg-dark">Collectionnez les mots. Capturez-les ! Volez-les !</h3>
                </div>
                <div class="col-md-10 col-lg-8 col-xl-7 mx-auto">
                    <form>
                        <div class="form-row">
                            <div class="col-12 col-md-6 offset-md-3"><a href="http://www.jeuxdemots.org/generateGames.php" class="btn btn-primary btn-block btn-lg" >Venez jouer à JeuxDeMots !</a></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <footer class="footer bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 my-auto h-100 text-center text-lg-left">
                    <ul class="list-inline mb-2">
                        <li class="list-inline-item"><a href="about.html">À propos</a></li>
                        <li class="list-inline-item"><span>⋅</span></li>
                        <li class="list-inline-item"><a href="#">Contact</a></li>
                        <li class="list-inline-item"></li>
                        <li class="list-inline-item"></li>
                        <li class="list-inline-item"></li>
                        <li class="list-inline-item"></li>
                    </ul>
                    <p class="text-muted small mb-4 mb-lg-0">© INDICO 2020. All Rights Reserved.</p>
                </div>
                <div class="col-lg-6 my-auto h-100 text-center text-lg-right">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item"><a href="#"><i class="fa fa-facebook fa-2x fa-fw"></i></a></li>
                        <li class="list-inline-item"><a href="#"><i class="fa fa-twitter fa-2x fa-fw"></i></a></li>
                        <li class="list-inline-item"><a href="#"><i class="fa fa-instagram fa-2x fa-fw"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/Table-With-Search.js"></script>
</body>

</html>