<?php
require_once '../config/db.php';


class Role {
    private $role;

    public function __construct($role) {
        $this->role = $role;
    }

    public function can($action) {
        $permissions = [
            'Admin' => ['approveArt', 'rejectArt', 'createCat', 'deleteCat', 'modifyCat'],
            'Author' => ['createArt', 'modifyArt', 'deleteArt'],
            'Reader' => ['readArt']
        ];

        return in_array($action, $permissions[$this->role]);
    }

    public function getRole() {
        return $this->role;
    }
}

class User {
    private $connection;
    private $userID = null;
    private $name = null;
    private $username = null;
    private $role = null;

    public function __construct() {
        $db = new DbConnection();
        $this->connection = $db->getConnection();
    }

    public function register($name, $username, $password, $role) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $query = "INSERT INTO users (name, username, password, role) VALUES (:name, :username, :password, :role)";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([
                ':name' => $name,
                ':username' => $username,
                ':password' => $hashedPassword,
                ':role' => $role
            ]);
            return true;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function login($username, $password) {
        try {
            $query = "SELECT * FROM users WHERE username = :username";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['Password'])) {
                $this->userID = $user['UserID'];
                $this->name = $user['Name'];
                $this->username = $user['Username'];
                $this->role = new Role($user['Role']);
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

    public function performAction($action) {
        if ($this->role && $this->role->can($action)) {
            echo "Action '{$action}' performed successfully.";
        } else {
            echo "Permission denied for action '{$action}'.";
        }
    }
}


class Article {
    private PDO $connection;

    public function __construct() {
        $db = new DbConnection();
        $this->connection = $db->getConnection();
    }

    public function createArt($authorID, $catID, $title, $content, $photoURL) {
        try {
            $query = "INSERT INTO Articles (AuthorID, CatID, Title, Content, PhotoURL, PubDate) 
                      VALUES (:authorID, :catID, :title, :content, :photoURL, NOW())";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([
                ':authorID' => $authorID,
                ':catID' => $catID,
                ':title' => $title,
                ':content' => $content,
                ':photoURL' => $photoURL
            ]);
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating article: " . $e->getMessage());
            return null;
        }
    }
}


?>

