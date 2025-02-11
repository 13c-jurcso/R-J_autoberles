<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fizetési oldal</title>
    <style>
        /* Általános stílusok */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        h1 {
            color: #333;
            text-align: center;
            font-size: 2em;
        }

        /* Formázás a űrlapnak */
        .payment-form {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .payment-form label {
            font-size: 1em;
            color: #555;
            margin-bottom: 10px;
            display: block;
        }

        .payment-form input[type="number"],
        .payment-form input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }

        .payment-form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px;
            font-size: 1.2em;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }

        .payment-form input[type="submit"]:hover {
            background-color: #45a049;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }

        @media (max-width: 600px) {
            body {
                flex-direction: column;
            }

            .payment-form {
                margin: 20px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <div class="payment-form">
        <h1>Fizetés PayPal segítségével</h1>

        <form action="pay.php" method="POST">
            <label for="amount">Összeg (HUF): </label>
            <input type="number" id="amount" name="amount" required><br><br>

            <label for="email">Email cím: </label>
            <input type="email" id="email" name="email" required><br><br>

            <input type="submit" value="Fizetés indítása">
        </form>
    </div>

    

</body>
</html>
