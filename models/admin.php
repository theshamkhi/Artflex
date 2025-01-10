<?php
require_once '../config/db.php';
require_once 'user.php';
require_once 'author.php';
require_once 'admin.php';
require_once 'reader.php';


class Admin extends User {
    protected $connection;

    public function __construct() {
        $db = new DbConnection();
        $this->connection = $db->getConnection();
    }

    public function getAllArticles() {
        try {
            $query = "SELECT categories.Name AS CatName, articles.*, users.Name AS AuthorName
                      FROM articles
                      JOIN categories ON categories.CatID = articles.CatID
                      JOIN users ON users.UserID = articles.AuthorID
                      WHERE status = 'Approved'
                      ORDER BY PubDate DESC";
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }    
    public function getArts() {
        try {
            $query = "SELECT categories.Name AS CatName, articles.*, users.Name AS AuthorName
                    FROM articles
                    JOIN categories ON categories.CatID = articles.CatID
                    JOIN users ON users.UserID = articles.AuthorID";
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $articles;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
    public function updateArtStatus($catID, $action) {
        $status = ($action === 'approve') ? 'Approved' : 'Rejected';
        $sql = "UPDATE Articles SET status = :status WHERE CatID = :cat_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':status' => $status,
            ':cat_id' => $catID,
        ]);
    }
    public function addCat($name) {
        try {
            $query = "INSERT INTO Categories (Name) 
                      VALUES (:name)";
            $stmt = $this->connection->prepare($query);
    
            $stmt->execute([
                ':name' => $name
            ]);
    
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return "Failed to create category: " . $e->getMessage();
        }
    }
    public function getCats() {
        try {
            $query = "SELECT * FROM Categories";
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $categories;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
    public function deleteCat($catID) {
        try {
            $query1 = "UPDATE Articles SET CatID = 4 WHERE CatID = :catID";
            $stmt1 = $this->connection->prepare($query1);
            $stmt1->execute([':catID' => $catID]);
    
            $query2 = "DELETE FROM Categories WHERE CatID = :catID";
            $stmt2 = $this->connection->prepare($query2);
            $stmt2->execute([':catID' => $catID]);
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
}
?>

