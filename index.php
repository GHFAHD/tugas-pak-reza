<?php

class Comment
{
    private ?int $id = null;
    private ?string $email = null;
    private ?string $comment = null;

    public function __construct(?int $id = null, ?string $email = null, ?string $comment = null)
    {
        $this->id = $id;
        $this->email = $email;
        $this->comment = $comment;
    }

    // Getter dan setter untuk id, email, dan comment (tambahkan sesuai kebutuhan)
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment) : void{
        $this->comment = $comment;
    }

}

interface CommentRepository
{
    function insert(Comment $comment): Comment;
    function findById(int $id): ?Comment; // Mengembalikan null jika tidak ditemukan
    function findAll(): array;
}

class CommentRepositoryImpl implements CommentRepository
{
    private PDO $connection;

    public function __construct(PDO $connection) {
        $this->connection = $connection;
    }

    function insert(Comment $comment): Comment
    {
        $sql = "INSERT INTO comments(email, comment) VALUES (?, ?)";
        $statement = $this->connection->prepare($sql);
        $statement->execute([$comment->getEmail(), $comment->getComment()]);
        $comment->setId((int)$this->connection->lastInsertId());
        return $comment;
    }

    function findById(int $id): ?Comment
    {
        $sql = "SELECT * from comments where id = ?";
        $statement = $this->connection->prepare($sql);
        $statement->execute([$id]);

        try{
             if ($row = $statement->fetch()){

            $comment = new Comment();
            $comment->setId($row['id']);
            $comment->setEmail($row['email']);
            $comment->setComment($row['comment']);
            return $comment;
        }else{
            return null;
        }
        }catch(PDOException $exception){
            echo "Error di findById: " . $exception->getMessage().PHP_EOL;
        }
       

    }

    function findAll(): array
    {
      $sql = "SELECT * from comments";
      $statement = $this->connection->query($sql);

      $array = [];
      foreach($statement as $row){
        $comment = new Comment();
        $comment->setId($row['id']);
        $comment->setEmail($row['email']);
        $comment->setComment($row['comment']);

        $array[] = $comment;

      }
      return $array;

    }
}



function getConnection(): PDO
{
    $host = "localhost";
    $port = 3306;
    $database = "belajar_php_database";
    $username = "root";
    $password = "";

    try {
        return new PDO("mysql:host=$host:$port;dbname=$database", $username, $password); // Langsung return
    } catch (PDOException $exception) {
        echo "Error terkoneksi ke database : " . $exception->getMessage() . PHP_EOL;
        die(); 
    }
}



// Contoh penggunaan:

$connection = getConnection();
$repository = new CommentRepositoryImpl($connection);

// --- Execute SQL (Membuat tabel dan insert data) ---
$sqlCreateTableCustomers = "CREATE TABLE customers (
    id VARCHAR(100) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
) ENGINE = InnoDB;";

$sqlCreateTableComments = "CREATE TABLE comments (
  id INT NOT NULL AUTO_INCREMENT,
  email VARCHAR(100) NOT NULL,
  comment TEXT,
  PRIMARY KEY (id)
) ENGINE = InnoDB;";

try {
    $connection->exec($sqlCreateTableCustomers);
    $connection->exec($sqlCreateTableComments);

    $comment = new Comment(email: "eko@test.com", comment: "Repository Pattern");
    $newComment = $repository->insert($comment);

    echo "Tabel berhasil dibuat, dan data berhasil di insert. ID = {$newComment->getId()}" . PHP_EOL;
   
} catch (PDOException $exception) {
    echo "Error saat eksekusi SQL: " . $exception->getMessage() . PHP_EOL;
}

// -- Menutup Koneksi --
$connection = null;

?>