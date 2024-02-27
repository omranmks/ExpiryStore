<!DOCTYPE html>
<html>

<head>
    <script src="js/app.js"></script>
</head>

<body class="">
    
</body>
<script>
    window.Echo.channel('messages.1').listen('Omran-Test', (e) =>{
        console.log(e);
    })
</script>

</html>