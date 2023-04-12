<?php
include("config.php");
include("classes/DomDocParser.php");

$alreadyCrawled = array();
$crawling = array();
$alreadyFoundImages = array();


function linkExists($url){
    global $con;

    $query = $con->prepare("SELECT * FROM sites WHERE url=:url");
    $query->bindParam(":url", $url);
    $query->execute();

    return $query->rowCount()!=0;
}

function insertSites($url, $title, $desc, $keywords){
    global $con;

    $query = $con->prepare("INSERT INTO sites( url, title, description, keywords)
    values(:url, :title, :desc, :kwords)");
    $query->bindParam(":url", $url);
    $query->bindParam(":title", $title);
    $query->bindParam(":desc", $desc);
    $query->bindParam(":kwords", $keywords);

    return $query->execute();
}

function insertImage($url, $src, $alt, $title){
    global $con;

    $query = $con->prepare("INSERT INTO images( siteURL, imageURL, alt, title)
    values(:siteURL, :imageURL, :alt, :title)");
    $query->bindParam(":siteURL", $url);
    $query->bindParam(":imageURL", $src);
    $query->bindParam(":alt", $alt);
    $query->bindParam(":title", $title);

    $query->execute();
}

function createLink($src, $url){
    $scheme = parse_url($url)["scheme"];
    $host = parse_url($url)["host"];

    if (substr($src, 0, 2) == "//"){
        $src = $scheme . ":" . $src;
    }
    else if (substr($src, 0, 1) == "/"){
        $src = $scheme . "://" .  $host . $src;
    }
    else if (substr($src, 0, 2) == "./"){
        $src = $scheme . "://" .  $host . dirname(parse_url($url)["path"]) . substr($src, 1);
    }
    else if (substr($src, 0, 3) == "../"){
        $src = $scheme . "://" . $host . "/" . $src;
    }
    else if (substr($src, 0, 5) != "https" && substr($src, 0, 4) != "http"){
        $src = $scheme . "://" . $host . "/" . $src;
    }

    return $src;
}
function getDetails($url){
    global $alreadyFoundImages;
    $parser = new DomDocParser($url);
    $titlesArray = $parser->getTitleTags();
    if (sizeof($titlesArray) == 0 || $titlesArray->item(0) == NULL){
        return;
    }
    $title = $titlesArray->item(0)->nodeValue;
    $title = str_replace("\n", "", $title);

    if ($title == ""){
        return;
    }

    $description = "";
    $keywords = "";

    $metasArray = $parser->getMetaTags();
    foreach($metasArray as $meta){
        if ($meta->getAttribute("name") == "description"){
            $description = $meta->getAttribute("content");
        }
        if ($meta->getAttribute("name") == "keywords"){
            $keywords = $meta->getAttribute("content");
        }
        
    }

    $description = str_replace("\n", "", $description);
    $keywords = str_replace("\n", "", $keywords);
    if (linkExists($url)){
        echo "$url already exists! <br>";
    }
    else if (insertSites($url, $title, $description, $keywords)){
        echo "$url inserted successfully! <br>";
    }
    else {
        echo "$url Insertion Failed! <br>";
    }
    $imageArray = $parser->getImageTags();
    foreach($imageArray as $image){
        $src = $image->getAttribute("src");
        $alt = $image->getAttribute("alt");
        $title = $image->getAttribute("title");
        if (!$title && !$alt){
            continue;
        }
        $src = createLink($src, $url);
        if (!in_array($src,$alreadyFoundImages)){
            $alreadyFoundImages[] = $src;
            // insert the image
            insertImage($url, $src, $alt, $title);
        }
    }
}
function followLinks($url) {

    global $alreadyCrawled;
    global $crawling;
    $parser = new DomDocParser($url);

    $linkList = $parser->getLinks();

    foreach($linkList as $link) {
        $href = $link->getAttribute("href");

        if (strpos($href, "#") !== false){
            continue;
        }
        else if (substr($href, 0, 11) == "javascript:"){
            continue;
        }

        $href = createLink($href, $url);

        if (!in_array($href, $alreadyCrawled)){
            $alreadyCrawled[] = $href;
            $crawling[] = $href;

            getDetails($href);
        }
        
        
    }

    array_shift($crawling);
    foreach($crawling as $site){
        followLinks($site);
    }

}

$startUrl = "https://www.researchgate.net/";
followLinks($startUrl);
?>
