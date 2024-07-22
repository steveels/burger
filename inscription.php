<?php
require 'bdd.php';
$db = Database::connect();
session_start();



if (isset($_POST['inscription']) && !empty(trim($_POST['nom'])) && filter_var(trim($_POST['mail']),FILTER_VALIDATE_EMAIL) && !empty(trim($_POST['mdp'])))
{ 
    $mail = htmlspecialchars($_POST['mail']);
   

    $stmt = $db->prepare('SELECT id FROM utilisateurs WHERE email = :mail');
    $stmt->execute(['mail' => $mail]);
    if($stmt->fetch()){
         $error = 'Email déjà utilisé';
         header('Location: inscription.php?error='.$error);
        
    }
    else{
        $nom = htmlspecialchars($_POST['nom']);
        $mdp = htmlspecialchars($_POST['mdp']);
        $mdpHash = password_hash($mdp, PASSWORD_DEFAULT);
        $role = 'user';

    $stmt = $db->prepare('INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (:nom, :mail, :mdp, :role)');
    $success = $stmt->execute(['nom' => $nom, 'mail' => $mail, 'mdp' => $mdpHash, 'role' => $role]);
    
    if($success){
        $msg = "Compte créé avec succès !";
        header('Location: inscription.php?msg='.$msg);
    

   

    }else{
        $err = 'Erreur lors de l\'inscription';
        header('Location: inscription.php?err=' . $err);
    }
    }

}

if (isset($_POST['connexion']) && filter_var(trim($_POST['mail']),FILTER_VALIDATE_EMAIL) && !empty(trim($_POST['mdp'])))
{
    $mail = htmlspecialchars($_POST['mail']);
    $mdp = htmlspecialchars($_POST['mdp']);

    // Vérifier si l'utilisateur existe en bdd avec le mail
    $stmt = $db -> prepare('SELECT * FROM utilisateurs WHERE email = :email');
    $stmt -> execute(['email' => $mail]);
    $user = $stmt -> fetch(PDO::FETCH_ASSOC);

    //  Vérifier si l'utilisateur existe et si le mot de passe est correct 
    if($user && password_verify($mdp, $user['mot_de_passe']))
    {
        
        $_SESSION['userId'] = $user['id'];
        $_SESSION['userRole']= $user['role'];

        // permet de ne pas perdre le panier en cas de connexion
        $updatePanier = $db-> prepare('UPDATE panier SET user_id = :userId WHERE userTemp = :userTemp');
        $updatePanier->execute(['userId' => $user['id'], 'userTemp' => $_SESSION['userTemp']]);

        
       header('Location: index.php');
       
    }
    else
    {
        $errCo = 'Mot de passe ou email incorrect';
        header('Location: inscription.php?errCo=' . $errCo);
    }
}

Database::disconnect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration and Login Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css">
  
    <style>
     
        .container-account {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .vertical-line {
           
            border-right: 1px solid black;
            margin-right:50px
           
           
        }
    </style>
</head>
<body>
     <?php if(isset($_GET['error'])) { ?>
    <div class="alert alert-danger" role="alert" style="text-align:center">
        <?= $_GET['error']?>
    </div>
    <?php } ?>

     <?php if(isset($_GET['err'])) { ?>
    <div class="alert alert-danger" role="alert" style="text-align:center">
        <?= $_GET['err']?>
    </div>
    <?php } ?>
    <?php if(isset($_GET['errCo'])) { ?>
    <div class="alert alert-danger" role="alert" style="text-align:center">
    <?= $_GET['errCo']?>
    </div>
    <?php } ?>
    <?php if(isset($_GET['msg'])) { ?>
    <div class="alert alert-success" role="alert"style="text-align:center">
    <?=
         $_GET['msg'] ;
        ?>
    </div>

    <?php } ?>
    <div class="container-account">
 
        <div class="row">
            <div class="col-md-4">
                <h3>Registration</h3>
                <form action="inscription.php" method="post">
                    <div class="mb-3">
                        <label for="regName" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="regName" placeholder="Enter your name" name="nom" required>
                    </div>
                    <div class="mb-3">
                        <label for="regEmail" class="form-label">Email </label>
                        <input type="email" class="form-control" id="regEmail" placeholder="Enter your email" name="mail" required>
                    </div>
                    <div class="mb-3">
                        <label for="regPassword" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="regPassword" placeholder="Enter a password"  name="mdp" required>
                    </div>
                    <button type="submit" class="btn btn-primary" name="inscription" >inscription</button>
                </form>
            </div>
            <div class="col-md-2 vertical-line"></div>
            <div class="col-md-4">
                <h3>Login</h3>
                <form action="inscription.php" method="post">
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">Email </label>
                        <input type="email" class="form-control" id="loginEmail" placeholder="Enter your email" name="mail">
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="loginPassword" placeholder="Enter your password" name="mdp">
                    </div>
                    <button type="submit" class="btn btn-primary" name="connexion">connexion</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>