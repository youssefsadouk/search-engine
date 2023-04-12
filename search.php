<?php
include("config.php");
include("classes/SitesProvider.php");
include("classes/imagesProvider.php");

if (isset($_GET["term"])){
    $term = $_GET["term"];
}
else{
    exit("You must enter a search term.");
}

$type = isset($_GET["type"]) ? $_GET["type"] : "sites";
$page = isset($_GET["page"]) ? $_GET["page"] : 1;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css"/>

    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <title>Querier</title>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js" integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>
</head>
<body>
    <div class="wrapper ">

        <div class="header">

            <div class="headerContent">

                <div class="logoContainer">
                    <a href="index.php"><img src="assets/images/querier_logo.png" alt="Querier's Logo"></a>
                    
                </div>

                <div class="searchContainer">
                    <form action="search.php" method="GET">
                        <div class="searchBarContainer">
                            <input type="hidden" name= "type" value="<?php echo $type?>">
                            <input class="searchBox" type="text" name="term" value="<?php echo $term?>">
                            <button class="searchButton">
                                <img src="assets/images/icons/search.png" alt="">
                            </button>
                        </div> 
                    </form>
                </div>
            </div>

            <div class="tabsContainer">
                <ul class="tabs">
                    <li class="<?php echo $type == 'sites' ? 'active' : '' ?>"><a href='<?php echo "search.php?term=$term&type=sites"; ?>'>Sites</a></li>
                    <li class="<?php echo $type == 'images' ? 'active' : '' ?>"><a href='<?php echo "search.php?term=$term&type=images"; ?>'>Images</a></li>
                    
                </ul>
            </div>
            
        </div>

        <div class="mainResultsSection">

			<?php

            if ($type == "sites"){
                $resultsProvider = new SitesProvider($con);
                $pageLimit = 20;
            }
            else {
                $resultsProvider = new imagesProvider($con);
                $pageLimit = 30;
            }
			
			$numResults = $resultsProvider->getNumberOfResults($term);

			echo "<p class='resultsCount'>$numResults results found</p>";

            echo $resultsProvider->getResultsHtml($page,$pageLimit,$term);
			?>


		</div>
        <div class="paginationContainer">
            <img src="assets/images/querier_logo.png" alt="querier logo" class="logo">
            <div class="pageNums">
                <?php 

                $pagesToShow = 10;
                $numOfPages= ceil($numResults / $pageLimit);
                $pageLeft = min($pagesToShow, $numOfPages);
                $currentPage = $page-floor($pagesToShow / 2);
                
                if ($currentPage < 1){
                    $currentPage = 1;
                }

                while($pageLeft != 0 && $currentPage <= $numOfPages){

                    if ($currentPage == $page){
                        echo "<div class='pageNumbersContainer'>
                                <span class = 'pageNum'>$currentPage</span>
                            </div>";
                    }
                    else{
                        echo "<div class='pageNumbersContainer'>
                            <a href = 'search.php?term=$term&type=$type&page=$currentPage'>
                                <span class = 'pageNum'>$currentPage</span>
                            </a>    
                        </div>";
                    }
                    

                    $currentPage++;
                    $pageLeft--;
                }
                ?>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>