<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin_berlesek.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Bérlések</title>
</head>
<body>
    <header>
        <div class="menu-toggle">☰ Menu</div>
        <nav>
            <ul>
                <li><a href="index.php">Főoldal</a></li>
                <li><a href="husegpontok.php">Hűségpontok</a></li>
                <li><a href="jarmuvek.php">Gépjárművek</a></li>
            </ul>
        </nav>
    </header>
    <h1>Bérlések</h1>

    <div class="menu">
        <a href="./autok_kezeles.php"><button>Járművek <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-car-front-fill" viewBox="0 0 16 16">
            <path d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679q.05.242.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.8.8 0 0 0 .381-.404l.792-1.848ZM3 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2m10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2M6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2zM2.906 5.189a.51.51 0 0 0 .497.731c.91-.073 3.35-.17 4.597-.17s3.688.097 4.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 11.691 3H4.309a.5.5 0 0 0-.447.276L2.906 5.19Z" />
        </svg></button></a>
        <a href="./admin_jogosultsag.php"><button>Jogosultságok <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill-down" viewBox="0 0 16 16">
            <path d="M12.5 9a3.5 3.5 0 1 1 0 7 3.5 3.5 0 0 1 0-7m.354 5.854 1.5-1.5a.5.5 0 0 0-.708-.708l-.646.647V10.5a.5.5 0 0 0-1 0v2.793l-.646-.647a.5.5 0 0 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
            <path d="M2 13c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4" />
        </svg></button></a>
        <a href="./admin_berlesek.php"><button>Bérlések <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
            <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0" />
            <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708" />
        </svg></button></a>
        <a href="./admin_velemenyek.php"><button>Vélemények</button></a>
        <a href="./admin_akciok.php"><button>Akciók</button></a>
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
            <input type="datetime-local" id="tol" required>
            <label>Leadás dátuma:</label>
            <input type="datetime-local" id="ig" required>
            <button type="submit">Hozzáadás</button>
        </form>

        <h2>Bérlés módosítása</h2>
        <form id="edit-berles-form" class="form">
            <label>Bérlés ID:</label>
            <input type="number" id="edit_berles_id" required>
            <label>Jármű ID:</label>
            <input type="number" id="edit_jarmu_id" required>
            <label>Felhasználó:</label>
            <input type="text" id="edit_felhasznalo" required>
            <label>Átvétel időpontja:</label>
            <input type="datetime-local" id="edit_tol" required>
            <label>Leadás dátuma:</label>
            <input type="datetime-local" id="edit_ig" required>
            <button type="submit">Módosítás</button>
        </form>

        <h2>Egy bérlés lekérdezése</h2>
        <form id="get-berles-form" class="form">
            <label>Bérlés ID:</label>
            <input type="number" id="get_berles_id" required>
            <button type="submit">Lekérdezés</button>
        </form>
        <div id="single-berles-result"></div>
    </div>

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
            fetch('./api.php', { method: 'GET' })
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
                                <td><button class="torles_button" onclick="deleteBerles(${berles.berles_id})">Törlés</button></td>
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
            fetch(`./api.php?berles_id=${berles_id}`, { method: 'GET' })
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
                headers: { 'Content-Type': 'application/json' },
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
                headers: { 'Content-Type': 'application/json' },
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
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ berles_id: berles_id })
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
</body>
</html>