<?php
    require_once __DIR__ . "/../../config/DataBase.php";

    class AddTeacherModel {
        private $conn;
        private $table = "utilisateur";

        public function __construct() {
            $database = new Database();
            $this->conn = $database->getConnection();
        }

        public function addteacher($nom, $postnom, $prenom, $email, $matricule, $password, $role, $grade) {
            try {
                // Démarrer une transaction
                $this->conn->beginTransaction();

                // Hacher le mot de passe
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Insérer le professeur
                $query = "INSERT INTO " . $this->table . "(nom, post_nom, prenom, email, matricule, mot_de_passe, role) VALUES (:nom, :postnom, :prenom, :email, :matricule, :password, :role)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":nom", $nom);
                $stmt->bindParam(":postnom", $postnom);
                $stmt->bindParam(":prenom", $prenom);
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":matricule", $matricule);
                $stmt->bindParam(":password", $hashed_password);
                $stmt->bindParam(":role", $role);

                if (!$stmt->execute()) {
                    throw new Exception("Échec de l'insertion de l'enseignat.");
                }

                // Récupérer l'ID du professeur ajouté
                $id_professeur = $this->conn->lastInsertId();

                // Insérer dans la table professeur
                $query_promotion = "INSERT INTO professeur (id_utilisateur, grade) VALUES (:id_utilisateur, :grade)";
                $stmt_promotion = $this->conn->prepare($query_promotion);
                $stmt_promotion->bindParam(":id_utilisateur", $id_professeur);
                $stmt_promotion->bindParam(":grade", $grade);

                if (!$stmt_promotion->execute()) {
                    throw new Exception("Échec de l'insertion de la promotion.");
                }

                // Valider la transaction
                $this->conn->commit();
                return true;

            } catch (Exception $e) {
                // Annuler la transaction en cas d'erreur
                $this->conn->rollBack();
                return ["error" => $e->getMessage()];
            }
        }
    }
?>

