<?php
require_once '../config/db.php';
require_once 'user.php';
require_once 'author.php';
require_once 'admin.php';
require_once 'reader.php';


class User {
    protected $connection;
    protected $userID;
    protected $name;
    protected $email;
    protected $username;
    protected $photo;
    protected $role;

    public function __construct() {
        $db = new DbConnection();
        $this->connection = $db->getConnection();
    }

    public function login($username, $password) {
        try {
            $query = "SELECT * FROM users WHERE username = :username";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['Password'])) {
                $_SESSION['user_id'] = $user['UserID'];
                $this->userID = $user['UserID'];
                $this->name = $user['Name'];
                $this->username = $user['Username'];
                $this->role = $user['Role'];
                return $this;
            }

            return null;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function getUserID() {
        return $this->userID;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getRole() {
        return $this->role;
    }

    public function setUserID($id) {
        $this->userID = (int)$id;
    }
    public function getUserData(){
        $userID = $_SESSION['user_id'];
        $query = "SELECT * FROM Users WHERE UserID = :userID";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([':userID' => $userID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getArtByID($artID) {
        $query = "SELECT * FROM articles WHERE ArtID = :artID";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([
            ':artID' => $artID,
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
    public function getByCat($categoryID = null) {
        try {
            $query = "SELECT categories.Name AS CatName, articles.*, users.Name AS AuthorName
                      FROM articles
                      JOIN categories ON categories.CatID = articles.CatID
                      JOIN users ON users.UserID = articles.AuthorID
                      WHERE status = 'Approved'";
            if ($categoryID) {
                $query .= " AND articles.CatID = :categoryID";
            }
            $query .= " ORDER BY PubDate DESC";
            $stmt = $this->connection->prepare($query);
    
            if ($categoryID) {
                $stmt->execute([':categoryID' => $categoryID]);
            } else {
                $stmt->execute();
            }
    
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
    
    public function getBySearch($searchTerm) {
        try {
            $sql = "SELECT categories.Name AS CatName, articles.*, users.Name AS AuthorName
                    FROM articles
                    JOIN categories ON categories.CatID = articles.CatID
                    JOIN users ON users.UserID = articles.AuthorID
                    WHERE (articles.Title LIKE :searchTerm OR articles.Content LIKE :searchTerm)
                    AND status = 'Approved'
                    ORDER BY PubDate DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':searchTerm' => "%$searchTerm%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
    
    public function getByCategoryAndSearch($categoryID = null, $searchTerm = null) {
        try {
            $query = "SELECT categories.Name AS CatName, articles.*, users.Name AS AuthorName
                      FROM articles
                      JOIN categories ON categories.CatID = articles.CatID
                      JOIN users ON users.UserID = articles.AuthorID
                      WHERE status = 'Approved'";
            
            $params = [];
            if ($categoryID) {
                $query .= " AND articles.CatID = :categoryID";
                $params[':categoryID'] = $categoryID;
            }
            if ($searchTerm) {
                $query .= " AND (articles.Title LIKE :searchTerm OR articles.Content LIKE :searchTerm)";
                $params[':searchTerm'] = "%$searchTerm%";
            }
            $query .= " ORDER BY PubDate DESC";
    
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
    public function updateUserData($name, $photo){
        $userID = $_SESSION['user_id'];
        $query = "UPDATE Users SET Name = :name, PhotoURL = :photoURL WHERE UserID = :userID";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([
            ':name' => $name,
            ':photoURL' => $photo,
            ':userID' => $userID
        ]);
    }
}
?>

