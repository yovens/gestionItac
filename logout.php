<?php
// logout.php
session_start();
session_destroy();
header('Location: index.php');
exit;
?>


<style>
    .logout {
    cursor: pointer;
    transition: all 0.3s ease;
}

.logout:hover {
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
    transform: translateY(-3px) scale(1.05);
}

</style>