<?php
session_start();
session_unset();
session_destroy();
header('Location: /template/tried_and_tasted/pages/login.php');
exit; 