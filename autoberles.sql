-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Feb 18. 09:52
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `autoberles`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `berlesek`
--

CREATE TABLE `berlesek` (
  `berles_id` int(11) NOT NULL,
  `jarmu_id` int(11) DEFAULT NULL,
  `felhasznalo` varchar(255) DEFAULT NULL,
  `tol` datetime DEFAULT NULL,
  `ig` datetime DEFAULT NULL,
  `kifizetve` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `berlesek`
--

INSERT INTO `berlesek` (`berles_id`, `jarmu_id`, `felhasznalo`, `tol`, `ig`, `kifizetve`) VALUES
(2, 6, 'admin', '2024-12-03 00:00:00', '2024-12-22 00:00:00', 0),
(3, 6, 'admin', '2024-12-03 00:00:00', '2024-12-22 00:00:00', 0),
(4, 6, 'admin', '2024-12-03 00:00:00', '2024-12-22 00:00:00', 0),
(5, 8, 'admin', '2024-12-09 00:00:00', '2024-12-13 00:00:00', 0),
(6, 10, 'admin', '2024-12-11 00:00:00', '2024-12-12 00:00:00', 0),
(7, 8, 'admin', '2024-12-09 00:00:00', '2024-12-13 00:00:00', 0),
(8, 9, 'admin', '2024-12-13 00:00:00', '2024-11-27 00:00:00', 0),
(9, 7, 'admin', '2024-12-09 00:00:00', '2024-12-14 00:00:00', 0),
(10, 47, 'admin', '2024-12-15 00:00:00', '2024-12-16 00:00:00', 0),
(11, 7, 'admin', '2024-12-16 00:00:00', '2024-12-22 00:00:00', 0),
(12, 6, 'admin', '2024-12-17 00:00:00', '2024-12-20 00:00:00', 0),
(13, 8, 'admin', '2024-12-16 00:00:00', '2024-12-30 00:00:00', 0),
(14, 9, 'admin', '2025-01-17 00:00:00', '2025-01-19 00:00:00', 0);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `felhasznalas`
--

CREATE TABLE `felhasznalas` (
  `felhasznalas_id` int(11) NOT NULL,
  `nev` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `felhasznalas`
--

INSERT INTO `felhasznalas` (`felhasznalas_id`, `nev`) VALUES
(1, 'Városi'),
(2, 'Családi'),
(3, 'Haszon'),
(4, 'Élmény autó'),
(5, 'Lakó');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `felhasznalo`
--

CREATE TABLE `felhasznalo` (
  `felhasznalo_nev` varchar(255) NOT NULL,
  `nev` varchar(255) DEFAULT NULL,
  `emailcim` varchar(255) DEFAULT NULL,
  `jogositvany_kiallitasDatum` datetime DEFAULT NULL,
  `szamlazasi_cim` varchar(255) DEFAULT NULL,
  `husegpontok` double DEFAULT NULL,
  `jelszo` varchar(255) DEFAULT NULL,
  `admin` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `felhasznalo`
--

INSERT INTO `felhasznalo` (`felhasznalo_nev`, `nev`, `emailcim`, `jogositvany_kiallitasDatum`, `szamlazasi_cim`, `husegpontok`, `jelszo`, `admin`) VALUES
('admin', 'admin', 'admin@admin.com', '2024-11-21 00:00:00', 'dsdsdx', 500, '$2y$10$eA4teVtYs8mUFgNVW/fi7Om.pWa9QzTVQ0SKsnzdy4hgDujq8V/m.', 1),
('jani', 'Jurcso Janos', 'dsdads@dsd.dsd', '2019-01-09 00:00:00', 'teszt varos teszt utca 1', 0, '$2y$10$B22AtfhKqS3TpOhUkR9yFedcJPm0zUUr4FLTJCCwR5xmj1NELJbD6', NULL),
('jani(admin)', 'Jurcso Janos', 'jurcso.janos@gmail.com', '2024-02-03 00:00:00', 'Veszprem', NULL, '$2y$10$gBMCX78fyZC2AnBU35BbYeDBU5oIsEe5LcqQG4dJr8eiyXxwHKY8y', NULL),
('user1', 'Kiss János', 'janos.kiss@example.com', '2018-08-12 00:00:00', 'Budapest, 1011', 150, NULL, NULL),
('user2', 'Nagy Anna', 'anna.nagy@example.com', '2020-05-30 00:00:00', 'Budapest, 1022', 90.5, NULL, NULL),
('user3', 'Tóth Péter', 'peter.toth@example.com', '2015-03-18 00:00:00', 'Debrecen, 4029', 120, NULL, NULL),
('user4', 'Szabó Zsófia', 'zsofia.szabo@example.com', '2017-07-25 00:00:00', 'Szeged, 6722', 60, NULL, NULL);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `jarmuvek`
--

CREATE TABLE `jarmuvek` (
  `jarmu_id` int(11) NOT NULL,
  `felhasznalas_id` int(11) DEFAULT NULL,
  `szerviz_id` int(11) DEFAULT NULL,
  `gyarto` varchar(255) DEFAULT NULL,
  `tipus` varchar(255) DEFAULT NULL,
  `motor` varchar(255) DEFAULT NULL,
  `gyartasi_ev` date DEFAULT NULL,
  `leiras` varchar(255) DEFAULT NULL,
  `ar` int(11) DEFAULT NULL,
  `kep_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `jarmuvek`
--

INSERT INTO `jarmuvek` (`jarmu_id`, `felhasznalas_id`, `szerviz_id`, `gyarto`, `tipus`, `motor`, `gyartasi_ev`, `leiras`, `ar`, `kep_url`) VALUES
(6, 2, 1, 'Mercedes-Benz', 'S450 Coupe', '2.0TDI', '2019-01-24', 'Az S kupé az autós világ ópiuma. Innen már csak lefelé vezet az út. Elképesztő, valóságtól teljesen elrugaszkodott élményekkel ajándékoz meg, de ennek ára van: utána minden más autó, de talán az egész világ elnagyolt, kezdetleges, ócska vacaknak tűnik.\r\n', 82000, './kepek/Mercedess450.PNG'),
(7, 4, 1, 'McLaren', '650S Spider', '3,8 literes ikerturbós M838T V8', '2014-11-13', 'Egy igazi ínyencség, ha számít egy autó designja: a McLaren 650S Spider messziről felismerhető. 650 lóerős és 3 másodperc alatti 100 kilométerre gyorsul, végsebessége 329 km/óra. Alapja az MP4-12C, motorja ugyanaz a biturbó 3,8 literes V8-as, csak nagyobb', 279400, './kepek/Mclaren650s.PNG'),
(8, 4, 1, 'Ferrari', '458 Italia', '4.5 Benzin', '2009-04-14', 'Ez az autó a kiforrott elegancia ötvözve a mai autósportban létező minden lényeges tulajdonsággal.  Brutális erő egy maximálisan felpörgetett V8-as motorral, 7 fokozatú váltóval, tökéletes légellenállási mutatókkal. Itt a lehetőség, hogy belemarkoljon egy', 320000, './kepek/Ferrari458italia.PNG'),
(9, 4, 1, 'Ford', 'Shelby GT500', '5.2 Benzin', '2022-08-18', 'A valaha készült legerősebb gyári Ford! Külső jegyei is izgalomba hozzák az embert, morcos megjelenése vizuálisan készít fel arra a felfoghatatlan erőre ami benne van! A Shelby GT500 motorját nem aprózták el, az 5,2 literes Predatorra még rápakoltak egy 2', 127000, './kepek/Shelbygt500.png'),
(10, 1, 1, 'Suzuki', 'Swift', '1.2 Benzin', '2023-08-11', 'Tökéletes Városi cruiser', 20000, './kepek/swift.PNG\r\n'),
(11, 5, 4, 'Weinsberg', 'CaraCore 700', '2.2 Dízel', '2024-05-15', 'Tökéletes 4fős lakóautó hosszabb utakra. 4 férőhelyes kerékpártartóval', 58000, './kepek/Weinsberg700.png'),
(12, 4, 3, 'Ford', 'Transit', '2.0 Dízel', '2023-10-09', 'Tökéletes lakóautó 3 fő számára.', 48000, './kepek/Weinsberg600.png\r\n'),
(27, 1, 3, 'BMW', 'M8 Competition', '4.4', '2024-11-21', 'A BMW M8 Competition egyszerre hozza az ízlésesen megkomponált dizájnt és a sportos jegyeket, mind külsőben és belsőben egyaránt. Mármint a belső térben, nem a motorban. Ott semmi ízléses finomkodás nincs, csak az irgalmatlanul súlyos lóerők! A 4,4 litere', 203000, 'kepek/1.jpg'),
(28, 2, 2, 'Ford', 'S-Max', '2.0 Dízel', '2021-07-08', 'A Ford S-Max a tökéletes kombináció a tágas tér, teljesítmény és a kényelem között. Az megbízható motor garantálja a dinamikus vezetési élményt, az innovatív technológiák, mint például az adaptív sebességtartó automatika és a parkoló asszisztens, segítene', 22860, 'kepek/Fordsmax5.PNG'),
(29, 1, 2, 'Toyota', 'Yaris', '1.5 Benzin', '2020-02-22', 'A Toyota Yaris 1.5 2020-as modell 111 lóerős csúcsteljesítményéhez 138 Nm nyomaték társul, amivel 9,47 másodperc alatt éri el a 100 km/h-t. Fogyasztása használattól függően 4,2 és 6,3 l közötti. Helykínálattal sem elöl, sem hátul nincs gond, a csomagtartó', 11430, 'kepek/Toyotayaris.PNG'),
(30, 1, 3, 'fiat', '500', '1.2', '2024-12-20', 'gyors', 20, './kepek/fiat.jpg'),
(31, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(32, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(33, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(34, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(35, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(36, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(37, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(38, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(39, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(40, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(41, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(42, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(43, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(44, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(45, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(46, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(47, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(48, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(49, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(50, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(51, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(52, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(53, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg'),
(54, 1, 1, 'fiat', '500', '1.2', '2024-12-11', 'gyors varosi', 21, './kepek/fiat.jpg');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `szervizek`
--

CREATE TABLE `szervizek` (
  `id` int(11) NOT NULL,
  `muszaki_vizs_lejarat` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `szervizek`
--

INSERT INTO `szervizek` (`id`, `muszaki_vizs_lejarat`) VALUES
(1, '2025-05-10 00:00:00'),
(2, '2026-12-20 00:00:00'),
(3, '2024-11-15 00:00:00'),
(4, '2026-03-05 00:00:00');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `velemenyek`
--

CREATE TABLE `velemenyek` (
  `velemeny_id` int(11) NOT NULL,
  `felhasznalo_nev` varchar(255) NOT NULL,
  `uzenet` text NOT NULL,
  `datum` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `velemenyek`
--

INSERT INTO `velemenyek` (`velemeny_id`, `felhasznalo_nev`, `uzenet`, `datum`) VALUES
(35, 'admin', 'valami', '2025-01-28 10:55:48'),
(36, 'admin', 'nagyon tetszett minden', '2025-01-28 10:56:02'),
(37, 'Rolika', 'Minden fasza volt', '2025-01-28 10:56:13'),
(38, 'jani(admin)', 'nagyon köszönjük', '2025-02-18 09:46:39');

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `berlesek`
--
ALTER TABLE `berlesek`
  ADD PRIMARY KEY (`berles_id`),
  ADD KEY `fk_jarmu_id` (`jarmu_id`),
  ADD KEY `fk_felhasznalo` (`felhasznalo`);

--
-- A tábla indexei `felhasznalas`
--
ALTER TABLE `felhasznalas`
  ADD PRIMARY KEY (`felhasznalas_id`);

--
-- A tábla indexei `felhasznalo`
--
ALTER TABLE `felhasznalo`
  ADD PRIMARY KEY (`felhasznalo_nev`);

--
-- A tábla indexei `jarmuvek`
--
ALTER TABLE `jarmuvek`
  ADD PRIMARY KEY (`jarmu_id`),
  ADD KEY `fk_felhasznalas_id` (`felhasznalas_id`),
  ADD KEY `fk_szerviz_id` (`szerviz_id`);

--
-- A tábla indexei `szervizek`
--
ALTER TABLE `szervizek`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `velemenyek`
--
ALTER TABLE `velemenyek`
  ADD PRIMARY KEY (`velemeny_id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `berlesek`
--
ALTER TABLE `berlesek`
  MODIFY `berles_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT a táblához `jarmuvek`
--
ALTER TABLE `jarmuvek`
  MODIFY `jarmu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT a táblához `velemenyek`
--
ALTER TABLE `velemenyek`
  MODIFY `velemeny_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `berlesek`
--
ALTER TABLE `berlesek`
  ADD CONSTRAINT `fk_felhasznalo` FOREIGN KEY (`felhasznalo`) REFERENCES `felhasznalo` (`felhasznalo_nev`),
  ADD CONSTRAINT `fk_jarmu_id` FOREIGN KEY (`jarmu_id`) REFERENCES `jarmuvek` (`jarmu_id`);

--
-- Megkötések a táblához `jarmuvek`
--
ALTER TABLE `jarmuvek`
  ADD CONSTRAINT `fk_felhasznalas_id` FOREIGN KEY (`felhasznalas_id`) REFERENCES `felhasznalas` (`felhasznalas_id`),
  ADD CONSTRAINT `fk_szerviz_id` FOREIGN KEY (`szerviz_id`) REFERENCES `szervizek` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
