<?php
// =========================================================
// INDEX - ROUTEUR PRINCIPAL DU SITE SUPPORTINI.TN
// Rôle : Contrôleur central (C de MVC)
// =========================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/model/Sujet.php';
require_once __DIR__ . '/model/Commentaire.php';
require_once __DIR__ . '/controller/sujetController.php';
require_once __DIR__ . '/controller/commentaireController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Initialisation des managers (Modèle) ---
$crud            = new SujetCRUD();
$commentaireCrud = new CommentaireCRUD();

// --- Détection de l'action demandée ---
$action = isset($_GET['action']) ? $_GET['action'] : 'front_liste';

$message = "";
$erreur  = "";

// =========================================================
//  FONCTIONS UTILITAIRES
// =========================================================

// POST
function getPost($key, $default = "")
{
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

// GET
function getGet($key, $default = "")
{
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

// =====================================================
// AUTH : table, compte admin et helpers de session
// =====================================================
function ensureUserTable(PDO $pdo)
{
    $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL DEFAULT 'user',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sql);
}

function ensureDefaultAdmin(PDO $pdo)
{
    $check = $pdo->prepare("SELECT COUNT(*) AS total FROM users WHERE username = :username");
    $check->execute([':username' => 'admin']);
    $exists = (int)$check->fetch()['total'];

    if ($exists === 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password_hash, role)
            VALUES (:username, :password_hash, 'admin')
        ");
        $stmt->execute([
            ':username'      => 'admin',
            ':password_hash' => $hash
        ]);
    }
}

function getLoggedUser()
{
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function isAdminUser()
{
    $user = getLoggedUser();
    return $user && isset($user['role']) && $user['role'] === 'admin';
}

function requireAdminAccess($crud)
{
    if (!isAdminUser()) {
        $erreur = "Acces reserve aux administrateurs.";
        afficherListeFront($crud, 1, 10, $erreur, "");
        exit;
    }
}

ensureUserTable($pdoAuth = Config::getConnexion());
ensureDefaultAdmin($pdoAuth);

// =====================================================
// EMAIL SMTP (Gmail)
// =====================================================
function envoyerMailSmtpGmail($to, $subject, $body)
{
    $smtpHost = 'smtp.gmail.com';
    $smtpPort = 465;
    $smtpUser = 'mohamedheni099@gmail.com';
    // L'app-password Gmail comporte des espaces, on les retire pour l'auth
    $smtpPass = str_replace(' ', '', 'clfj zdfi mlwj vxee');

    $timeout = 20;
    $context = stream_context_create([
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
            'allow_self_signed'=> true
        ]
    ]);

    $socket = stream_socket_client("ssl://{$smtpHost}:{$smtpPort}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) {
        return false;
    }

    $read = function() use ($socket) {
        $data = "";
        while ($line = fgets($socket, 515)) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };

    $send = function($cmd) use ($socket, $read) {
        fwrite($socket, $cmd . "\r\n");
        return $read();
    };

    $read(); // Greeting
    $send("EHLO localhost");
    $send("AUTH LOGIN");
    $send(base64_encode($smtpUser));
    $send(base64_encode($smtpPass));

    $send("MAIL FROM:<{$smtpUser}>");
    $send("RCPT TO:<{$to}>");
    $send("DATA");

    $headers  = "From: {$smtpUser}\r\n";
    $headers .= "To: {$to}\r\n";
    $headers .= "Subject: {$subject}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n\r\n";

    $message = $headers . $body . "\r\n.";
    fwrite($socket, $message . "\r\n");
    $read(); // message ack
    $send("QUIT");
    fclose($socket);

    return true;
}

// =====================================================
// FONCTION : Détecter les mots interdits dans un texte
// =====================================================
function contientBadWords($texte)
{
    $badWords = [
        "fuck","shit","bitch","slut","merde","pute","salope",
        "enculé","enculer","ntiri","kleb","zebi","zoby","tfou",
        "ta mere","ta-mere","ta_mere","3asba"
    ];

    $texteMin = strtolower($texte);

    foreach ($badWords as $bad) {
        if (strpos($texteMin, strtolower($bad)) !== false) {
            return true;
        }
    }
    return false;
}

// =========================================================
// 🔥 IA : FONCTION DE GÉNÉRATION AUTOMATIQUE (GEMINI)
// =========================================================
function genererTexteIA($prompt)
{
    // Utilise l'API Gemini (Google)
    $apiKey = "AIzaSyCzGjFOwVspJSigeriRu1iUHMIBk9Mn5qg";

    if (trim($apiKey) == "") {
        return "Erreur : clé API Gemini non configurée.";
    }

    // Plusieurs modèles/versions testés ; ordre du plus robuste/rapide
    $endpoints = [
        "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent",
        "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent",
        "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent",
    ];

    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ]
    ];

    foreach ($endpoints as $baseUrl) {
        $url = $baseUrl . "?key=" . urlencode($apiKey);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $result = curl_exec($ch);

        if ($result === false) {
            curl_close($ch);
            continue; // essayer le modèle suivant
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Certains modèles peuvent renvoyer 503 (overload) ou 404 (non dispo) => on tente le suivant
        if ($statusCode < 200 || $statusCode >= 300) {
            continue;
        }

        $json = json_decode($result, true);

        if (isset($json["candidates"][0]["content"]["parts"][0]["text"])) {
            return $json["candidates"][0]["content"]["parts"][0]["text"];
        }
    }

    return "Erreur IA (tous les modèles ont échoué, réessaie plus tard).";
}















// =========================================================
// FONCTIONS LISTE AVEC PAGINATION
// =========================================================

function afficherListeFront($crud, $page = 1, $parPage = 10, $erreur = "", $message = "")
{
    $page = max(1, (int)$page);

    $totalSujets = $crud->countAll();
    $totalPages  = max(1, ceil($totalSujets / $parPage));

    if ($page > $totalPages) $page = $totalPages;

    $offset = ($page - 1) * $parPage;

    $liste  = $crud->getPage($parPage, $offset);
    $loggedUser = getLoggedUser();

    include __DIR__ . '/view/front_office/listeSujets.php';
}

function afficherListeBack($crud, $page = 1, $parPage = 10, $erreur = "", $message = "")
{
    $page = max(1, (int)$page);

    $totalSujets = $crud->countAll();
    $totalPages  = max(1, ceil($totalSujets / $parPage));

    if ($page > $totalPages) $page = $totalPages;

    $offset = ($page - 1) * $parPage;

    $liste  = $crud->getPage($parPage, $offset);

    include __DIR__ . '/view/back_office/listeSujetsBO.php';
}

// =========================================================
// Fonction upload image
// =========================================================

function uploadImageSujet($inputName, &$erreur)
{
    $imagePath = null;

    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['name'] == "") {
        return null;
    }

    $uploadDir    = __DIR__ . '/uploads/sujets/';
    $uploadWebDir = '/novombre/uploads/sujets/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES[$inputName]['name']);
    $fileTmp  = $_FILES[$inputName]['tmp_name'];

    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($extension, ['jpg','jpeg','png','gif'])) {
        $erreur = "Format d'image non supporté.";
        return null;
    }

    $newName  = time() . "_" . $fileName;
    $fullPath = $uploadDir . $newName;

    if (!move_uploaded_file($fileTmp, $fullPath)) {
        $erreur = "Erreur lors de l'upload.";
        return null;
    }

    return $uploadWebDir . $newName;
}

// =========================================================
// *********  A U T H   (inscription / connexion) *********
// =========================================================

// ---------- Inscription ----------
if ($action == 'signup') {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim(getPost('username'));
        $password = getPost('password');

        if ($username == "" || $password == "") {
            $erreur = "Nom d'utilisateur et mot de passe obligatoires.";
        } else {
            $check = $pdoAuth->prepare("SELECT id FROM users WHERE username = :username");
            $check->execute([':username' => $username]);
            $exists = $check->fetch();

            if ($exists) {
                $erreur = "Ce nom d'utilisateur existe deja.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $insert = $pdoAuth->prepare("
                    INSERT INTO users (username, password_hash, role)
                    VALUES (:username, :password_hash, 'user')
                ");
                $insert->execute([
                    ':username'      => $username,
                    ':password_hash' => $hash
                ]);
                $message = "Compte cree. Connecte-toi pour continuer.";

                // Notification email à l'admin/observateur
                $mailSubject = "Nouvelle inscription : " . $username;
                $mailBody    = "Un nouveau compte vient d'etre cree sur le site.\r\n\r\n"
                             . "Username : " . $username . "\r\n"
                             . "Date : " . date('Y-m-d H:i:s') . "\r\n";
                envoyerMailSmtpGmail("bonour39@gmail.com", $mailSubject, $mailBody);
                envoyerMailSmtpGmail("nheni0777@gmail.com", $mailSubject, $mailBody);
            }
        }
    }

    afficherListeFront($crud, 1, 10, $erreur, $message);
    exit;
}

// ---------- Connexion ----------
if ($action == 'login') {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim(getPost('username'));
        $password = getPost('password');

        if ($username == "" || $password == "") {
            $erreur = "Nom d'utilisateur et mot de passe obligatoires.";
        } else {
            $stmt = $pdoAuth->prepare("
                SELECT id, username, password_hash, role
                FROM users
                WHERE username = :username
                LIMIT 1
            ");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user'] = [
                    'id'       => $user['id'],
                    'username' => $user['username'],
                    'role'     => $user['role']
                ];

                $message = "Connexion reussie. Bienvenue " . $user['username'] . ".";

                if ($user['role'] === 'admin') {
                    header("Location: /novombre/index.php?action=back_liste");
                    exit;
                }
            } else {
                $erreur = "Identifiants invalides.";
            }
        }
    }

    afficherListeFront($crud, 1, 10, $erreur, $message);
    exit;
}

// ---------- Déconnexion ----------
if ($action == 'logout') {
    unset($_SESSION['user']);
    session_regenerate_id(true);
    $message = "Vous etes deconnecte.";
    afficherListeFront($crud, 1, 10, $erreur, $message);
    exit;
}

// Protéger toutes les actions back_ si non admin
if (strpos($action, 'back_') === 0) {
    requireAdminAccess($crud);
}

// =========================================================
// *********  F R O N T    O F F I C E   *********
// =========================================================

// ---------- Ajouter un sujet ----------
if ($action == 'front_ajouter') {

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $titre     = getPost('titre');
        $categorie = getPost('categorie');

        if ($titre == "" || $categorie == "") {
            $erreur = "Titre et catégorie obligatoires.";
        } else {
            $imagePath = uploadImageSujet('image', $erreur);

            if ($erreur == "") {
                $sujet = new Sujet($titre, $categorie);
                $sujet->setImage($imagePath);
                $crud->add($sujet);
                $message = "Sujet ajouté avec succès.";
            }
        }

        afficherListeFront($crud, 1, 10, $erreur, $message);
        exit;
    }
}

// ---------- Détail d'un sujet ----------
if ($action == 'front_detail') {

    $id_sujet = getGet('id_sujet', null);

    // Récupération des flash messages (pour affichage après redirection)
    if (isset($_SESSION['flash_error'])) {
        $erreur = $_SESSION['flash_error'];
        unset($_SESSION['flash_error']);
    }
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
    }

    if ($id_sujet) {

        $sujet_data = $crud->getById($id_sujet);

        if ($sujet_data) {

            $parPageComm = 5;
            $pageComm    = isset($_GET['page_commentaire']) ? (int)$_GET['page_commentaire'] : 1;

            $totalCommentaires = $commentaireCrud->countBySujet($id_sujet);
            $totalPagesComm    = max(1, ceil($totalCommentaires / $parPageComm));

            if ($pageComm > $totalPagesComm) $pageComm = $totalPagesComm;

            $offsetComm = ($pageComm - 1) * $parPageComm;

            $liste_commentaires = $commentaireCrud->getPageBySujet(
                $id_sujet, $parPageComm, $offsetComm
            );
            $loggedUser = getLoggedUser();

            include __DIR__ . '/view/front_office/detailSujet.php';

        } else {
            $erreur = "Sujet introuvable.";
            afficherListeFront($crud, 1, 10, $erreur, $message);
        }
    }
    exit;
}

// ---------- Ajouter un commentaire ----------
if ($action == 'front_ajouter_commentaire') {

    $id_sujet = getPost('id_sujet');
    $contenu  = getPost('contenu');

    if ($id_sujet == "" || $contenu == "") {

        $erreur = "Le commentaire est obligatoire.";

    } elseif (contientBadWords($contenu)) {

        $erreur = "Votre commentaire contient des mots interdits.";

    } else {

        $commentaire = new Commentaire($contenu, $id_sujet);
        $commentaireCrud->add($commentaire);
        $message = "Commentaire ajouté avec succès.";

    }

    $sujet_data = $crud->getById($id_sujet);

    $parPageComm = 5;
    $pageComm    = 1;

    $totalCommentaires = $commentaireCrud->countBySujet($id_sujet);
    $totalPagesComm    = max(1, ceil($totalCommentaires / $parPageComm));

    $liste_commentaires = $commentaireCrud->getPageBySujet(
        $id_sujet, $parPageComm, 0
    );

    include __DIR__ . '/view/front_office/detailSujet.php';
    exit;
}


// =========================================================
// 🔥 ACTION : GÉNÉRER UN COMMENTAIRE AUTOMATIQUE AVEC IA
// =========================================================
if ($action == 'generer_commentaire_ia') {

    $id_sujet = getPost('id_sujet');
    $sujet = $crud->getById($id_sujet);

    if (!$sujet) {
        die("Sujet introuvable.");
    }

    // Prompt IA (titre + catégorie pour contextualiser)
    $titreSujet = isset($sujet['titre']) ? $sujet['titre'] : '';
    $catSujet   = isset($sujet['categorie']) ? $sujet['categorie'] : '';
    $prompt = "Tu es un accompagnant bienveillant. Rédige un commentaire court, empathique et encourageant pour un sujet de forum. "
            . "Titre: " . $titreSujet . ". Catégorie: " . $catSujet . ". "
            . "Garde un ton positif et soutenant, 2-3 phrases maximum.";

    // Génération IA
    $texteIA = genererTexteIA($prompt);

    // Si l'IA renvoie une erreur, ne pas l'enregistrer comme commentaire
    if (strpos($texteIA, "Erreur IA") === 0) {
        $_SESSION['flash_error'] = $texteIA;
    } else {
        $commentaire = new Commentaire($texteIA, $id_sujet);
        $commentaireCrud->add($commentaire);
        $_SESSION['flash_message'] = "Commentaire IA ajouté.";
    }

    // Retour sur page détail
    header("Location: /novombre/index.php?action=front_detail&id_sujet=" . $id_sujet);
    exit;
}













// ---------- Form edit commentaire ----------
if ($action == 'front_edit_form_commentaire') {

    $id_commentaire = getGet('id_commentaire');
    $id_sujet       = getGet('id_sujet');

    if ($id_commentaire != "") {

        $commentaire_data = $commentaireCrud->getById($id_commentaire);

        if ($commentaire_data) {
            include __DIR__ . '/view/front_office/editCommentaire.php';
        } else {
            $erreur = "Commentaire introuvable.";
            afficherListeFront($crud, 1, 10, $erreur, "");
        }
    }
    exit;
}

// ---------- Modifier commentaire ----------
if ($action == 'front_modifier_commentaire') {

    $id_commentaire = getPost('id_commentaire');
    $id_sujet       = getPost('id_sujet');
    $contenu        = getPost('contenu');

    if ($contenu == "") {
        $erreur = "Le commentaire est obligatoire.";
    } elseif (contientBadWords($contenu)) {
        $erreur = "Le commentaire contient des mots interdits.";
    } else {
        $commentaire = new Commentaire($contenu, $id_sujet, $id_commentaire);
        $commentaireCrud->update($commentaire);
        $message = "Commentaire modifié.";
    }

    $sujet_data = $crud->getById($id_sujet);

    $parPageComm = 5;
    $pageComm    = 1;

    $totalCommentaires = $commentaireCrud->countBySujet($id_sujet);
    $totalPagesComm    = max(1, ceil($totalCommentaires / $parPageComm));

    $liste_commentaires = $commentaireCrud->getPageBySujet(
        $id_sujet, $parPageComm, 0
    );

    include __DIR__ . '/view/front_office/detailSujet.php';
    exit;
}

// ---------- Supprimer commentaire ----------
if ($action == 'front_supprimer_commentaire') {

    $id_commentaire = getGet('id_commentaire');
    $id_sujet       = getGet('id_sujet');

    if ($id_commentaire != "") {
        $commentaireCrud->delete($id_commentaire);
        $message = "Commentaire supprimé.";
    }

    $sujet_data = $crud->getById($id_sujet);

    $parPageComm = 5;
    $pageComm    = 1;

    $totalCommentaires = $commentaireCrud->countBySujet($id_sujet);
    $totalPagesComm    = max(1, ceil($totalCommentaires / $parPageComm));

    $liste_commentaires = $commentaireCrud->getPageBySujet(
        $id_sujet, $parPageComm, 0
    );

    include __DIR__ . '/view/front_office/detailSujet.php';
    exit;
}

// =========================================================
// *********  B A C K   O F F I C E   *********
// =========================================================

// ---------- Supprimer sujet ----------
if ($action == 'back_supprimer') {

    $id_sujet = getGet('id_sujet');

    if ($id_sujet != "") {
        $crud->delete($id_sujet);
    }

    header("Location: /novombre/index.php?action=back_liste");
    exit;
}

// ---------- Edit sujet ----------
if ($action == 'back_edit_form') {

    $id_sujet = getGet('id_sujet');

    if ($id_sujet != "") {
        $sujet_data = $crud->getById($id_sujet);

        if ($sujet_data) {
            include __DIR__ . '/view/back_office/editSujet.php';
        } else {
            $erreur = "Sujet introuvable.";
            afficherListeBack($crud, 1, 10, $erreur, "");
        }
    }
    exit;
}

// ---------- Modifier sujet ----------
if ($action == 'back_modifier') {

    $id_sujet  = getPost('id_sujet');
    $titre     = getPost('titre');
    $categorie = getPost('categorie');

    $image       = null;
    $sujetAncien = null;

    if ($id_sujet != "") {
        $sujetAncien = $crud->getById($id_sujet);
        if ($sujetAncien && isset($sujetAncien['image'])) {
            $image = $sujetAncien['image'];
        }
    }

    if ($titre == "" || $categorie == "") {

        $erreur     = "Titre et catégorie obligatoires.";
        $sujet_data = $crud->getById($id_sujet);
        include __DIR__ . '/view/back_office/editSujet.php';
        exit;

    } else {

        $nouvelleImage = uploadImageSujet('image', $erreur);

        if ($erreur == "") {
            if ($nouvelleImage != null) {
                $image = $nouvelleImage;
            }

            $sujet = new Sujet($titre, $categorie, $id_sujet);
            $sujet->setImage($image);
            $crud->update($sujet);

        } else {
            $sujet_data = $crud->getById($id_sujet);
            include __DIR__ . '/view/back_office/editSujet.php';
            exit;
        }
    }

    header("Location: /novombre/index.php?action=back_liste");
    exit;
}

// ---------- Liste BACK ----------
if ($action == 'back_liste') {

    $page = (int)getGet('page', 1);
    afficherListeBack($crud, $page, 10, $erreur, $message);
    exit;
}

// ---------- Liste FRONT ----------
if ($action == 'front_liste') {

    $page = (int)getGet('page', 1);
    afficherListeFront($crud, $page, 10, $erreur, $message);
    exit;
}

// =========================================================
// *****  PAR DÉFAUT : LISTE FRONT *****
// =========================================================

afficherListeFront($crud, 1, 10, $erreur, $message);

?>
