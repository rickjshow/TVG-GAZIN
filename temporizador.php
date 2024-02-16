<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temporizador</title>
</head>

<body>

    <script>
        let inactivityTimer;

        function resetTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(logout, 10 * 60 * 1000);
        }

        function logout() {
            alert(" Sua sessão expirou, faça login novamente! :P");
            window.location.href = 'logout.php';
        }
        document.addEventListener('mousemove', resetTimer);
        document.addEventListener('keydown', resetTimer);
        document.addEventListener('DOMContentLoaded', resetTimer);
    </script>

</body>
</html>