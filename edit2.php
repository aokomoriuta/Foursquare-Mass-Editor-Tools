<?php
mb_internal_encoding("UTF-8");
mb_http_output( "iso-8859-1" );
ob_start("mb_output_handler");
header("Content-Type: text/html; charset=ISO-8859-1",true);

$VERSION = "CSV Venues Editor 0.5 beta";

$oauth_token = $_POST["oauth_token"];

//if (is_uploaded_file($_FILES['csv']['tmp_name'])) {
//  $csv = $_FILES['csv']['tmp_name'];
if (is_uploaded_file($_FILES['csvs']['tmp_name'][0])) {
  $csv = $_FILES['csvs']['tmp_name'][0];
  require "CsvToArray.Class.php";
  $file = CsvToArray::open($csv);
  if (count($file) > 500) {
    echo '<html><head><title>', $VERSION, '</title><meta http-equiv="Content-type" content="text/html; charset=ISO-8859-1" /><style type="text/css">body, html { font-family:helvetica,arial,sans-serif; font-size:90%; }</style></head><body><p>O limite da API &eacute; de 500 requisi&ccedil;&otilde;es por hora por conjunto de endpoints por OAuth.</p><p>Reduza a quantidade de linhas do arquivo e tente novamente.<p><button type="button" onclick="history.go(-1)">Voltar</button></p></body></html>';
    exit;
  }
  if (array_key_exists("venue", $file[0]) == false) {
    echo '<html><head><title>', $VERSION, '</title><meta http-equiv="Content-type" content="text/html; charset=ISO-8859-1" /><style type="text/css">body, html { font-family:helvetica,arial,sans-serif; font-size:90%; }</style></head><body><p>A coluna "venue" &eacute; obrigat&oacute;ria para a edi&ccedil;&atilde;o!</p><p>Verifique o arquivo CSV e tente novamente.<p><button type="button" onclick="history.go(-1)">Voltar</button></p></body></html>';
    exit;
  }
} else {
  echo '<html><head><title>', $VERSION, '</title><meta http-equiv="Content-type" content="text/html; charset=ISO-8859-1" /><style type="text/css">body, html { font-family:helvetica,arial,sans-serif; font-size:90%; }</style></head><body>Erro ao enviar o arquivo!</body></html>';
  exit();
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html dir="ltr">
<head>
<title><?php echo $VERSION;?></title>
<meta http-equiv="Content-type" content="text/html; charset=ISO-8859-1" />
<script src="js/dojo/dojo.js" djConfig="parseOnLoad: true"></script>
<script type="text/javascript">
dojo.require("dijit.Dialog");
dojo.require("dijit.form.Button");
dojo.require("dijit.form.ComboBox");
dojo.require("dijit.form.TextBox");
function xmlhttpPost(venue, dados, i) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState == 4) {
      var resposta = JSON.parse(xmlhttp.responseText);
      if (xmlhttp.status == 200)
        //document.getElementById("result" + i).innerHTML = xmlhttp.responseText;
        document.getElementById("result" + i).innerHTML = "<img src='img/ok.png' alt='OK' style='vertical-align: middle;'>";
      else if (xmlhttp.status == 400)
        document.getElementById("result" + i).innerHTML = "<img src='img/erro.png' alt='" + "Erro 400: Bad Request, Tipo: " + resposta.meta.errorType + ", Detalhe: " + resposta.meta.errorDetail + "' style='vertical-align: middle;'>";
      else if (xmlhttp.status == 401)
        document.getElementById("result" + i).innerHTML = "<img src='img/erro.png' alt='" + "Erro 401: Unauthorized, Tipo: " + resposta.meta.errorType + ", Detalhe: " + resposta.meta.errorDetail + "' style='vertical-align: middle;'>";
      else if (xmlhttp.status == 403)
        document.getElementById("result" + i).innerHTML = "<img src='img/erro.png' alt='" + "Erro 403: Forbidden" + "' style='vertical-align: middle;'>";
      else if (xmlhttp.status == 404)
        document.getElementById("result" + i).innerHTML = "<img src='img/erro.png' alt='" + "Erro 404: Not Found" + "' style='vertical-align: middle;'>";
      else if (xmlhttp.status == 405)
        document.getElementById("result" + i).innerHTML = "<img src='img/erro.png' alt='" + "Erro 405: Method Not Allowed" + "' style='vertical-align: middle;'>";
      else if (xmlhttp.status == 500)
        document.getElementById("result" + i).innerHTML = "<img src='img/erro.png' alt='" + "Erro 500: Internal Server Error" + "' style='vertical-align: middle;'>";
      else
        document.getElementById(result).innerHTML = "<img src='img/erro.png' alt='" + "Erro desconhecido: " + xmlhttp.status + "' style='vertical-align: middle;'>";
    }
  }
  xmlhttp.open("POST", "https://api.foursquare.com/v2/venues/" + venue + "/edit", true);
  xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
  xmlhttp.send(dados);
  return false;
}
function salvarVenues() {
  var oauth_token = "<?php echo $oauth_token;?>";
  var venue, dados, ll;
  //document.getElementById("result").innerHTML = "Enviando dados...";
  for (i = 0; i < document.forms.length; i++) {
    venue = document.forms[i]["venue"].value;
    dados = "oauth_token=" + oauth_token;
    for (j = 1; j < document.forms[i].elements.length; j++) {
      if (document.forms[i].elements[j].name != "ll")
        dados += "&" + document.forms[i].elements[j].name + "=" + document.forms[i].elements[j].value;
      else {
        ll = document.forms[i]["ll"].value;
        if (ll != null && ll != "")
          dados += "&ll=" + document.forms[i]["ll"].value;
      }
    }
    dados += "&v=20111130";
    //document.getElementById("result").innerHTML += "<br>venue=" +venue + "<br>dados=" + dados + "<br>result=result" + i;
    document.getElementById("result" + i).innerHTML = "<img src='img/loading.gif' alt='Enviando dados...' style='vertical-align: middle;'>";
    xmlhttpPost(venue, dados, i);
  }
  //document.getElementById("result").innerHTML = "Dados enviados!";
}
var dlg;
dojo.addOnLoad(function() {
  // create the dialog:
  dlg = new dijit.Dialog({
    title: "Guia de estilo",
    style: "width: 400px"
  });
});
function showDialog() {
  // set the content of the dialog:
  dlg.attr("content", "<ul><li><p>Use sempre a ortografia e as letras maiúsculas corretas.</p></li><li><p>Em redes ou lugares com vários locais, não é mais preciso adicionar um sufixo de local. Portanto, pode deixar &quot;Starbucks&quot; ou &quot;Apple Store&quot; (em vez de &quot;Starbucks - Queen Anne&quot; ou &quot;Apple Store – Cidade alta&quot;).</p></li><li><p>Sempre que possível, use abreviações: &quot;Av.&quot; em vez de &quot;Avenida&quot;, &quot;R.&quot; em vez de &quot;Rua&quot;, etc.</p></li><li>Cross Street should be like one of the following:<ul><li>na R. Main (para lugares em uma esquina)</li><li>entre a Av. 2a. e Av. 3a. (para lugares no meio de um quarteirão)</li></ul><br></li><li>A R. Cross não <b>deve</b> ter o nome repetido da rua no endereço.<ul><li>Se o local é na R. Principal, a rua transversal deve ser &quot;na Segunda Av.&quot;</li><li>A transversal não deve ser &quot;R. Principal na R. Segunda&quot;</li></ul></li><li><p>Os nomes de estados e províncias devem ser abreviados.</p></li><li><p><b>Em caso de dúvida, formate os endereços de lugares de acordo com as diretrizes postais locais.</b></p></li><li><p>Se tiver mais perguntas sobre a criação e edição de lugares no foursquare, consulte nossas <a href='https://pt.foursquare.com/info/houserules' target='_blank'>regras da casa</a> e as <a href='http://support.foursquare.com/forums/191151-venue-help' target='_blank'>perguntas frequentes sobre lugares</a>.</p></li></ul>");
  dlg.show();
}
</script>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<link rel="stylesheet" type="text/css" href="js/dijit/themes/claro/claro.css"/>
<link rel="stylesheet" type="text/css" href="estilo.css"/>
</head>
<body class="claro">
<h2>Editar venues!</h2>
<p>Antes de salvar suas altera&ccedil;&otilde;es, não deixe de ler nosso <a href="javascript:showDialog();">guia de estilo</a> e as <a href="https://pt.foursquare.com/info/houserules" target="_blank">regras da casa</a>.<p>
<table class="listContainer">
<?php
if (array_key_exists("name", $file[0])) {
  $hasName = true;
  //echo '<th>Nome</th>';
} else {
  $hasName = false;
}
if (array_key_exists("address", $file[0])) {
  $hasAddress = true;
  //echo '<th>Endere&ccedil;o</th>';
} else {
  $hasAddress = false;
}
if (array_key_exists("crossStreet", $file[0])) {
  $hasCross = true;
  //echo '<th>Rua Cross</th>';
} else {
  $hasCross = false;
}
if (array_key_exists("city", $file[0])) {
  $hasCity = true;
  //echo '<th>Cidade</th>';
} else {
  $hasCity = false;
}
if (array_key_exists("state", $file[0])) {
  $hasState = true;
  //echo '<th>Estado</th>';
} else {
  $hasState = false;
}
if (array_key_exists("zip", $file[0])) {
  $hasZip = true;
  //echo '<th>CEP</th>';
} else {
  $hasZip = false;
}
if (array_key_exists("twitter", $file[0])) {
  $hasTwitter = true;
  //echo '<th>Twitter</th>';
} else {
  $hasTwitter = false;
}
if (array_key_exists("phone", $file[0])) {
  $hasPhone = true;
  //echo '<th>Telefone</th>';
} else {
  $hasPhone = false;
}
if (array_key_exists("url", $file[0])) {
  $hasUrl = true;
  //echo '<th>Website</th>';
} else {
  $hasUrl = false;
}
if (array_key_exists("description", $file[0])) {
  $hasDesc = true;
  //echo '<th>Descri&ccedil;&atilde;o</th>';
} else {
  $hasDesc = false;
}
if (array_key_exists("ll", $file[0])) {
  $hasLl = true;
  //echo '<th>Lat/Long</th>';
} else {
  $hasLl = false;
}
//echo '<th></th></tr>', chr(10);

$ufs = array("AC", "AL", "AP", "AM", "BA", "CE", "DF", "ES", "GO", "MA", "MT", "MS", "MG", "PA", "PB", "PR", "PE", "PI", "RJ", "RN", "RS", "RO", "RR", "SC", "SP", "SE", "TO");

$i = 0;

foreach ($file as $f) {
  $i++;

  $formId = "form$i";
  echo '<tr>', chr(10), '<form name="', $formId, '" accept-charset="utf-8" encType="multipart/form-dados" method="post">', chr(10);

  $venue = $f[venue];
  echo '<td><input type="hidden" name="venue" value="', $venue, '"><a href="https://foursquare.com/v/', $venue, '" target="_blank">', $i, '</a></td>', chr(10);

  $name = htmlentities($f[name]);
  if ($hasName) {
    echo '<td><input type="text" name="name" maxlenght="256" value="', $name, '" dojoType="dijit.form.TextBox" placeHolder="Nome" style="width: 9em;" onFocus="window.temp=this.value" onBlur="if (window.temp != this.value) dojo.byId(\'result', $i - 1, '\').innerHTML=\'\'" onChange="dojo.byId(\'result', $i - 1, '\').innerHTML=\'\'"></td>', chr(10);
  }

  $address = htmlentities($f[address]);
  if ($hasAddress) {
    echo '<td><input type="text" name="address" maxlength="128" value="', $address, '" dojoType="dijit.form.TextBox" placeHolder="Endere&ccedil;o" style="width: 9em;"></td>', chr(10);
  }

  $crossStreet = htmlentities($f[crossStreet]);
  if ($hasCross) {
    echo '<td><input type="text" name="crossStreet" maxlength="51" value="', $crossStreet, '" dojoType="dijit.form.TextBox" placeHolder="Rua Cross" style="width: 9em;"></td>', chr(10);
  }

  $city = htmlentities($f[city]);
  if ($hasCity) {
    echo '<td><input type="text" name="city" maxlength="31" value="', $city, '" dojoType="dijit.form.TextBox" placeHolder="Cidade" style="width: 8em;"></td>', chr(10);
  }

  $state = $f[state];
  if ($hasState) {
    echo '<td><select dojoType="dijit.form.ComboBox" id="state', $i, '" name="state" style="width: 4em;">';
    $key = array_search($state, $ufs);
    for ($j = 0; $j <= 26; $j++) {
      if ($key == $j) {
        echo '<option value="', $state, '" selected>', $state, '</option>';
      }
      echo '<option value="', $ufs[$j], '">', $ufs[$j], '</option>';
    }
    echo '</select></td>', chr(10);
  }

  $zip = $f[zip];
  if ($hasZip) {
    echo '<td><input type="text" name="zip" maxlength="13" value="', $zip, '" dojoType="dijit.form.TextBox" placeHolder="CEP" style="width: 8em;"></td>', chr(10);
  }

  $twitter = $f[twitter];
  if ($hasTwitter) {
    echo '<td><input type="text" name="twitter" maxlength="51" value="', $twitter, '" dojoType="dijit.form.TextBox" placeHolder="Twitter" style="width: 8em;"></td>', chr(10);
  }

  $phone = $f[phone];
  if ($hasPhone) {
    echo '<td><input type="text" name="phone" maxlength="21" value="', $phone, '" dojoType="dijit.form.TextBox" placeHolder="Telefone" style="width: 8em;"></td>', chr(10);
  }

  $url = $f[url];
  if ($hasUrl) {
    echo '<td><input type="text" name="url" maxlength="256" value="', $url, '" dojoType="dijit.form.TextBox" placeHolder="Website" style="width: 9em;"></td>', chr(10);
  }

  $description = htmlentities($f[description]);
  if ($hasDesc) {
    echo '<td><input type="text" name="description" maxlength="300" value="', $description, '" dojoType="dijit.form.TextBox" placeHolder="Descri&ccedil;&atilde;o" style="width: 9em;"></td>', chr(10);
  }

  $ll = $f[ll];
  if ($hasLl) {
    if (($ll != '') && ($ll != ' ')) {
      echo '<td><input type="text" name="ll" maxlength="402" value="', $ll, '" dojoType="dijit.form.TextBox" placeHolder="Lat/Long" style="width: 9em;"></td>', chr(10);
    } else {
      echo '<td><input type="text" name="ll" dojoType="dijit.form.TextBox" placeHolder="Lat/Long" style="width: 9em;" disabled></td>', chr(10);
    }
  }
  echo '<td><div id="result', $i - 1, '"></div></td>', chr(10), '</form>', chr(10), '</tr>', chr(10);
}
?>
</table>
<button dojoType="dijit.form.Button" type="button" onclick="salvarVenues()" name="submitButton">Salvar</button>
<button dojoType="dijit.form.Button" type="button" onclick="history.go(-1)" name="backButton">Voltar</button>
<!--<p><div id="result"></div></p>-->
</body>
</html>
