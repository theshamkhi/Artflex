<?php
require_once '../config/db.php';
require_once 'user.php';
require_once 'author.php';
require_once 'admin.php';
require_once 'reader.php';


class Reader extends User {
    protected $connection;

    public function __construct() {
        $db = new DbConnection();
        $this->connection = $db->getConnection();
    }

    public function register($name, $photo, $username, $password, $role) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $query = "INSERT INTO users (name, username, password, role, photoURL) VALUES (:name, :username, :password, :role, :photoURL)";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([
                ':name' => $name,
                ':username' => $username,
                ':password' => $hashedPassword,
                ':role' => $role,
                ':photoURL' => $photo
            ]);
            return true;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
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
    public function toggleLike($artID) {
        try {
            $query = "SELECT * FROM Likes WHERE ArtID = :artID AND UserID = :userID";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([':artID' => $artID, ':userID' => $this->userID]);
            $like = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($like) {
                // Unlike if already liked
                $query = "DELETE FROM Likes WHERE LikeID = :likeID";
                $stmt = $this->connection->prepare($query);
                $stmt->execute([':likeID' => $like['LikeID']]);
                return "unliked";
            } else {
                // Like the article
                $query = "INSERT INTO Likes (ArtID, UserID) VALUES (:artID, :userID)";
                $stmt = $this->connection->prepare($query);
                $stmt->execute([':artID' => $artID, ':userID' => $this->userID]);
                return "liked";
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    public function hasLiked($artID) {
        try {
            $query = "SELECT * FROM Likes WHERE ArtID = :artID AND UserID = :userID";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([':artID' => $artID, ':userID' => $this->userID]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    public function getFavoris() {
        try {
            $query = "SELECT articles.*, users.Name AS AuthorName
                        FROM Likes
                        JOIN articles ON articles.ArtID = Likes.ArtID
                        JOIN users ON users.UserID = Likes.UserID
                        WHERE Likes.UserID = :userID";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([':userID' => $this->userID]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }    
    public function getLikeCount($artID) {
        try {
            $query = "SELECT COUNT(*) AS likeCount FROM Likes WHERE ArtID = :artID";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([':artID' => $artID]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['likeCount'];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }  
}
?>

