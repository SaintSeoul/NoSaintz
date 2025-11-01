<?php

include 'components/connect.php';

session_start();

// If already logged in, redirect according to role
if(isset($_SESSION['role'])){
    if($_SESSION['role'] === 'admin'){
        header('location:admin/dashboard.php');
        exit;
    }elseif($_SESSION['role'] === 'user'){
        header('location:home.php');
        exit;
    }
}

$message = [];

if(isset($_POST['submit'])){

    $role = isset($_POST['role']) ? $_POST['role'] : 'user';
    $role = ($role === 'admin') ? 'admin' : 'user';

    $identifier = trim($_POST['identifier']);
    $identifier = filter_var($identifier, FILTER_SANITIZE_STRING);
    $pass = sha1($_POST['pass']);
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);

    if($role === 'admin'){
        // Admin login (identifier is username)
        $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ? AND password = ?");
        $select_admin->execute([$identifier, $pass]);
        if($select_admin->rowCount() > 0){
            $row = $select_admin->fetch(PDO::FETCH_ASSOC);
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_name'] = $row['name'];
            $_SESSION['role'] = 'admin';
            header('location:admin/dashboard.php');
            exit;
        }
    }else{
        // User login (identifier is email)
        $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ?");
        $select_user->execute([$identifier, $pass]);
        if($select_user->rowCount() > 0){
            $row = $select_user->fetch(PDO::FETCH_ASSOC);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['role'] = 'user';
            header('location:home.php');
            exit;
        }
    }

    $message[] = 'incorrect username/email or password!';

}

// Optional debug info: append ?debug=1 to the URL to see why authentication failed
if(isset($_GET['debug']) && $_GET['debug'] == '1' && isset($_POST['submit'])){
    $dbgRole = $role ?? 'user';
    $dbgId = $identifier ?? '';
    $dbgPass = $pass ?? '';
    if($dbgRole === 'admin'){
        $q = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
        $q->execute([$dbgId]);
        if($q->rowCount() > 0){
            $r = $q->fetch(PDO::FETCH_ASSOC);
            $message[] = "DEBUG: admin found. stored_hash=".htmlspecialchars($r['password'])." provided_hash=".htmlspecialchars($dbgPass);
        }else{
            $message[] = "DEBUG: admin NOT found for identifier='".htmlspecialchars($dbgId)."'.";
        }
    }else{
        $q = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
        $q->execute([$dbgId]);
        if($q->rowCount() > 0){
            $r = $q->fetch(PDO::FETCH_ASSOC);
            $message[] = "DEBUG: user found. stored_hash=".htmlspecialchars($r['password'])." provided_hash=".htmlspecialchars($dbgPass);
        }else{
            $message[] = "DEBUG: user NOT found for identifier='".htmlspecialchars($dbgId)."'.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<?php
   if(!empty($message)){
      foreach($message as $m){
         echo '<div class="message"><span>'.htmlspecialchars($m).'</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
      }
   }
?>

<section class="form-container">

    <form action="" method="post">
        <h3>Login</h3>

        <label for="role" style="display:block; text-align:center; margin-bottom:8px;">Login as</label>
        <select name="role" id="role" class="box" style="width:60%; margin:0 auto 12px auto; display:block;">
            <option value="user" selected>User</option>
            <option value="admin">Admin</option>
        </select>

        <input type="text" id="identifier" name="identifier" required placeholder="email (for users) or username (for admin)" maxlength="50"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="password" name="pass" required placeholder="enter your password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="submit" value="login now" class="btn" name="submit">
        <p>Don't have an account?</p>
        <a href="user_register.php" class="option-btn">Register Now.</a>
    </form>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

<script>
// change placeholder based on role selection to guide the user
document.getElementById('role').addEventListener('change', function(e){
    var id = document.getElementById('identifier');
    if(e.target.value === 'admin'){
        id.placeholder = 'username (admin)';
        id.type = 'text';
    }else{
        id.placeholder = 'email (user)';
        id.type = 'text';
    }
});
</script>

</body>
</html>
