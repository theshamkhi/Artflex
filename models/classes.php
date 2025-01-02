<?php
require_once '../config/db.php';

class Auth extends DbConnection {

    public function register($username, $password, $name, $phone, $email, $role = 'Member') {
        try {
            $this->connection->beginTransaction();

            $role = ($role === 'Admin') ? 'Admin' : 'Member';

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $sqlUser = "INSERT INTO Users (Username, Password, Role) VALUES (:username, :password, :role)";
            $stmtUser = $this->connection->prepare($sqlUser);
            $stmtUser->execute([
                ':username' => $username,
                ':password' => $hashedPassword,
                ':role' => $role
            ]);

            $userId = $this->connection->lastInsertId();

            if ($role === 'Member') {
                $sqlMember = "INSERT INTO Members (MemberID, Name, Phone, Email) VALUES (:id, :name, :phone, :email)";
                $stmtMember = $this->connection->prepare($sqlMember);
                $stmtMember->execute([
                    ':id' => $userId,
                    ':name' => $name,
                    ':phone' => $phone,
                    ':email' => $email
                ]);
            }

            $this->connection->commit();
            return $userId;
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

?>