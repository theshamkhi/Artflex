<?php
require_once '../config/db.php';

class Auth extends DbConnection {

    public function register($name, $username, $password, $role) {
        try {

            $allowedRoles = ['Admin', 'Author', 'Reader'];
            if (!in_array($role, $allowedRoles)) {
                throw new Exception("Invalid role provided.");
            }

            $this->connection->beginTransaction();

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $sqlUser = "INSERT INTO Users (Name, Username, Password, Role) VALUES (:name, :username, :password, :role)";
            $stmtUser = $this->connection->prepare($sqlUser);
            $stmtUser->execute([
                ':name' => $name,
                ':username' => $username,
                ':password' => $hashedPassword,
                ':role' => $role
            ]);

            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw new Exception("Registration failed. Please try again.");
        }
    }

    public function login($username, $password) {
        try {
            $sql = "SELECT UserID, Username, Password, Role FROM Users WHERE Username = :username";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user['Password'])) {
                throw new Exception("Login failed. Please check your credentials.");
            }

            return [
                'id' => $user['UserID'],
                'username' => $user['Username'],
                'role' => $user['Role']
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }
}

class User {
    private $pdo;
    protected $id;
    protected $name;
    protected $username;
    protected $password;
    protected $role;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
}

class Admin extends User {
    public function approveArt(){}
    public function rejectArt(){}
    public function createCat(){}
    public function modifyCat(){}
    public function deleteCat(){}
}
class Reader extends User {
    public function register(){}
}
class Author extends Reader {
    public function createArt(){}
    public function modifyArt(){}
    public function deleteArt(){}
}

?>