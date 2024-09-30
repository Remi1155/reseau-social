<?php
session_start();
if (!isset($_SESSION['id_compte'])) {
    header('Location: ../index.php');
    exit();
}

// Connexion à la base de données
require_once '../config/config.php';

// Création d'une publication
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publication'])) {
    $contenu = $_POST['contenu'];
    $stmt = $pdo->prepare("INSERT INTO publication (id_compte, contenu) VALUES (?, ?)");
    $stmt->execute([$_SESSION['id_compte'], $contenu]);
}

// Récupération des publications avec le nombre de réactions
$publications = $pdo->query("
    SELECT p.id_publication, p.contenu, p.date_heure, p.id_compte, c.nom, c.prenom,
           COUNT(r.id_reaction) as reactions_count, 
           (SELECT COUNT(*) FROM comments WHERE id_publication = p.id_publication) as comments_count
    FROM publication p
    JOIN compte c ON p.id_compte = c.id
    LEFT JOIN reaction_publication r ON p.id_publication = r.id_publication
    GROUP BY p.id_publication
    ORDER BY p.date_heure DESC
");


// Récupération de tous les utilisateurs
$users = $pdo->query("SELECT nom, prenom FROM compte");

?>


<!DOCTYPE html>
<html>

<head>
    <title>Accueil</title>
    <link rel="stylesheet" href="../styles/output.css">
</head>

<body class="text-xl">
    <div id="container">
        <header class="w-full h-[100px] bg-blue-500 flex items-center justify-between px-12 text-3xl fixed">
            <div>Bienvenue, <?php echo $_SESSION['prenom'] . ' ' . $_SESSION['nom']; ?></div>
            <a href="./logout.php" class="mt-4 inline-block bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 text-sm rounded transition duration-300 ease-in-out transform hover:-translate-y-0.5">
                Déconnecter
            </a>
        </header>

        <main class="w-full flex px-12 bg-gray-200 pt-8  pt-[120px]">
            <!-- Partie gauche -->
            <div class=" w-1/5 min-h-screen bg-gray-50 border border-gray-200 shadow-lg rounded-lg p-4">
                <h2 class="text-2xl font-extrabold text-gray-800 mb-4">Liste des amis:</h2>
                <ul class="list-disc list-inside pl-5 space-y-3">
                    <?php foreach ($users as $user):
                        if ($user['nom'] != $_SESSION['nom']): ?>
                            <li class="text-lg text-gray-700 font-medium">
                                <?php echo htmlspecialchars($user['prenom']) . ' ' . htmlspecialchars($user['nom']); ?>
                            </li>
                    <?php endif;
                    endforeach; ?>
                </ul>
            </div>

            <!-- Partie principale -->
            <div class="w-3/5 mx-4 bg-gray-300 rounded-lg p-4">
                <div class="items-center bg-gray-300 w-full">
                    <div class="bg-gray-300 text-sm mb-4">A quoi pensez-vous ?</div>

                    <!-- Formulaire de publication -->
                    <form method="post" action="" class=" flex items-center w-full mx-auto">
                        <textarea
                            name="contenu"
                            placeholder="Quoi de neuf ?"
                            required
                            class="w-full border rounded-lg  mr-4 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none h-20">
                        </textarea>
                        <button
                            type="submit"
                            name="publication"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold px-8 py-2 rounded text-xs ">
                            Publier
                        </button>
                    </form>
                </div>


                <div class="w-full">
                    <!-- Liste des publications -->
                    <div class="bg-gray-300 text-sm mt-12 mb-4">Publications récentes:</div>
                    <?php
                    foreach ($publications as $publication) {

                        // Vérifier si l'utilisateur a déjà réagi à cette publication
                        $stmt = $pdo->prepare("SELECT type FROM reaction_publication WHERE id_publication = ? AND id_compte = ?");
                        $stmt->execute([$publication['id_publication'], $_SESSION['id_compte']]);
                        $userReaction = $stmt->fetchColumn();
                    ?>

                        <div class=" mb-8 p-4 bg-white rounded-lg">
                            <!-- Utilisateur qui publie -->
                            <div class="text-3xl flex items-center">
                                <img src="../img/personeAnonyme2.png" alt="Image de l'Utilisateur" class="h-16 w-16">
                                <?php echo htmlspecialchars($publication['prenom'] . ' ' . $publication['nom']); ?>
                            </div>
                            <!-- Date de publication -->
                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($publication['date_heure']); ?></div>

                            <div class="bg-gray-400 w-full p-4 pb-0 mb-4 rounded-lg">
                                <div class="flex justify-between">
                                    <!-- Contenu de la publication -->
                                    <div class="mb-6"><?php echo nl2br(htmlspecialchars($publication['contenu'])); ?></div>

                                    <!-- Suppression de la publication -->
                                    <!-- Si l'utilisateur est l'auteur de la publication, afficher un lien pour la supprimer -->
                                    <?php if ($publication['id_compte'] == $_SESSION['id_compte']) { ?>
                                        <form method="post" action="./delete_publication.php" class="ml-8">
                                            <input type="hidden" name="id_publication" value="<?php echo $publication['id_publication']; ?>">
                                            <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?');">
                                                <svg fill="#000000" width="24px" height="24px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M22,5H17V2a1,1,0,0,0-1-1H8A1,1,0,0,0,7,2V5H2A1,1,0,0,0,2,7H3.117L5.008,22.124A1,1,0,0,0,6,23H18a1,1,0,0,0,.992-.876L20.883,7H22a1,1,0,0,0,0-2ZM9,3h6V5H9Zm8.117,18H6.883L5.133,7H18.867Z" />
                                                </svg>
                                            </button>
                                        </form>
                                    <?php } ?>
                                </div>

                                <!-- Bouttons de reactions et affichage des commentaires -->
                                <div class="flex justify-around border-t border-1 border-solid border-gray-600 py-1">
                                    <div class="bg-gray-300 flex w-fit px-4 rounded">
                                        <!-- Nombre de reactions -->
                                        <div class="mr-4"><?php echo $publication['reactions_count']; ?></div>

                                        <!-- Boutons de réactions -->
                                        <form method="post" action="../react_pub/react.php" id="reactionForm<?php echo $publication['id_publication']; ?>">
                                            <input type="hidden" name="id_publication" value="<?php echo $publication['id_publication']; ?>">

                                            <label class="inline-flex items-center ml-2.5">
                                                <input type="radio" name="reaction" value="j'aime" class="mr-1"
                                                    <?php if ($userReaction == "j'aime") echo 'checked'; ?>
                                                    onchange="submitReaction(<?php echo $publication['id_publication']; ?>)">
                                                <svg role="img" xmlns="http://www.w3.org/2000/svg" width="22px" height="22px" viewBox="0 0 24 24" aria-labelledby="thumbUpIconTitle thumbUpIconDesc" stroke="#2329D6" stroke-width="1" stroke-linecap="square" stroke-linejoin="miter" fill="none" color="#2329D6">
                                                    <title id="thumbUpIconTitle">Thumb Up</title>
                                                    <desc id="thumbUpIconDesc">Icon of a a hand with a thumb pointing up</desc>
                                                    <path d="M8,8.73984815 C8,8.26242561 8.17078432,7.80075162 8.4814868,7.43826541 L13.2723931,1.84887469 C13.7000127,1.34998522 14.4122932,1.20614658 15,1.5 C15.5737957,1.78689785 15.849314,2.45205792 15.6464466,3.06066017 L14,8 L18.6035746,8 C18.7235578,8 18.8432976,8.01079693 18.9613454,8.03226018 C20.0480981,8.22985158 20.7689058,9.27101818 20.5713144,10.3577709 L19.2985871,17.3577709 C19.1256814,18.3087523 18.2974196,19 17.3308473,19 L10,19 C8.8954305,19 8,18.1045695 8,17 L8,8.73984815 Z" />
                                                    <path d="M4,18 L4,9" />
                                                </svg>
                                            </label>

                                            <label class="inline-flex items-center ml-2.5">
                                                <input type="radio" name="reaction" value="j'adore" class="mr-1"
                                                    <?php if ($userReaction == "j'adore") echo 'checked'; ?>
                                                    onchange="submitReaction(<?php echo $publication['id_publication']; ?>)">
                                                <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                                    width="18px" height="18px" viewBox="0 0 128 128" enable-background="new 0 0 128 128" xml:space="preserve">
                                                    <g id="Heart">
                                                        <g>
                                                            <path d="M128,36c0-19.883-16.117-36-36-36C80.621,0,70.598,5.383,64,13.625C57.402,5.383,47.379,0,36,0C16.117,0,0,16.117,0,36
			                                                    c0,0.398,0.105,0.773,0.117,1.172H0C0,74.078,64,128,64,128s64-53.922,64-90.828h-0.117C127.895,36.773,128,36.398,128,36z
			                                                     M119.887,36.938l-0.051,3.172c-2.652,24.742-37.203,60.523-55.84,77.273c-18.5-16.617-52.695-52-55.773-76.742l-0.109-3.703
			                                                    C8.102,36.523,8.063,36.109,8,35.656C8.188,20.375,20.676,8,36,8c8.422,0,16.352,3.875,21.754,10.625L64,26.43l6.246-7.805
			                                                    C75.648,11.875,83.578,8,92,8c15.324,0,27.813,12.375,27.996,27.656C119.941,36.078,119.898,36.5,119.887,36.938z" />
                                                        </g>
                                                    </g>
                                                </svg>

                                            </label>

                                            <label class="inline-flex items-center ml-2.5">
                                                <input type="radio" name="reaction" value="haha" class="mr-1"
                                                    <?php if ($userReaction == "haha") echo 'checked'; ?>
                                                    onchange="submitReaction(<?php echo $publication['id_publication']; ?>)">
                                                <svg height="20px" id="svg8" version="1.1" viewBox="0 0 16.110678 16.110678" width="20px" xmlns="http://www.w3.org/2000/svg" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" xmlns:svg="http://www.w3.org/2000/svg">
                                                    <defs id="defs2" />
                                                    <g id="layer1" transform="translate(-18.473866,-280.4638)">
                                                        <circle cx="26.529205" cy="288.51913" id="circle2488" r="8.0553389" style="opacity:1;fill:#ffd42a;fill-opacity:1;stroke:none;stroke-width:0.01055691;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" />
                                                        <path d="m 21.061395,282.61394 a 8.055339,8.055339 0 0 0 -2.587439,5.90506 8.055339,8.055339 0 0 0 8.055322,8.05533 8.055339,8.055339 0 0 0 7.004741,-4.08761 8.055339,8.055339 0 0 1 -0.460954,0.40463 8.055339,8.055339 0 0 1 -0.654741,0.46819 8.055339,8.055339 0 0 1 -0.698665,0.401 8.055339,8.055339 0 0 1 -0.734839,0.32867 8.055339,8.055339 0 0 1 -0.763777,0.25424 8.055339,8.055339 0 0 1 -0.785999,0.17622 8.055339,8.055339 0 0 1 -0.799435,0.0971 8.055339,8.055339 0 0 1 -0.569474,0.0202 8.055339,8.055339 0 0 1 -0.804085,-0.0403 8.055339,8.055339 0 0 1 -0.796334,-0.12041 8.055339,8.055339 0 0 1 -0.780314,-0.19947 8.055339,8.055339 0 0 1 -0.756543,-0.27595 8.055339,8.055339 0 0 1 -0.72502,-0.35037 8.055339,8.055339 0 0 1 -0.686264,-0.42064 8.055339,8.055339 0 0 1 -0.640787,-0.48731 8.055339,8.055339 0 0 1 -0.589112,-0.5488 8.055339,8.055339 0 0 1 -0.53175,-0.60514 8.055339,8.055339 0 0 1 -0.468189,-0.65474 8.055339,8.055339 0 0 1 -0.400492,-0.69866 8.055339,8.055339 0 0 1 -0.329179,-0.73484 8.055339,8.055339 0 0 1 -0.253731,-0.76429 8.055339,8.055339 0 0 1 -0.176217,-0.78549 8.055339,8.055339 0 0 1 -0.09715,-0.79943 8.055339,8.055339 0 0 1 -0.02015,-0.56948 8.055339,8.055339 0 0 1 0.04031,-0.8046 8.055339,8.055339 0 0 1 0.11989,-0.79581 8.055339,8.055339 0 0 1 0.199471,-0.78032 8.055339,8.055339 0 0 1 0.275952,-0.75654 8.055339,8.055339 0 0 1 0.350366,-0.72502 8.055339,8.055339 0 0 1 0.0646,-0.10542 z" id="path2490" style="opacity:1;fill:#ffbc2a;fill-opacity:1;stroke:none;stroke-width:0.01055691;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" />
                                                        <path d="m 31.899322,288.72705 a 5.3701167,5.3701167 0 0 1 -2.685058,4.65066 5.3701167,5.3701167 0 0 1 -5.370117,0 5.3701167,5.3701167 0 0 1 -2.685058,-4.65066 l 5.370116,0 z" id="path2504" style="opacity:1;fill:#1a1a1a;fill-opacity:1;stroke:none;stroke-width:0.01502244;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" />
                                                        <path d="m 87.052734,32.732422 h -7.082031 a 20.296504,20.296504 0 0 0 10.148438,17.576172 20.296504,20.296504 0 0 0 20.296879,0 20.296504,20.296504 0 0 0 10.14843,-17.576172 h -7.08203 v 3.589844 c 0,0.643035 -0.12896,1.254709 -0.36328,1.810546 -0.23432,0.555838 -0.57454,1.055012 -0.99414,1.47461 -0.4196,0.419598 -0.91877,0.757868 -1.47461,0.992187 -0.55584,0.23432 -1.16751,0.365235 -1.81055,0.365235 H 91.697266 c -0.643036,0 -1.25471,-0.130915 -1.810547,-0.365235 C 89.330882,40.36529 88.829754,40.02702 88.410156,39.607422 87.990558,39.187824 87.652288,38.68865 87.417969,38.132812 87.18365,37.576975 87.052734,36.965301 87.052734,36.322266 Z" id="path2511" style="opacity:1;fill:#1a1a1a;fill-opacity:1;stroke:none;stroke-width:0.05677772;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" transform="matrix(0.26458334,0,0,0.26458334,0,280.06665)" />
                                                        <path d="m 87.052734,32.732422 v 3.589844 c 0,0.643035 0.130916,1.254709 0.365235,1.810546 0.234319,0.555838 0.572589,1.055012 0.992187,1.47461 0.419598,0.419598 0.920726,0.757868 1.476563,0.992187 0.555837,0.23432 1.167511,0.365235 1.810547,0.365235 h 17.142574 c 0.64304,0 1.25471,-0.130915 1.81055,-0.365235 0.55584,-0.234319 1.05501,-0.572589 1.47461,-0.992187 0.4196,-0.419598 0.75982,-0.918772 0.99414,-1.47461 0.23432,-0.555837 0.36328,-1.167511 0.36328,-1.810546 v -3.589844 h -13.21484 z" id="path2506" style="opacity:1;fill:#ffffff;fill-opacity:1;stroke:none;stroke-width:0.05677772;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" transform="matrix(0.26458334,0,0,0.26458334,0,280.06665)" />
                                                        <path d="m 28.218075,293.21433 a 1.7347691,1.4991172 0 0 0 -0.01753,-0.0685 1.7347691,1.4991172 0 0 0 -0.05488,-0.14228 1.7347691,1.4991172 0 0 0 -0.07051,-0.13668 1.7347691,1.4991172 0 0 0 -0.08652,-0.13009 1.7347691,1.4991172 0 0 0 -0.100999,-0.12187 1.7347691,1.4991172 0 0 0 -0.114338,-0.11264 1.7347691,1.4991172 0 0 0 -0.126914,-0.1021 1.7347691,1.4991172 0 0 0 -0.137968,-0.0906 1.7347691,1.4991172 0 0 0 -0.147876,-0.0784 1.7347691,1.4991172 0 0 0 -0.15588,-0.0652 1.7347691,1.4991172 0 0 0 -0.163122,-0.0514 1.7347691,1.4991172 0 0 0 -0.168077,-0.0369 1.7347691,1.4991172 0 0 0 -0.171504,-0.0224 1.7347691,1.4991172 0 0 0 -0.173032,-0.008 1.7347691,1.4991172 0 0 0 -0.122722,0.004 1.7347691,1.4991172 0 0 0 -0.172269,0.0181 1.7347691,1.4991172 0 0 0 -0.169219,0.0329 1.7347691,1.4991172 0 0 0 -0.164266,0.0471 1.7347691,1.4991172 0 0 0 -0.158548,0.0613 1.7347691,1.4991172 0 0 0 -0.150163,0.0744 1.7347691,1.4991172 0 0 0 -0.141016,0.0873 1.7347691,1.4991172 0 0 0 -0.130345,0.0988 1.7347691,1.4991172 0 0 0 -0.118149,0.10968 1.7347691,1.4991172 0 0 0 -0.10519,0.11955 1.7347691,1.4991172 0 0 0 -0.09033,0.12746 1.7347691,1.4991172 0 0 0 -0.07546,0.13503 1.7347691,1.4991172 0 0 0 -0.05946,0.14064 1.7347691,1.4991172 0 0 0 -0.03316,0.11198 3.9605801,3.4225729 0 0 0 3.379438,-10e-4 z" id="path2518" style="opacity:1;fill:#ff5555;fill-opacity:1;stroke:none;stroke-width:0.01029941;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" />
                                                        <path d="m 22.007835,283.55326 a 0.50005,0.50005 0 0 0 -0.109554,0.98444 c -0.10682,-0.0286 -0.01909,-0.005 0.04289,0.0274 0.06198,0.0323 0.147512,0.0802 0.248047,0.13695 0.201068,0.11344 0.458607,0.26161 0.709001,0.40979 0.13643,0.0807 0.142837,0.086 0.26665,0.1602 -0.123837,0.0742 -0.130188,0.0794 -0.26665,0.16019 -0.250394,0.14818 -0.507933,0.29895 -0.709001,0.41238 -0.100535,0.0567 -0.186069,0.10414 -0.248047,0.13643 -0.06198,0.0323 -0.14971,0.0561 -0.04289,0.0274 a 0.50005,0.50005 0 1 0 0.257865,0.9648 c 0.153855,-0.0412 0.166842,-0.0641 0.24598,-0.10542 0.07914,-0.0412 0.173467,-0.0926 0.27957,-0.15245 0.212207,-0.11972 0.470899,-0.2716 0.724503,-0.42168 0.507211,-0.30016 0.990121,-0.59376 0.990121,-0.59376 a 0.50043518,0.50043518 0 0 0 5.29e-4,-5.3e-4 0.50005,0.50005 0 0 0 0.0031,-0.002 0.50043518,0.50043518 0 0 0 0.0088,-0.006 0.50005,0.50005 0 0 0 -0.0801,-0.88729 c -0.05947,-0.0359 -0.449851,-0.27224 -0.922424,-0.5519 -0.253604,-0.15009 -0.512296,-0.30196 -0.724503,-0.42168 -0.106103,-0.0599 -0.200431,-0.11123 -0.27957,-0.15245 -0.07914,-0.0412 -0.09213,-0.0663 -0.24598,-0.10748 a 0.50005,0.50005 0 0 0 -0.148311,-0.0176 z" id="path2520" style="color:#000000;font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:medium;line-height:normal;font-family:sans-serif;font-variant-ligatures:normal;font-variant-position:normal;font-variant-caps:normal;font-variant-numeric:normal;font-variant-alternates:normal;font-feature-settings:normal;text-indent:0;text-align:start;text-decoration:none;text-decoration-line:none;text-decoration-style:solid;text-decoration-color:#000000;letter-spacing:normal;word-spacing:normal;text-transform:none;writing-mode:lr-tb;direction:ltr;text-orientation:mixed;dominant-baseline:auto;baseline-shift:baseline;text-anchor:start;white-space:normal;shape-padding:0;clip-rule:nonzero;display:inline;overflow:visible;visibility:visible;opacity:1;isolation:auto;mix-blend-mode:normal;color-interpolation:sRGB;color-interpolation-filters:linearRGB;solid-color:#000000;solid-opacity:1;vector-effect:none;fill:#1a1a1a;fill-opacity:1;fill-rule:nonzero;stroke:none;stroke-width:1;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;color-rendering:auto;image-rendering:auto;shape-rendering:auto;text-rendering:auto;enable-background:accumulate" />
                                                        <path d="m 31.037258,283.55326 a 0.50005,0.50005 0 0 0 -0.132808,0.0176 c -0.153855,0.0412 -0.167359,0.0663 -0.246496,0.10749 -0.07914,0.0412 -0.17295,0.0926 -0.279053,0.15244 -0.212207,0.11972 -0.470898,0.27159 -0.724504,0.42168 -0.480531,0.28437 -0.891505,0.53289 -0.938445,0.56121 a 0.50005,0.50005 0 0 0 -0.11317,0.84284 0.50005,0.50005 0 0 0 0.03565,0.0269 0.50043518,0.50043518 0 0 0 0.01241,0.008 0.50005,0.50005 0 0 0 0.01291,0.009 c 0,0 0.483428,0.2936 0.990637,0.59376 0.253605,0.15008 0.512296,0.30196 0.724503,0.42168 0.106103,0.0598 0.199914,0.11122 0.279053,0.15245 0.07914,0.0412 0.09264,0.0642 0.246497,0.10542 a 0.50005,0.50005 0 1 0 0.272335,-0.9617 c 0.04379,0.0114 -0.0034,-0.003 -0.05581,-0.0305 -0.06198,-0.0323 -0.149579,-0.0797 -0.250116,-0.13643 -0.201067,-0.11343 -0.456538,-0.26419 -0.706932,-0.41237 -0.136499,-0.0808 -0.143092,-0.0859 -0.267168,-0.1602 0.124052,-0.0742 0.130701,-0.0794 0.267168,-0.1602 0.250394,-0.14818 0.505865,-0.29635 0.706932,-0.40979 0.100537,-0.0567 0.188138,-0.10466 0.250116,-0.13694 0.06198,-0.0323 0.149709,-0.056 0.04289,-0.0274 a 0.50005,0.50005 0 0 0 -0.126609,-0.98444 z" id="path2524" style="color:#000000;font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:medium;line-height:normal;font-family:sans-serif;font-variant-ligatures:normal;font-variant-position:normal;font-variant-caps:normal;font-variant-numeric:normal;font-variant-alternates:normal;font-feature-settings:normal;text-indent:0;text-align:start;text-decoration:none;text-decoration-line:none;text-decoration-style:solid;text-decoration-color:#000000;letter-spacing:normal;word-spacing:normal;text-transform:none;writing-mode:lr-tb;direction:ltr;text-orientation:mixed;dominant-baseline:auto;baseline-shift:baseline;text-anchor:start;white-space:normal;shape-padding:0;clip-rule:nonzero;display:inline;overflow:visible;visibility:visible;opacity:1;isolation:auto;mix-blend-mode:normal;color-interpolation:sRGB;color-interpolation-filters:linearRGB;solid-color:#000000;solid-opacity:1;vector-effect:none;fill:#1a1a1a;fill-opacity:1;fill-rule:nonzero;stroke:none;stroke-width:1;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;color-rendering:auto;image-rendering:auto;shape-rendering:auto;text-rendering:auto;enable-background:accumulate" />
                                                    </g>
                                                </svg>
                                            </label>

                                            <label class="inline-flex items-center ml-2.5">
                                                <input type="radio" name="reaction" value="triste" class="mr-1"
                                                    <?php if ($userReaction == "triste") echo 'checked'; ?>
                                                    onchange="submitReaction(<?php echo $publication['id_publication']; ?>)">
                                                <svg height="22px" id="Sad" style="enable-background:new 0 0 32 32;" version="1.1" viewBox="0 0 32 32" width="32px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                    <linearGradient gradientUnits="userSpaceOnUse" id="SVGID_1_" x1="24.001" x2="7.9984" y1="2.1416" y2="29.8589">
                                                        <stop offset="0" style="stop-color:#FFE254" />
                                                        <stop offset="1" style="stop-color:#FFB255" />
                                                    </linearGradient>
                                                    <circle cx="16" cy="16" r="16" style="fill:url(#SVGID_1_);" />
                                                    <circle cx="9" cy="16" r="2" style="fill:#212731;" />
                                                    <circle cx="23" cy="16" r="2" style="fill:#212731;" />
                                                    <path d="M21,24c-2.211-2.212-7.789-2.212-10,0" style="fill:none;stroke:#212731;stroke-width:1.2804;stroke-miterlimit:10;" />
                                                    <path d="M25,27c0,1.104-0.896,2-2,2s-2-0.896-2-2s2-4,2-4S25,25.896,25,27z" style="fill:#2667C6;" />
                                                    <path d="M27,14c-1-2-3-3-5-3" style="fill:none;stroke:#212731;stroke-miterlimit:10;" />
                                                    <path d="M5,14c1-2,3-3,5-3" style="fill:none;stroke:#212731;stroke-miterlimit:10;" />
                                                </svg>

                                            </label>
                                        </form>
                                    </div>

                                    <!-- Liste des commentaires -->
                                    <div class="bg-gray-300 flex ml-8 px-4 rounded">
                                        <!-- Nombre de commentaires -->
                                        <div class="mr-4"><?php echo $publication['comments_count']; ?></div>
                                        <!-- Lien vers toutes les commentaires -->
                                        <a href="../comment/show_comments.php?id_publication=<?php echo $publication['id_publication']; ?>" class="">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24">
                                                <path d="M8.2881437,19.1950792 C8.38869181,19.1783212 8.49195996,19.1926955 8.58410926,19.2362761 C9.64260561,19.7368747 10.8021412,20 12,20 C16.418278,20 20,16.418278 20,12 C20,7.581722 16.418278,4 12,4 C7.581722,4 4,7.581722 4,12 C4,13.7069096 4.53528582,15.3318588 5.51454846,16.6849571 C5.62010923,16.830816 5.63909672,17.022166 5.5642591,17.1859256 L4.34581002,19.8521348 L8.2881437,19.1950792 Z M3.58219949,20.993197 C3.18698783,21.0590656 2.87870208,20.6565881 3.04523765,20.2921751 L4.53592782,17.0302482 C3.54143337,15.5576047 3,13.818993 3,12 C3,7.02943725 7.02943725,3 12,3 C16.9705627,3 21,7.02943725 21,12 C21,16.9705627 16.9705627,21 12,21 C10.707529,21 9.4528641,20.727055 8.30053434,20.2068078 L3.58219949,20.993197 Z" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Champ pour commenter -->
                            <form method="post" action="../comment/comment.php" class="w-full">
                                <input name="commentaire" placeholder="Ajouter un commentaire" class="text-base border border-1 border-solid border-gray-600 p-2 rounded-md w-4/5"></input>
                                <input type="hidden" name="id_publication" value="<?php echo $publication['id_publication']; ?>">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold  px-4 py-2 rounded text-sm ml-2">Commenter</button>
                            </form>

                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Partie droite -->
            <div class="w-1/5 min-h-screen bg-white rounded-lg p-4">
                Menu
            </div>
        </main>
    </div>

    <script>
        function submitReaction(publicationId) {
            document.getElementById('reactionForm' + publicationId).submit();
        }
    </script>


</body>

</html>