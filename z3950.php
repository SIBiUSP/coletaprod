<!DOCTYPE html>
<html lang="pt-br" dir="ltr">
    <head>
        <?php 
            include('inc/config.php');             
            include('inc/meta-header.php');
            include('inc/functions.php');
            
            if(!empty($_SESSION['oauthuserdata'])) { 
                store_user($_SESSION['oauthuserdata'],$client);
            }
        ;      

        ?> 
        <title>Conversor do arquivo JSON da Plataforma Lattes para o ElasticSearch - Coleta Produção USP</title>
        <script src="http://cdn.jsdelivr.net/g/filesaver.js"></script>
        <script>
              function SaveAsFile(t,f,m) {
                    try {
                        var b = new Blob([t],{type:m});
                        saveAs(b, f);
                    } catch (e) {
                        window.open("data:"+m+"," + encodeURIComponent(t), '_blank','');
                    }
                }
        </script>         
        
    </head>

    <body>     
        
        <?php include('inc/navbar.php'); ?>
        
        <div class="uk-container uk-container-center uk-margin-large-bottom">
            <div class="uk-width-medium-1-1">

<?php 

$isbn = $_GET["isbn"];
//$host = ["dedalus.usp.br:9991/usp01","biblioteca2.senado.leg.br:9992/sen01","lx2.loc.gov:210/LCDB","marte.biblioteca.upm.es:2200","sirsi.library.utoronto.ca:2200","ilsz3950.nlm.nih.gov:7091/VOYAGER","168.176.5.96:9991/SNB01","athena.biblioteca.unesp.br:9992/uep01","library.ox.ac.uk:210/aleph","zcat.libraries.psu.edu:2200","ringding.law.yale.edu:210/INNOPAC","newton.lib.cam.ac.uk:7090/VOYAGER"];
$host = ["dedalus.usp.br:9991/usp01"];

function query_z3950($isbn,$host) {
    $isbn_query='@attr 1=7 '.$isbn.'';
    
    $id = yaz_connect($host);
    yaz_syntax($id, "usmarc");
    yaz_range($id, 1, 10);
    yaz_search($id, "rpn", $isbn_query);    
    yaz_wait();
    $error = yaz_error($id);

    if (!empty($error)) {
        echo "Error: $error";
    } else {
        $hits = yaz_hits($id);
        
        for ($p = 1; $p <= $hits; $p++) {
            $rec_download = yaz_record($id, $p, "raw");
            $rec_download = str_replace('"','',$rec_download);
            $rec = yaz_record($id, $p, "string");
            print_r($rec);
            echo '<br/><br/>';
            print_r($rec_download);
            echo '<br/><br/>';
            echo '<button onclick="SaveAsFile(\''.addslashes($rec_download).'\',\'record.mrc\',\'text/plain;charset=utf-8\')">Baixar MARC</button>';
            echo '<br/><br/>';
            $result_record = z3950::parse_usmarc_string($rec);
            print_r($result_record);
            echo '<br/><br/>';
        }
        
        
//        
//        echo " $hits resultado(s)";        
//        print_r(yaz_record($id, 1, "raw"));
//        echo '<br/><br/>';
//        print_r(yaz_record($id, 1, "string"));
//        echo '<br/><br/>';
//        $rec = yaz_record($id, 1, "string");
//        $result_record = z3950::parse_usmarc_string($rec);
//        print_r($result_record);
    }
    
//        for ($p = 1; $p <= 10; $p++) {
//            if ($host[$i] == "lx2.loc.gov:210/LCDB") {
//                $rec_download = yaz_record($id[$i], $p, "raw");
//                $rec_download = str_replace('"','',$rec_download);
//            } else {
//                $rec_download = yaz_record($id[$i], $p, "raw");
//                $rec_download = str_replace('"','',$rec_download);
//            }
//            $rec = yaz_record($id[$i], $p, "string");
//            if (empty($rec)) continue;
//            $result_record = parse_usmarc_string($rec);
//            $rec_id= $i.$p;
//            echo '<div><div class="ui top attached tabular menu menu'.$rec_id.'">
//            <a class="item active" data-tab="first'.$rec_id.'">Resumo</a>
//            <a class="item" data-tab="second'.$rec_id.'">Registro completo</a>
//            </div>
//            <div class="ui bottom attached tab segment active" data-tab="first'.$rec_id.'">
//            <table class="ui celled table">
//            <thead>
//            <tr>
//            <th>ISBN</th>
//            <th>Título</th>
//            <th>Autor</th>
//            <th>Editora</th>
//            <th>Local</th>
//            <th>Ano</th>
//            <th>Edição</th>
//            <th>Descrição física</th>
//            <th>Download</th>
//            </tr>
//            </thead>
//            <tbody>
//            <tr>
//            <td>'.$result_record[isbn].'</td>
//            <td>'.$result_record[title].'</td>
//            <td>'.$result_record[author].'</td>
//            <td>'.$result_record[publisher].'</td>
//            <td>'.$result_record[pub_place].'</td>
//            <td>'.$result_record[pub_date].'</td>
//            <td>'.$result_record[edition].'</td>
//            <td>'.$result_record[extent].'</td>
//            <td><button  class="ui blue label" onclick="SaveAsFile(\''.addslashes($rec_download).'\',\'record.mrc\',\'text/plain;charset=utf-8\')">Baixar MARC</button></td>
//            </tr>
//            </tbody>
//            </table>
//            </div>
//            <div class="ui bottom attached tab segment" data-tab="second'.$rec_id.'">
//            <b>'.$p.'</b>
//            '.nl2br($rec).'
//            </div></div><br/><br/>';
//            echo '<script>
//                        $(\'.menu'.$rec_id.' .item\')
//                        .tab();
//                  </script>';
//        }
//    }
}   
foreach ($host as $host_server) {
    query_z3950($isbn,$host_server);    
}

?>