<?php
class ReviewController
{
    public function __construct(private ReviewGateway $gateway)
    {
    }
    public function processRequest(string $method, ?string $id): void
    {
        // if ($id) {
        //     $this->processResourcetRequest($method, $id);
        // } else {
        //     $this->processCollectionRequest($method);
        // }


        ($id) ? $this->processResourcetRequest($method, $id) : $this->processCollectionRequest($method);
    }

    private function processResourcetRequest(string $method, string $id): void
    {
        $review = $this->gateway->get($id);
        if (!$review) {
            http_response_code(404);
            echo json_encode(["message" => "Review not found"]);
            return;
        }

        switch ($method) {
            case "GET":
                echo json_encode($review);
                break;

            case "PATCH":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $errors = $this->getValidationErrors($data, false);

                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                $rows = $this->gateway->update($review, $data);

                echo json_encode([
                    "message" => "Review $id updated",
                    "rows" => $rows
                ]);
                break;

            case "DELETE":
                $rows = $this->gateway->delete($id);
                echo json_encode([
                    "message" => "Review $id deleted",
                    "rows" => $rows
                ]);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, PATCH, DELETE");
        }
    }

    private function processCollectionRequest(string $method): void
    {
        switch ($method) {
            case "GET":
                echo json_encode($this->gateway->getAll());
                break;

            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $errors = $this->getValidationErrors(($data));

                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                $id = $this->gateway->create($data);

                http_response_code(201);
                echo json_encode([
                    "message" => "Review created",
                    "id" => $id
                ]);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, POST");
        }
    }

    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];
        if ($is_new && empty($data["name"])) {
            $errors[] = "name is requred";
        }

        if (array_key_exists("productid", $data)) {
            if (filter_var($data["productid"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "productid must be an integer";
            }
        }else{
            $errors[] = "productid is required";
        }


        if (array_key_exists("rate", $data)) {
            if (filter_var($data["rate"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "rate must be an integer";
            }
        }else{
            $errors[] = "rate is required";
        }

        return $errors;
    }
}
