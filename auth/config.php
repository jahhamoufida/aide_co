<?php
class config {

    private static ?PDO $pdo = null; // Instance PDO unique

    public static function getConnexion(): PDO {
        if (self::$pdo === null) {
            try {
                // Connexion à la base 'evenements'
                self::$pdo = new PDO(
                    "mysql:host=localhost;dbname=evenements;charset=utf8mb4",
                    "root",
                    "" // mot de passe XAMPP vide par défaut
                );

                // Mode d'erreur PDO : exceptions
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            } catch (PDOException $e) {
                // En cas d'erreur de connexion
                die("Erreur de connexion à la base : " . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}
?>
