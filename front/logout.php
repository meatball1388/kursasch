<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <script>
        localStorage.removeItem('bronic_favorites');
        window.location.href = 'index.php';
    </script>
</head>
<body>
    Выход из системы...
</body>
</html>
