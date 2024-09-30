<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Connexion à la base de données
    require_once './config/config.php';

    // Récupération des valeurs du formulaire
    $mail = $_POST['mail'];
    $mdp = $_POST['mdp'];

    // Vérification si l'utilisateur existe
    $stmt = $pdo->prepare("SELECT id, nom, prenom FROM compte WHERE mail = ? AND mdp = ?");
    $stmt->execute([$mail, $mdp]);
    $user = $stmt->fetch();

    if ($user) {
        // Stockage de l'utilisateur dans la session et redirection vers la page d'accueil
        $_SESSION['id_compte'] = $user['id'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];

        header('Location: ./others/home.php');
        exit();
    } else {
        $error = "Identifiants incorrects";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="./styles/output.css">
</head>

<body class="min-h-screen flex items-center justify-center bg-gray-100">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-sm">
        <h2 class="text-2xl font-bold mb-6 text-center">Connexion</h2>

        <form method="post" action="" class="space-y-4">
            <div>
                <label for="mail" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="text" id="mail" name="mail" placeholder="Email" required
                    class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="mdp" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                <input type="password" id="mdp" name="mdp" placeholder="Mot de passe" required
                    class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <button type="submit"
                class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Se connecter
            </button>
        </form>

        <p class="mt-4 text-sm text-center">
            Pas encore de compte ?
            <a href="./others/register.php" class="text-indigo-600 hover:underline">Créer un compte</a>.
        </p>

        <?php if (isset($error)): ?>
            <p class="text-red-500 text-sm mt-4 flex justify-center"><?php echo $error; ?></p>
        <?php endif; ?>
    </div>

</body>

</html>