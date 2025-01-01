<?php

class UserGateway
{
    private PDO $conn;
    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function register(array $data): string
    {
        $sql = "INSERT INTO users (name, email) 
VALUES (:name, :email)";
        $res = $this->conn->prepare($sql);
        $res->bindValue(":name", $data["name"], PDO::PARAM_STR);
        $res->bindValue(":email", $data["email"], PDO::PARAM_STR);

        $res->execute();
        return $this->conn->lastInsertId();
    }

    public function login(string $email, string $password)
    {
        $sql = "SELECT * FROM users WHERE email = :email AND password = :password";
        $res = $this->conn->prepare($sql);
        $res->bindValue(":email", $email, PDO::PARAM_STR);
        $res->bindValue(":password", md5($password), PDO::PARAM_STR);

        $res->execute();
        $data = $res->fetch(PDO::FETCH_ASSOC);
        if ($data !== false) {

            $payload_response = array(
                "sub" => $data["ID"],
                "Username" => $data["Username"],
                "Email" => $data["Email"],
                "FirstName" => $data["FirstName"],
                "MiddleName" => $data["MiddleName"],
                "LastName" => $data["LastName"],
                "UserType" => $data["UserType"]
            );
            $codec = new JWTCodec;
            $access_token = $codec->encode($payload_response);
            
        }
        return ["access_token" => $access_token];
    }

    public function changePassword(array $current, array $new): int
    {
        $sql = "UPDATE users SET password = :password";
        $res = $this->conn->prepare($sql);
        $res->bindValue(":password", md5($new["password"]) ?? md5($current["password"]), PDO::PARAM_STR);

        $res->execute();

        return $res->rowCount();
    }
}
