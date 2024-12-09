<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Husegpontok</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/index.css">

</head>
<body>
<header>
    <div class="menu-toggle">☰ Menu</div>
    <nav>
        <ul>
            <li><a href="index.php">R&J</a></li>
            <li><a href="kapcsolat.php">Kapcsolat</a></li>
            <li><a href="husegpontok.php">Hűségpontok</a></li>
            <li><a href="jarmuvek.php">Gépjárművek</a></li>
            <?php if (isset($_SESSION['felhasznalo_nev'])): ?>
                <li><a href="logout.php">Kijelentkezés</a></li>
            <?php else: ?>
                <li><a href="register.php">Regisztráció</a></li>
                <li><a href="login.php">Bejelentkezés</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<div class="container">
<h2>Hűségpontok</h2>
<p>A hűségpontok rendszere lehetőséget biztosít arra, hogy vásárlásaid után értékes jutalmakat szerezz anélkül, hogy bármilyen külön regisztrációra lenne szükség. Az egész folyamat rendkívül egyszerű és kényelmes: mindössze annyi szükséges, hogy a vásárlásaid során a rendszer automatikusan rögzítse a szükséges adatokat, és a pontok maguktól gyűlnek, hogy később kihasználhasd őket.

**Hogyan működik a rendszer?**

1. **Pontok gyűjtése**: Minden vásárlás során egy előre meghatározott arányban hűségpontokat kapsz. A pontok száma a vásárlásaid összegétől függ – minél többet vásárolsz, annál több pontot gyűjtesz. A rendszer azonnal rögzíti az adatokat, így nem kell semmit külön tenned.

2. **Nincs szükség regisztrációra**: A legjobb az egészben, hogy nem szükséges semmilyen extra lépés vagy regisztráció a programhoz való csatlakozáshoz. Minden vásárlás során automatikusan hozzáadódnak a pontok a vásárlói fiókodhoz, így nem kell aggódnod a külön regisztrációs folyamatok miatt. Az adatok az üzletnél történő vásárláskor kerülnek rögzítésre, és a pontjaid is automatikusan frissülnek.

3. **Pontok felhasználása**: A felhalmozott pontokat később különféle kedvezményekre, ajándékokra vagy exkluzív ajánlatokra válthatod be. A pontok felhasználása szintén egyszerű: a vásárláskor a rendszer automatikusan felajánlja a pontjaid felhasználásának lehetőségét, így még kényelmesebbé téve a vásárlásokat.

4. **Egyszerű és gyors**: Mivel nincs szükség külön regisztrációra, a hűségpontok gyűjtése és felhasználása minden vásárlásnál zökkenőmentes és gyors. Csak vásárolj, és élvezd a hűségpontok előnyeit, miközben azokat bármikor felhasználhatod a következő vásárláskor.

A hűségpontok rendszere tehát egy kényelmes és egyszerű módja annak, hogy hűségedet jutalmakkal viszonozzuk, anélkül, hogy bárminemű adminisztrációval kellene foglalkoznod. Vásárolj bátran, és gyűjtsd a pontokat, hogy a következő vásárlásod még kellemesebb élmény legyen!</p>
</div>


<script>
    document.querySelector(".menu-toggle").addEventListener("click", function () {
        document.querySelector("header").classList.toggle("menu-opened");
    });
</script>

</body>
</html>