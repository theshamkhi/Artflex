<?php
require_once '../config/db.php';
require_once 'user.php';
require_once 'author.php';
require_once 'admin.php';
require_once 'reader.php';



class Author extends Reader {
    protected $connection;

    public function __construct() {
        $db = new DbConnection();
        $this->connection = $db->getConnection();
    }
    public function createArt($title, $photoURL, $content, $category) {
        try {
            $query = "INSERT INTO Articles (AuthorID, CatID, PhotoURL, Title, Content, PubDate, Status) 
                      VALUES (:author_id, :cat_id, :photo_url, :title, :content, :pub_date, :status)";
            $stmt = $this->connection->prepare($query);
    
            $pubDate = date('Y-m-d H:i:s');
            $status = 'pending';
    
            $stmt->execute([
                ':author_id' => $this->userID,
                ':cat_id' => $category,
                ':photo_url' => $photoURL,
                ':title' => $title,
                ':content' => $content,
                ':pub_date' => $pubDate,
                ':status' => $status,
            ]);
    
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return "Failed to create article: " . $e->getMessage();
        }
    }
    public function deleteArt($artID) {
        try {
            $this->connection->beginTransaction();
    
            $queryLikes = "DELETE FROM likes WHERE ArtID = :artID";
            $stmtLikes = $this->connection->prepare($queryLikes);
            $stmtLikes->execute([':artID' => $artID]);
    
            $queryArticles = "DELETE FROM Articles WHERE ArtID = :artID AND AuthorID = :authorID";
            $stmtArticles = $this->connection->prepare($queryArticles);
            $stmtArticles->execute([
                ':artID' => $artID,
                ':authorID' => $this->userID
            ]);
    
            $this->connection->commit();
    
            return $stmtArticles->rowCount() > 0;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
    public function updateArt($artID, $title, $content, $photoURL) {
        try {
            $query = "UPDATE Articles 
                      SET Title = :title, Content = :content, PhotoURL = :photoURL 
                      WHERE ArtID = :artID AND AuthorID = :authorID";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([
                ':title' => $title,
                ':content' => $content,
                ':photoURL' => $photoURL,
                ':artID' => $artID,
                ':authorID' => $this->userID
            ]);
    
            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    public function getAuthorArts() {
        try {
            $query = "SELECT categories.Name AS CatName, articles.*, users.Name AS AuthorName
                    FROM articles
                    JOIN categories ON categories.CatID = articles.CatID
                    JOIN users ON users.UserID = articles.AuthorID
                    WHERE AuthorID = :author_id";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([':author_id' => $this->userID]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }
}
?>

