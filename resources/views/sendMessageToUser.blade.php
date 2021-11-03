<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enviar comentario a el usuario</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
        rel="stylesheet">

    <style>

        html, body {
            margin: 0;
            padding: 0;
            border: 0;
            font-family: Roboto, serif;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #EDF2F7;
            width: 100vw;
            height: 100vh;
        }

        .comment-box {
            background-color: white;
            padding: 25px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            max-width: 70vw;
            border-radius: 10px;
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
        }

        .input-box {
            margin-top: 10px;
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
        }

        .input-box textarea {
            border: none;
            overflow: auto;
            outline: none;
            -webkit-box-shadow: none;
            -moz-box-shadow: none;
            padding: 10px;
        }

        .btn {
            background: #3498db;
            background-image: -webkit-linear-gradient(top, #3498db, #2980b9);
            background-image: -moz-linear-gradient(top, #3498db, #2980b9);
            background-image: -ms-linear-gradient(top, #3498db, #2980b9);
            background-image: -o-linear-gradient(top, #3498db, #2980b9);
            background-image: linear-gradient(to bottom, #3498db, #2980b9);
            -webkit-border-radius: 8;
            -moz-border-radius: 8;
            border-radius: 8px;
            color: #ffffff;
            font-size: 15px;
            padding: 10px 20px 10px 20px;
            text-decoration: none;
            cursor: pointer;
        }

    </style>

</head>


<body>
<div class="container">

    <div class="comment-box">
        <h3 style="text-align: center; color: #4e4e4e">
            Enviar un mensaje a el usuario
        </h3>
        <p style="text-align: justify">
            Por favor, ingrese el mensaje que desea comunicar a el usuario. Este será enviado automáticamente a la
            dirección de correo electrónico del usuario que solicitó el servicio. Adicionalmente se enviará una nota
            con el mensaje proporcionado.
        </p>
        <div style="text-align:center" class="input-box">
            <textarea style="width:50vw; resize:none" placeholder="Ingrese su mensaje" rows="4" id="message"></textarea>
        </div>

        <div style="align-self: end; margin-top: 15px">
            <div class="btn" onclick="sendMessage()">
                Enviar mensaje
            </div>
        </div>
    </div>

</div>
</body>
</html>

<script>
    async function sendMessage() {
        let issue_id = getIssueId();
        let url = 'https://tickets.unibague.edu.co/tickets-forms/comments/issue/' + issue_id;
        let message = document.getElementById('message').value;
        console.log(message);
        let data = {
            'message': message
        };
        let request = await fetch(url, {
                'method': 'POST',
                'body': JSON.stringify(data),
                headers: {
                    'Content-Type': 'application/json'
                },
            }
        );
        let json = await request.json();
        alert(json.message);
    }

    function getIssueId() {
        let url = window.location.href;
        let split_url = url.split('/'); //Get the url separated by slashes
        return split_url[split_url.length - 2]; //Get the text inside the /issue/100/new <- so length-2 will get the actual issue id
    }
</script>
