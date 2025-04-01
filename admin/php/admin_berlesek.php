<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Bérlések</title>
</head>

<body>
    <header>
        <div class="menu-toggle">☰ Menu</div>
        <nav>
            <ul>
                <li><a href="../../php/index.php">Főoldal</a></li>
                <li><a href="../../php/husegpontok.php">Hűségpontok</a></li>
                <li><a href="../../php/jarmuvek.php">Gépjárművek</a></li>
            </ul>
        </nav>
    </header>
    <h1>Bérlések</h1>

    <div class="menu">
        <a href="./autok_kezeles.php"><button type="submit">Járművek</button></a>
        <a href="./admin_jogosultsag.php"><button type="submit">Jogosultságok</button></a>
        <a href="./admin_berlesek.php"><button type="submit">Bérlések</button></a>
        <a href="./admin_velemenyek.php"><button type="submit">Vélemények</button></a>
        <a href="./admin_akciok.php"><button type="submit">Akciók</button></a>
    </div>
    <hr>

    <div id="uzenet"></div>

    <div class="tartalmi-resz">
        <h2>Új bérlés hozzáadása</h2>
        <form id="add-berles-form" class="form">
            <label>Jármű ID:</label>
            <input type="number" id="jarmu_id" required>
            <label>Felhasználó:</label>
            <input type="text" id="felhasznalo" required>
            <label>Átvétel időpontja:</label>
            <input type="date" id="tol" required>
            <label>Leadás dátuma:</label>
            <input type="date" id="ig" required>
            <button type="submit">Hozzáadás</button>
        </form>
        <hr><br>

        <h2>Bérlés módosítása</h2>
        <form id="edit-berles-form" class="form">
            <label>Bérlés ID:</label>
            <input type="number" id="edit_berles_id" required>
            <label>Jármű ID:</label>
            <input type="number" id="edit_jarmu_id" required>
            <label>Felhasználó:</label>
            <input type="text" id="edit_felhasznalo" required>
            <label>Átvétel időpontja:</label>
            <input type="date" id="edit_tol" required>
            <label>Leadás dátuma:</label>
            <input type="date" id="edit_ig" required>
            <button type="submit">Módosítás</button>
        </form>
        <hr><br>

        <h2>Egy bérlés lekérdezése</h2>
        <form id="get-berles-form" class="form">
            <label>Bérlés ID:</label>
            <input type="number" id="get_berles_id" required>
            <button type="submit">Lekérdezés</button>
        </form>
        <div id="single-berles-result"></div>
    </div>
    <hr>

    <h2>Törlés</h2>
    <div class="table-container">
        <table id="berlesek-table">
            <tr>
                <th>Bérlés sorszáma</th>
                <th>Kibérelt jármű gyártója</th>
                <th>Kibérelt jármű típusa</th>
                <th>Bérlő neve</th>
                <th>Átvétel időpontja</th>
                <th>Leadás dátuma</th>
                <th>Művelet</th>
            </tr>
        </table>
    </div>

    <script>
        // Összes bérlés betöltése
        function loadBerlesek() {
            fetch('./api.php', {
                    method: 'GET'
                })
                .then(response => response.json())
                .then(data => {
                    const table = document.getElementById('berlesek-table');
                    table.innerHTML = `
                        <tr>
                            <th>Bérlés sorszáma</th>
                            <th>Kibérelt jármű gyártója</th>
                            <th>Kibérelt jármű típusa</th>
                            <th>Bérlő neve</th>
                            <th>Átvétel időpontja</th>
                            <th>Leadás dátuma</th>
                            <th>Művelet</th>
                        </tr>
                    `;
                    if (data.status === 'success') {
                        data.data.forEach(berles => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${berles.berles_id}</td>
                                <td>${berles.gyarto}</td>
                                <td>${berles.tipus}</td>
                                <td>${berles.nev}</td>
                                <td>${berles.tol}</td>
                                <td>${berles.ig}</td>
                                <td><button type="submit" class="torles_button" onclick="deleteBerles(${berles.berles_id})">Törlés</button></td>
                            `;
                            table.appendChild(row);
                        });
                    } else {
                        table.innerHTML += `<tr><td colspan="7">${data.message}</td></tr>`;
                    }
                })
                .catch(error => showMessage('Hiba a bérlések betöltése során!', 'sikertelen'));
        }

        // Egy bérlés lekérdezése
        document.getElementById('get-berles-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const berles_id = document.getElementById('get_berles_id').value;
            fetch(`./api.php?berles_id=${berles_id}`, {
                    method: 'GET'
                })
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('single-berles-result');
                    if (data.status === 'success') {
                        const b = data.data;
                        resultDiv.innerHTML = `
                            <p>ID: ${b.berles_id}</p>
                            <p>Jármű: ${b.gyarto} ${b.tipus}</p>
                            <p>Bérlő: ${b.nev}</p>
                            <p>Átvétel: ${b.tol}</p>
                            <p>Leadás: ${b.ig}</p>
                        `;
                    } else {
                        resultDiv.innerHTML = `<p>${data.message}</p>`;
                    }
                })
                .catch(error => showMessage('Hiba a bérlés lekérdezése során!', 'sikertelen'));
        });

        // Új bérlés hozzáadása
        document.getElementById('add-berles-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const data = {
                jarmu_id: document.getElementById('jarmu_id').value,
                felhasznalo: document.getElementById('felhasznalo').value,
                tol: document.getElementById('tol').value,
                ig: document.getElementById('ig').value
            };
            fetch('./api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    showMessage(data.message, data.status === 'success' ? 'sikeres' : 'sikertelen');
                    if (data.status === 'success') loadBerlesek();
                })
                .catch(error => showMessage('Hiba a bérlés hozzáadása során!', 'sikertelen'));
        });

        // Bérlés módosítása
        document.getElementById('edit-berles-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const data = {
                berles_id: document.getElementById('edit_berles_id').value,
                jarmu_id: document.getElementById('edit_jarmu_id').value,
                felhasznalo: document.getElementById('edit_felhasznalo').value,
                tol: document.getElementById('edit_tol').value,
                ig: document.getElementById('edit_ig').value
            };
            fetch('./api.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    showMessage(data.message, data.status === 'success' ? 'sikeres' : 'sikertelen');
                    if (data.status === 'success') loadBerlesek();
                })
                .catch(error => showMessage('Hiba a bérlés módosítása során!', 'sikertelen'));
        });

        // Bérlés törlése
        function deleteBerles(berles_id) {
            if (confirm('Biztosan törölni szeretné ezt a bérlést?')) {
                fetch('./api.php', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            berles_id: berles_id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        showMessage(data.message, data.status === 'success' ? 'sikeres' : 'sikertelen');
                        if (data.status === 'success') loadBerlesek();
                    })
                    .catch(error => showMessage('Hiba a törlés során!', 'sikertelen'));
            }
        }

        // Üzenet megjelenítése
        function showMessage(message, type) {
            const uzenetDiv = document.getElementById('uzenet');
            uzenetDiv.innerHTML = `<div class="${type}" id="animDiv">${message}</div>`;
            setTimeout(() => {
                const animDiv = document.getElementById('animDiv');
                if (animDiv) animDiv.classList.add('hidden');
            }, 3000);
        }

        // Oldal betöltésekor bérlések lekérése
        document.addEventListener('DOMContentLoaded', loadBerlesek);
    </script>

    <footer class="container mt-5 mb-3 text-center text-muted">
        R&J Admin - @ <?= date('Y') ?>
    </footer>
</body>

</html>