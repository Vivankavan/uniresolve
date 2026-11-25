<?php require 'includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check(); $_SESSION = []; session_destroy(); session_start(); flash('You have been signed out.'); }
redirect('index.php');
