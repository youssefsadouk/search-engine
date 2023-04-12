<?php 
class SitesProvider {

    private $con;

    public function __construct($con){
        $this->con = $con;
    }
    public function getNumberOfResults($term){
        $query = $this->con->prepare("SELECT COUNT(*) as total FROM sites
        WHERE title LIKE :term 
        OR url LIKE :term
        OR keywords LIKE :term
        OR description LIKE :term");

        $searchTerm= "%" . $term . "%";
        $query->bindParam(":term", $searchTerm);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row["total"];
    }

    public function getResultsHtml($page, $pageSize, $term){

        $from = ($page - 1) * $pageSize;
        $query = $this->con->prepare("SELECT * FROM sites
        WHERE title LIKE :term 
        OR url LIKE :term
        OR keywords LIKE :term
        OR description LIKE :term
        ORDER BY clicks DESC
        LIMIT :fromLimit, :pageSize");

        $searchTerm= "%" . $term . "%";
        $query->bindParam(":term", $searchTerm);
        $query->bindParam(":fromLimit", $from, PDO::PARAM_INT);
        $query->bindParam(":pageSize", $pageSize, PDO::PARAM_INT);
        $query->execute();

        $resultsHtml = "<div class='siteResults'>";
        while ($row = $query->fetch(PDO::FETCH_ASSOC)){
            $url = $row["url"];
            $title = $row["title"];
            $description = $row["description"];
            $id = $row["id"];

            $title = $this->trimField($title, 60);
            $description = $this->trimField($description, 150);
            $url = $this->trimField($url, 150);


            $resultsHtml .= "<div class='resultContainer'>
                                <h3 class='title'>
                                    <a class='result' href='$url' data-id='$id'>
                                        $title
                                    </a>
                                
                                </h3>
                                <span class='url'>$url</span>
                                <span class='description'>$description</span>
                            </div>";
        }

        $resultsHtml .= "</div>";
        return $resultsHtml;
    }
        
    private function trimField($string, $charLimit){
        $dots = strlen($string) > $charLimit ? "..." : "";
        return substr($string, 0, $charLimit) . $dots;
    }
}
?>