<?php
session_start();
include('connection.php');

// Fetch the account by email only (never compare password in SQL)
$Query = mysqli_query($Connection,
    "SELECT * FROM application WHERE email = '" . mysqli_real_escape_string($Connection, $_POST['email']) . "'"
) or die(mysqli_error($Connection));

if (mysqli_num_rows($Query) > 0) {
    $Row = mysqli_fetch_array($Query);

    // Support both hashed passwords (new) and plain-text (old, before migration)
    $inputPassword = $_POST['password'];
    $storedPassword = $Row['password'];

    $passwordOk = false;
    if (password_verify($inputPassword, $storedPassword)) {
        $passwordOk = true;
    } elseif ($inputPassword === $storedPassword) {
        // Legacy plain-text match — rehash it now for security
        $hashed = password_hash($inputPassword, PASSWORD_DEFAULT);
        mysqli_query($Connection,
            "UPDATE application SET password='" . mysqli_real_escape_string($Connection, $hashed) . "'
             WHERE id_application=" . intval($Row['id_application'])
        );
        $passwordOk = true;
    }

    if ($passwordOk) {
        $_SESSION['validpage']       = TRUE;
        $_SESSION['id_application']  = $Row['id_application'];
        $_SESSION['firstname']       = $Row['firstname'];
        $_SESSION['lastname']        = $Row['lastname'];
        $_SESSION['email']           = $Row['email'];
        $_SESSION['username']        = $Row['username'];
        header('location: service.php');
    } else {
        header('location: login.php?error=1');
    }
} else {
    header('location: login.php?error=1');
}
?>