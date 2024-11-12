-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2024. Nov 12. 09:49
-- Kiszolgáló verziója: 10.4.27-MariaDB
-- PHP verzió: 8.2.0

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
(1, 1, 'user1', '2024-01-10 00:00:00', '2024-01-20 00:00:00', 1),
(2, 2, 'user2', '2024-03-15 00:00:00', '2024-03-25 00:00:00', 0),
(3, 3, 'user3', '2024-05-01 00:00:00', '2024-05-10 00:00:00', 1),
(4, 4, 'user4', '2024-07-05 00:00:00', '2024-07-15 00:00:00', 1),
(5, 5, 'user1', '2024-09-01 00:00:00', '2024-09-15 00:00:00', 0);

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
  `jelszo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `felhasznalo`
--

INSERT INTO `felhasznalo` (`felhasznalo_nev`, `nev`, `emailcim`, `jogositvany_kiallitasDatum`, `szamlazasi_cim`, `husegpontok`, `jelszo`) VALUES
('admin', 'admin', 'admin@admin.com', '2024-11-15 00:00:00', 'dsdsd', 500, '$2y$10$eA4teVtYs8mUFgNVW/fi7Om.pWa9QzTVQ0SKsnzdy4hgDujq8V/m.'),
('jani', 'Jurcso Janos', 'dsdads@dsd.dsd', '2019-01-09 00:00:00', 'teszt varos teszt utca 1', 0, '$2y$10$B22AtfhKqS3TpOhUkR9yFedcJPm0zUUr4FLTJCCwR5xmj1NELJbD6'),
('user1', 'Kiss János', 'janos.kiss@example.com', '2018-08-12 00:00:00', 'Budapest, 1011', 150, NULL),
('user2', 'Nagy Anna', 'anna.nagy@example.com', '2020-05-30 00:00:00', 'Budapest, 1022', 90.5, NULL),
('user3', 'Tóth Péter', 'peter.toth@example.com', '2015-03-18 00:00:00', 'Debrecen, 4029', 120, NULL),
('user4', 'Szabó Zsófia', 'zsofia.szabo@example.com', '2017-07-25 00:00:00', 'Szeged, 6722', 60, NULL);

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
  `gyartasi_ev` datetime DEFAULT NULL,
  `leiras` varchar(255) DEFAULT NULL,
  `ar` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `jarmuvek`
--

INSERT INTO `jarmuvek` (`jarmu_id`, `felhasznalas_id`, `szerviz_id`, `gyarto`, `tipus`, `motor`, `gyartasi_ev`, `leiras`, `ar`) VALUES
(1, 1, 1, 'Toyota', 'Corolla', '1.8L Hybrid', '2022-01-01 00:00:00', 'Megbízható városi autó', 15000),
(2, 2, 2, 'Volkswagen', 'Passat', '2.0 TDI', '2020-06-15 00:00:00', 'Tágas családi autó', 20000),
(3, 3, 3, 'Ford', 'Transit', '2.2 Diesel', '2019-08-20 00:00:00', 'Ideális munkára', 18000),
(4, 4, 1, 'Mazda', 'MX-5', '2.0 Skyactiv', '2021-05-10 00:00:00', 'Sportos élményautó', 25000),
(5, 5, 4, 'Mercedes', 'Marco Polo', '2.2 Diesel', '2023-03-03 00:00:00', 'Lakóautó hosszú utakra', 45000);

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
  `id` int(11) NOT NULL,
  `felhasznalo_nev` varchar(255) DEFAULT NULL,
  `jarmu_id` int(11) DEFAULT NULL,
  `velemeny` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `velemenyek`
--

INSERT INTO `velemenyek` (`id`, `felhasznalo_nev`, `jarmu_id`, `velemeny`) VALUES
(1, 'user1', 1, 'Nagyon kényelmes autó, városi használatra tökéletes.'),
(2, 'user2', 2, 'Tágas és megbízható, az egész család kényelmesen elfért.'),
(3, 'user3', 3, 'Munkára ideális, erős motor és jó fogyasztás.'),
(4, 'user4', 4, 'Nagyon élveztem a vezetést, igazi sportos élmény!'),
(5, 'user1', 5, 'Kényelmes lakóautó, sok helyet biztosít az utazáshoz.');

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_velemeny_felhasznalo` (`felhasznalo_nev`),
  ADD KEY `fk_velemeny_jarmu` (`jarmu_id`);

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

--
-- Megkötések a táblához `velemenyek`
--
ALTER TABLE `velemenyek`
  ADD CONSTRAINT `fk_velemeny_felhasznalo` FOREIGN KEY (`felhasznalo_nev`) REFERENCES `felhasznalo` (`felhasznalo_nev`),
  ADD CONSTRAINT `fk_velemeny_jarmu` FOREIGN KEY (`jarmu_id`) REFERENCES `jarmuvek` (`jarmu_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
