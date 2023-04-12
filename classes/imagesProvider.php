<?php 
class imagesProvider {

    private $con;

    public function __construct($con){
        $this->con = $con;
    }
    public function getNumberOfResults($term){
        $query = $this->con->prepare("SELECT COUNT(*) as total FROM images
        WHERE (title LIKE :term 
        OR alt LIKE :term)
        AND broken = 0");

        $searchTerm= "%" . $term . "%";
        $query->bindParam(":term", $searchTerm);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row["total"];
    }

    public function getResultsHtml($page, $pageSize, $term){

        $from = ($page - 1) * $pageSize;
        $query = $this->con->prepare("SELECT * FROM images
        WHERE (title LIKE :term 
        OR alt LIKE :term)
        AND broken = 0
        ORDER BY clicks DESC
        LIMIT :fromLimit, :pageSize");

        $searchTerm= "%" . $term . "%";
        $query->bindParam(":term", $searchTerm);
        $query->bindParam(":fromLimit", $from, PDO::PARAM_INT);
        $query->bindParam(":pageSize", $pageSize, PDO::PARAM_INT);
        $query->execute();

        $resultsHtml = "<div class='imageResults'>";

        $count = 0;
        while ($row = $query->fetch(PDO::FETCH_ASSOC)){
            
            $count++;
            $imageURL = $row["imageURL"];
            $siteURL = $row["siteURL"];
            $title = $row["title"];
            $alt = $row["alt"];
            $id = $row["id"];

            if ($title){
                $info = $title;
            }
            else if ($alt){
                $info = $alt;
            }
            else {
                $info = $imageURL;
            }

            $resultsHtml .= "<div class='gridItem image$count'>
                                <a href = '$imageURL' data-fancybox>
                                    <script>
                                        $(document).ready(function(){
                                            loadImage(\"$imageURL\", \"image$count\");
                                        })
                                    </script>
                                    <span class='imageInfo'>$info</span>
                                </a>
                            </div>";
        }

        $resultsHtml .= "</div>";
        return $resultsHtml;
    }

}
?>