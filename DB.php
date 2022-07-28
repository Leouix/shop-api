<?php

class DB {

    private PDO $pdo;

    private string $host;
    private string $dbname;
    private string $user;
    private string $pass;
    public string $charset = 'utf8';

    public string $table = 'price_product';

    public function __construct( array $db_connect ) {
        $this->host     = $db_connect['host'];
        $this->user     = $db_connect['user'];
        $this->pass     = $db_connect['password'];
        $this->dbname   = $db_connect['db'];

        $this->classInitial();
    }

    private function classInitial(): void
    {

        $this->pdo = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->pass);

        if( ! $this->isPriceTableExist() ) {
            $this->createPriceTable();
        }

    }

    private function isPriceTableExist()
    {
        $sql = "SHOW TABLES LIKE '$this->table'";
        $query = $this->pdo->query($sql);
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    private function createPriceTable()
    {

        $sql = "CREATE TABLE {$this->table} (
            id INT NOT NULL AUTO_INCREMENT,
            product_id INT UNSIGNED NOT NULL,
            region_id INT NOT NULL,
            price_purchase INT NULL,
            price_selling INT NULL,
            price_discount INT NULL,
            PRIMARY KEY ( id )
            )";

        try {
            $this->pdo->exec($sql);
        } catch(PDOException $e) {
            echo 'Ошибка создания таблицы: ' . $e->getMessage();
        }

        return true;

    }

    public function addPriceProduct(int $product_id, int $region_id, int $price_purchase = null, int $price_selling = null, int $price_discount = null): string
    {

        try {

            $sql = "INSERT INTO price_product (product_id, region_id, price_purchase, price_selling, price_discount) VALUES (?, ?, ?, ?, ?)";

            $stmt = $this->pdo->prepare($sql);
            if( !$stmt->execute([$product_id, $region_id, $price_purchase, $price_selling, $price_discount])) {
                throw new Exception('Error Product added');
            }

            return 'Added success';

        } catch ( \Exception $e) {
            return  $e->getMessage();
        }

    }

    public function updatePrice(int $product_id, int $region_id, string $kind_of_price, int $price_value): string
    {
        try {

            $sql = "UPDATE price_product SET {$kind_of_price}=? WHERE product_id=? AND region_id=?";
            $stmt = $this->pdo->prepare($sql);

            if( !$stmt->execute([$price_value, $product_id, $region_id]) ) {
                throw new Exception('Error update price');
            }

            return 'Update success';

        } catch ( \Exception $e) {
            return  $e->getMessage();
        }

    }

    public function issetThisProductInDb(int $product_id, int $region_id)
    {
        $sql = "SELECT * FROM price_product WHERE product_id = $product_id AND region_id = $region_id";
        return $this->pdo->query($sql)->fetch(\PDO::FETCH_ASSOC);
    }
}
