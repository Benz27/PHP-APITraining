<?php
class ProductController
{
    public function __construct(private ProductGateway $gateway, private Auth $auth)
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
        $product = $this->gateway->get($id);
        if (!$product) {
            http_response_code(404);
            echo json_encode(["message" => "Product not found"]);
            return;
        }

        switch ($method) {
            case "GET":
                echo json_encode($product);
                break;

            case "PATCH":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $errors = $this->getValidationErrors($data, false);

                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                $rows = $this->gateway->update($product, $data);

                echo json_encode([
                    "message" => "Product $id updated",
                    "rows" => $rows
                ]);
                break;

            case "DELETE":
                $rows = $this->gateway->delete($id);
                echo json_encode([
                    "message" => "Product $id deleted",
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
                // $data = (array) json_decode(file_get_contents("php://input"), true);
                $data = $_POST;
                $errors = $this->getValidationErrors(($data));

                if (!empty($_FILES['file']['name'])) {
                    $file_name = $_FILES['file']['name'];
                    $temp_path = $_FILES['file']['tmp_name'];
                    $file_size = $_FILES['file']['size'];
                    $temp = explode(".", $_FILES['file']['name']);
                    $new_file_name = round(microtime(true)) . '.' . end($temp);

                    $upload_path = "uploads/";
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $valid_extensions = array("jpeg", "jpg", "png", "gif");

                    if (in_array($file_ext, $valid_extensions)) {
                        if (!file_exists($upload_path . $new_file_name)) {
                            if ($file_size < 10000000 && empty($errors)) {
                                $data['image'] = $upload_path . $new_file_name;
                                move_uploaded_file($temp_path, $upload_path . $new_file_name);
                            } else {
                                $errors[] = "file is too large, maximum file size is 5Mb";
                            };
                        } else {
                            $errors[] = "file already exists in upload folder";
                        }
                    } else {
                        $errors[] = "Invalid file format";
                    }
                } else {
                    if (empty($file_name)) {
                        $errors[] = "Image is required";
                    }
                }


                
                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                $id = $this->gateway->create($data);

                http_response_code(201);
                echo json_encode([
                    "message" => "Product created",
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

        if (array_key_exists("size", $data)) {
            if (filter_var($data["size"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "size must be an integer";
            }
        }

        if ($is_new && empty($data["price"])) {
            $errors[] = "price is required";
        }


        return $errors;
    }
}
