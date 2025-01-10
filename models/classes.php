<?php
require_once '../config/db.php';


class User {
    private $connection;
    private $userID;
    private $name;
    private $username;
    private $role;

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
    public function getArtByID($artID) {
        $query = "SELECT * FROM articles WHERE ArtID = :artID";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([
            ':artID' => $artID,
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
            $query = "DELETE FROM Articles WHERE ArtID = :artID AND AuthorID = :authorID";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([
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
    public function getUserData(){
        $userID = $_SESSION['user_id'];
        $query = "SELECT * FROM Users WHERE UserID = :userID";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([':userID' => $userID]);
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

