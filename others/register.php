<?php

require_once "../config/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email     =    $_POST["email"];
    $nom       =    $_POST["nom"];
    $prenom    =    $_POST["prenom"];
    $password1 =    $_POST["password-1"];
    $password2 =    $_POST["password-2"];

    if ($password1 === $password2) {
        $sql = "SELECT * FROM compte WHERE mail = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $message_to_show = "L'email déja assigné à un compte";
        } else {
            // $hashedPassword =   password_hash($password1, PASSWORD_DEFAULT);

            $sql_addUser    =   "INSERT INTO compte (nom, prenom, mail, mdp) VALUES (? ,? ,? , ?)";
            $stmt           =   $pdo->prepare($sql_addUser);
            $stmt->execute([$nom, $prenom, $email, $password1]);

            $message_to_show = "Compte créée avec succès.";
        }
    } else {
        $message_to_show = "Mot de passe incorrect";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire d'inscription</title>
    <link rel="stylesheet" href="../styles/output.css">
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-3xl font-bold text-center mb-6">Inscription</h1>

        <form action="register.php" method="POST" class="space-y-4">
            <div>
                <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" id="nom" name="nom" placeholder="Votre nom" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
                <input type="text" id="prenom" name="prenom" placeholder="Votre prénom"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" placeholder="Votre email" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="password-1" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                <input type="password" id="password-1" name="password-1" placeholder="Votre mot de passe" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="password-2" class="block text-sm font-medium text-gray-700">Confirmation mot de passe</label>
                <input type="password" id="password-2" name="password-2" placeholder="Confirmer votre mot de passe" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                S'inscrire
            </button>
        </form>

        <p class="mt-4 text-sm text-center">
            Déjà un compte ?
            <a href="../index.php" class="text-indigo-600 hover:underline">Se connecter</a>
        </p>

        <!-- Message apres soummission du formulaire -->
        <?php
        if (!empty($message_to_show)) {
            if ($message_to_show == "Compte créée avec succès.") {
                echo '<p class = "text-green-500 text-sm mt-4 flex justify-center">' . $message_to_show . "</p>";
            } else {
                echo '<p class = "text-red-500 text-sm mt-4 flex justify-center">' . $message_to_show . "</p>";
            }
        }
        ?>

    </div>
</body>

</html>