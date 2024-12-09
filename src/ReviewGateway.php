<?php
class ReviewGateway
{
    private PDO $conn;
    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM reviews";
        $res = $this->conn->query($sql);
        $data = [];

        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            // $row["is_available"] = (bool) $row["is_available"];
            $data[] = $row;
        }

        return $data;
    }

    public function create(array $data): string
    {
        $sql = "INSERT INTO reviews (productid, name, content, rate) 
VALUES (:productid, :name, :content, :rate)";
        $res = $this->conn->prepare($sql);
        $res->bindValue(":productid", $data["productid"], PDO::PARAM_INT);
        $res->bindValue(":name", $data["name"] ?? 0, PDO::PARAM_STR);
        $res->bindValue(":content", $data["content"] ?? 0, PDO::PARAM_STR);
        $res->bindValue(":rate", $data["rate"] ?? 0, PDO::PARAM_INT);

        $res->execute();
        return $this->conn->lastInsertId();
    }

    public function get(string $id)
    {
        $sql = "SELECT * FROM reviews WHERE id = :id";
        $res = $this->conn->prepare($sql);
        $res->bindValue(":id", $id, PDO::PARAM_INT);
        $res->execute();
        $data = $res->fetch(PDO::FETCH_ASSOC);

        // if ($data !== false) {
        //     // $data["is_available"] = (bool) $data["is_available"];
        //     $sqlReviews = "SELECT * FROM reviews where productid = :productid LIMIT 10";
        //     $resReviews = $this->conn->prepare($sqlReviews);
        //     $resReviews->bindValue(":productid", $id, PDO::PARAM_INT);
        //     $resReviews->execute();
        //     $dataReviews = $resReviews->fetchAll(PDO::FETCH_ASSOC);
        //     if ($dataReviews !== false) {
        //         $data['reviews'] = $dataReviews;
        //     }
        // }

        return $data;
    }

    public function update(array $current, array $new): int
    {
        $sql = "UPDATE reviews SET name = :name, productid = :productid, content = :content, rate = :rate WHERE id =:id";
        $res = $this->conn->prepare($sql);
        $res->bindValue(":productid",  $new["productid"] ?? $current["productid"], PDO::PARAM_INT);
        $res->bindValue(":name", $new["name"] ?? $current["name"], PDO::PARAM_STR);
        $res->bindValue(":content", $new["content"] ?? $current["content"], PDO::PARAM_STR);
        $res->bindValue(":rate",  $new["rate"] ?? $current["rate"], PDO::PARAM_INT);
        $res->bindValue(":id", $current["id"], PDO::PARAM_INT);

        $res->execute();

        return $res->rowCount();
    }

    public function delete(string $id): int
    {
        $sql = "DELETE FROM reviews WHERE id = :id";
        $res = $this->conn->prepare($sql);
        $res->bindValue(":id", $id, PDO::PARAM_INT);
        $res->execute();

        return $res->rowCount();
    }
}
