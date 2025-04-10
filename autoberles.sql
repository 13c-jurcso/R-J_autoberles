-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Ápr 09. 13:05
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
-- Tábla szerkezet ehhez a táblához `akciok`
--

CREATE TABLE `akciok` (
  `akcio_id` int(11) NOT NULL,
  `jarmu_id` int(11) NOT NULL,
  `kedvezmeny_szazalek` decimal(5,2) NOT NULL,
  `kezdete` date NOT NULL,
  `vege` date NOT NULL,
  `leiras` varchar(255) DEFAULT NULL,
  `is_black_friday` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `akciok`
--

INSERT INTO `akciok` (`akcio_id`, `jarmu_id`, `kedvezmeny_szazalek`, `kezdete`, `vege`, `leiras`, `is_black_friday`) VALUES
(9, 81, 11.00, '2025-04-09', '2025-04-10', 'Figyelem! Mai napon kedvezményesen bérelheti ki autónkat!', 0),
(10, 83, 18.00, '2025-04-09', '2025-04-10', 'Figyelem! Mai napon kedvezményesen bérelheti ki autónkat!', 0);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `berlesek`
--

CREATE TABLE `berlesek` (
  `berles_id` int(11) NOT NULL,
  `jarmu_id` int(11) DEFAULT NULL,
  `felhasznalo` varchar(255) DEFAULT NULL,
  `tol` date DEFAULT NULL,
  `ig` date DEFAULT NULL,
  `kifizetve` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `berlesek`
--

INSERT INTO `berlesek` (`berles_id`, `jarmu_id`, `felhasznalo`, `tol`, `ig`, `kifizetve`) VALUES
(96, 86, 'admin', '2025-04-01', '2025-04-04', 1),
(97, 81, 'rolika', '2025-04-04', '2025-04-08', 1),
(98, 80, 'lanctalpaskecske', '2025-04-09', '2025-04-11', 0);

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
  `jelszo` varchar(255) DEFAULT NULL,
  `admin` tinyint(1) DEFAULT NULL,
  `Telefonszám` varchar(16) NOT NULL,
  `husegpontok` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `felhasznalo`
--

INSERT INTO `felhasznalo` (`felhasznalo_nev`, `nev`, `emailcim`, `jogositvany_kiallitasDatum`, `szamlazasi_cim`, `jelszo`, `admin`, `Telefonszám`, `husegpontok`) VALUES
('admin', 'admin', 'admin@admin.com', '2024-11-21 00:00:00', 'dsdsdx', '$2y$10$eA4teVtYs8mUFgNVW/fi7Om.pWa9QzTVQ0SKsnzdy4hgDujq8V/m.', 1, '', 11297450),
('Géza', 'Géza Kiss', 'gez@gmail.com', '2000-01-23 00:00:00', 'fghjkléáű', '$2y$10$geElDWF/r3FMkmdy1Ufi6ujZJzgXsjtu/Y3/doEYRlWQGqtBqyHTm', NULL, '', 0),
('Janos', 'Jurcsó János', 'jurcso.ocsi@gmail.com', '2023-12-22 00:00:00', 'Balatonkenese Urbánus utca 3/1', '$2y$10$CHWVcQZEdPKTpEEk5sAMEeRSuxEK9WxADiObEullZTQ076fx5O0.W', NULL, '', 1419706),
('lanctalpaskecske', 'Blank Máté', '13c-blank@ipari.vein.hu', '2025-04-07 00:00:00', 'Nemesvámos', '$2y$10$T4Nh.YsG9RXl1vdeR7zUM.wJH0pZw7E2ZU9r0oAmTYcspr1Kzf25O', NULL, '', 35560),
('rolika', 'Dávid Roland', '13c-david@ipari.vein.hu', '2023-02-28 00:00:00', 'Herend', '$2y$10$otKlDNmNwtAuZa1HYpg8j.FqFId9e.1XVQmxawFCUUB1UI5262iCO', NULL, '', 24892);

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
  `leiras` varchar(2550) DEFAULT NULL,
  `ar` int(11) DEFAULT NULL,
  `kep_url` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `jarmuvek`
--

INSERT INTO `jarmuvek` (`jarmu_id`, `felhasznalas_id`, `szerviz_id`, `gyarto`, `tipus`, `motor`, `gyartasi_ev`, `leiras`, `ar`, `kep_url`) VALUES
(80, 4, 2, 'BMW', 'M3 Competition Touring', '3.0', '2025-02-06', 'Competition változatban 510 lóerős a soros hathengeres motor, 650 Nm a nyomatékcsúcs 2750 és 5500-as percenkénti fordulatszám között. A nagynyomású (350 bar) közvetlen befecskendezéses motor 7000 fölé húzatható. 3,6 mp a gyorsulási idő 0-ról 100 km/órára, a végsebesség 250 km/óra.', 177800, '[\"\\/R-J_autoberles\\/kepek\\/bmwm3competitiontouring_4.webp\",\"\\/R-J_autoberles\\/kepek\\/bmwm3competitiontouring_1.webp\",\"\\/R-J_autoberles\\/kepek\\/bmwm3competitiontouring_2.webp\"]'),
(81, 4, 2, 'Mercedes-Benz', 'S450 Coupe', '4.0', '2023-05-10', 'Az S kupé az autós világ ópiuma. Innen már csak lefelé vezet az út. Elképesztő, valóságtól teljesen elrugaszkodott élményekkel ajándékoz meg, de ennek ára van: utána minden más autó, de talán az egész világ elnagyolt, kezdetleges, ócska vacaknak tűnik.', 62230, '[\"\\/R-J_autoberles\\/kepek\\/mercedesbenzs450coupe_1.webp\",\"\\/R-J_autoberles\\/kepek\\/mercedesbenzs450coupe_2.webp\",\"\\/R-J_autoberles\\/berles\\/mercedesbenzs450coupe_3.webp\"]'),
(82, 4, 2, 'Maserati', 'GranCabrio V8', '4.7 V8', '2013-10-16', 'A Maserati GranCabrio Sport V8 a tökéletes egyensúly eleganciája és sportossága között. Ferrari-szívó V8 460 LE-vel, lenyűgöző designnal, prémium anyagokkal. Akár 4 fő utazhat kényelmesen, full extra felszereltségben. Ez nem csak autó – érzés!', 190500, '[\"\\/R-J_autoberles\\/kepek\\/maseratigrancabriov8_1.webp\",\"\\/R-J_autoberles\\/kepek\\/maseratigrancabriov8_2.webp\",\"\\/R-J_autoberles\\/kepek\\/maseratigrancabriov8_3.webp\"]'),
(83, 4, 2, 'Chevrolet', 'Corvette C6 6.2 V8', '6.2 V8', '2012-10-12', 'A legendás Chevrolet Corvette C6: színtiszta amerikai erő! 430 lóerős V8 motorja 4,2 mp alatt katapultál 100 km/h-ra. Adrenalin, precíz irányíthatóság és sportosan elegáns belső tér vár. A hamisítatlan Corvette élmény garantáltan rabul ejt. Sebesség és stílus!', 64900, '[\"\\/R-J_autoberles\\/kepek\\/chevroletcorvettec662v8_2.webp\",\"\\/R-J_autoberles\\/kepek\\/chevroletcorvettec662v8_1.webp\",\"\\/R-J_autoberles\\/kepek\\/chevroletcorvettec662v8_3.webp\"]'),
(84, 2, 1, 'Opel', 'Crossland X', '1.2', '2019-09-10', 'Az Opel Crossland X: tökéletes családi társ. Stílusosan kompakt, mégis tágas, ideális városba és kalandokra. Hatékony motor, okos biztonsági rendszerek (pl. adaptív tempomat, vészfék). Kényelmes, sokoldalú belső, praktikus csomagtérrel. Megbízható választás minden napra.', 11430, '[\"\\/R-J_autoberles\\/kepek\\/opelcrosslandx_3.webp\",\"\\/R-J_autoberles\\/kepek\\/opelcrosslandx_1.webp\",\"\\/R-J_autoberles\\/kepek\\/opelcrosslandx_2.webp\"]'),
(85, 2, 2, 'KIA', 'Sportage Hybrid Platina', '1.6', '2025-09-26', 'Az új Kia Sportage meghökkentő formatervvel hódít! Egyedi, dinamikus külső, modern vonalak és éles kontúrok adják sportosan elegáns stílusát. Áramvonalas formája nemcsak látványos, de a csökkentett légellenállás révén hatékony is. Garantáltan figyelemfelkeltő választás.', 20320, '[\"\\/R-J_autoberles\\/kepek\\/kiasportagehybridplatina_3.webp\",\"\\/R-J_autoberles\\/kepek\\/kiasportagehybridplatina_1.webp\",\"\\/R-J_autoberles\\/kepek\\/kiasportagehybridplatina_2.webp\"]'),
(86, 1, 3, 'Mini', 'Countryman', '1.6D', '2012-11-11', 'A legnagyobb MINI Countryman: 1.6 dízel (112 LE) ALL4 hajtással mindenre kész. Kiemelkedő, élvezetes vezethetőség kanyarokban is, kényelmes rugózással. Merész, stílusos külső. Praktikus: 5 ülés, nagy (450L) csomagtartó. Karakteres választás, kompromisszumok nélkül.', 10000, '[\"\\/R-J_autoberles\\/kepek\\/minicountryman_2.webp\",\"\\/R-J_autoberles\\/kepek\\/minicountryman_1.webp\",\"\\/R-J_autoberles\\/kepek\\/minicountryman_3.webp\"]'),
(87, 2, 3, 'Toyota', 'C-HR 2.0 GR Sport Hybrid', '2.0 Hybrid', '2020-11-14', 'Futurisztikus hibrid SUV: merész dizájn, japán megbízhatóság. 4L alatti fogyasztás, jó teljesítmény. Játékos formák, bevált technológia és innováció ötvözete. Nyugodt erő, precíz működés. Ideális választás a modern korra.', 12700, '[\"\\/R-J_autoberles\\/kepek\\/toyotachr20grsporthybrid_1.webp\",\"\\/R-J_autoberles\\/kepek\\/toyotachr20grsporthybrid_2.webp\",\"\\/R-J_autoberles\\/kepek\\/toyotachr20grsporthybrid_3.webp\"]'),
(88, 2, 3, 'Volkswagen', 'Tiguan 2.0TDI DSG', '2.0TDI', '2020-11-13', 'VW Tiguan 2.0 TDI DSG: kényelmes, gazdaságos kompakt SUV. Erős, mégis takarékos 2.0 TDI motor, sima DSG váltóval. Tágas belső, modern infotainment és vezetéstámogató rendszerek. Praktikus, stabil és biztonságos választás, akár hosszú utakra is. Ideális bérléshez.', 26670, '[\"\\/R-J_autoberles\\/kepek\\/volkswagentiguan20tdidsg_1.webp\",\"\\/R-J_autoberles\\/kepek\\/volkswagentiguan20tdidsg_2.webp\",\"\\/R-J_autoberles\\/kepek\\/volkswagentiguan20tdidsg_3.webp\"]'),
(89, 1, 1, 'Toyota', 'Yaris Hybrid Selection', '1.5 Hybrid', '2021-01-20', 'Ez a Yaris meglepő dolgokra képes, akár 130 km/h-ig tisztán elektromosan megy. 116 LE hibrid rendszer, könnyedén hozható 3,7 l/100 km fogyasztás. Luxus felszereltség: adaptív tempomat (0 km/h-tól), kereszteződés asszisztens, auto távfény, sávtartó, hátsó vészfék. Digit klíma, ülésfűtés.', 10160, '[\"\\/R-J_autoberles\\/kepek\\/toyotayarishybridselection_1.webp\",\"\\/R-J_autoberles\\/kepek\\/toyotayarishybridselection_2.webp\",\"\\/R-J_autoberles\\/kepek\\/toyotayarishybridselection_3.webp\"]'),
(91, 3, 4, 'Volkswagen', 'Multivan', '2.0 CR TDI', '2020-05-03', 'Valódi luxusautó, nem csak prémium furgon. Tökéletes családoknak: első osztályú helykínálat, prémium műszerfal, kényelmes, variálható ülések 7 főnek. Magas üléshelyzet, jó kilátás, meglepően fordulékony méretéhez képest. 190LE/440Nm, 0-100: 9,1s. Kiváló kamerarendszer.', 33000, '[\"\\/R-J_autoberles\\/kepek\\/volkswagenmultivan_1.webp\",\"\\/R-J_autoberles\\/kepek\\/volkswagenmultivan_2.webp\",\"\\/R-J_autoberles\\/kepek\\/volkswagenmultivan_3.webp\"]'),
(92, 3, 4, 'Mercedes-Benz', 'eSprinter', 'Elektromos 150kW', '2024-01-01', 'Mercedes-Benz eSprinter 2.0 (2024): fejlett, tisztán elektromos furgon üzleti célra, szállítmányozásra. Dinamikus motor, nulla kibocsátás. Nagy hatótáv, gyors töltés. Modern asszisztensek, tágas, kényelmes belső. Biztonságos, produktív munkavégzés. Zöld, gazdaságos fuvarozás.', 25000, '[\"\\/R-J_autoberles\\/kepek\\/mercedesbenzesprinter_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/mercedesbenzesprinter_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/mercedesbenzesprinter_3.jpg\"]'),
(93, 1, 3, 'Fiat', '500e Cabrio', 'Elektromos 87kW', '2024-03-15', 'Fiat 500e Cabrio 2024: Stílusos, tisztán elektromos városi élményautó. Élvezd a nyitott tetős vezetést nulla károsanyag-kibocsátással! Ideális városba: kompakt, könnyen manőverezhető. Modern infotainment, környezetbarát sikk. Szórakozás és fenntarthatóság egyben.', 15000, '[\"\\/R-J_autoberles\\/kepek\\/fiat500ecabrio_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/fiat500ecabrio_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/fiat500ecabrio_3.jpg\"]'),
(94, 1, 1, 'Nissan', 'Leaf e+ Tekna', 'Elektromos 120Kw', '2017-11-24', 'Nissan Leaf e+ Tekna 62kWh (2024): Fejlett EV, akár 385km hatótávval, ideális hosszú utakra. Tekna: prémium kényelem, ProPilot, modern infotainment. Erős motor, dinamikus vezetés, nulla kibocsátás. Megbízható, nagy hatótávú elektromos autó.', 15000, '[\"\\/R-J_autoberles\\/kepek\\/nissanleafetekna_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/nissanleafetekna_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/nissanleafetekna_3.jpg\"]'),
(95, 3, 4, 'Lada', 'Niva', '1.7 i', '2003-06-03', 'A 2003-as Lada Niva: klasszikus, robusztus terepjáró, kiváló terepen. 4x4 hajtása szinte mindenhol elviszi (sár, hó, hegy). Egyszerű mechanika, könnyen javítható, ideális nehéz körülményekre. Nem modern, de strapabíró és megbízható. Erős, praktikus választás minimális elektronikával.', 10000, '[\"\\/R-J_autoberles\\/kepek\\/ladaniva_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/ladaniva_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/ladaniva_3.jpg\"]'),
(96, 3, 4, 'Ford', 'Ranger Wildtrak', '3.0 V6 EcoBlue', '2024-12-03', '2024 Ford Ranger Wildtrak 3.0 V6 EcoBlue: erős, prémium pickup. Erőteljes, 250+ LE V6 dízel motorja bírja a terhet és a terepet. Intelligens 4x4, fejlett futómű. Wildtrak felszereltség: prémium beltér, SYNC 4, modern asszisztensek. Ideális erős, kényelmes munkagép.', 17000, '[\"\\/R-J_autoberles\\/kepek\\/fordrangerwildtrak_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/fordrangerwildtrak_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/fordrangerwildtrak_3.jpg\"]'),
(97, 5, 1, 'Weinsberg', 'CaraCore 700', '2.3 TD', '2024-03-12', 'Weinsberg CaraCore 700 MEG: Prémium integrált lakóautó hosszú utakra, kényelmes kempinghez. Tágas, modern belső: felszerelt konyha, kényelmes ágyak, sok tároló. Erős motor, kényelmes vezetés, modern technológia. Ideális választás a szabadság és modern kényelem kedvelőinek.', 58000, '[\"\\/R-J_autoberles\\/kepek\\/weinsbergcaracore700_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/weinsbergcaracore700_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/weinsbergcaracore700_3.jpg\"]'),
(98, 5, 1, 'Mobilvetta', 'KEA I64 “2023”', '2.2 TD', '2023-04-18', 'Mobilvetta KEA I64 (2023): Prémium integrált lakóautó stílusos, kényelmes utazáshoz. Modern dizájn, fejlett technológia. Tágas belső: felszerelt konyha, étkező, hálók. Komfortos (fűtés, klíma, infotainment). Ideális választás hosszú távra vagy hétvégékre.', 58000, '[\"\\/R-J_autoberles\\/kepek\\/mobilvettakeai642023_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/mobilvettakeai642023_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/mobilvettakeai642023_3.jpg\"]'),
(99, 5, 1, 'Weinsberg', 'CaraTour 600 MQ', '2 TD', '2023-04-07', 'Weinsberg CaraTour 600 MQ: Kompakt, jól felszerelt integrált lakóautó kalandvágyóknak. Ötvözi a kényelmet és a praktikusságot. Jól kihasznált belső tér: konyha, étkező, praktikus ágy. Minőségi anyagok, ergonomikus dizájn. Könnyen manőverezhető.', 48000, '[\"\\/R-J_autoberles\\/kepek\\/weinsbergcaratour600mq_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/weinsbergcaratour600mq_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/weinsbergcaratour600mq_3.jpg\"]'),
(100, 5, 1, 'GiottiVan', '60B', '2.2 TD', '2023-01-21', 'GiottiVan 60B: Prémium, kompakt lakóautó. Könnyen manőverezhető, modern dizájnnal és magas komforttal. Tágas, jól kihasznált belső: praktikus konyha, étkező, kényelmes ágy, rugalmas fürdő. Minőségi anyagok, ergonomikus elrendezés. Ideális városban is.', 53000, '[\"\\/R-J_autoberles\\/kepek\\/giottivan60b_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/giottivan60b_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/giottivan60b_3.jpg\"]');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `szervizek`
--

CREATE TABLE `szervizek` (
  `id` int(11) NOT NULL,
  `muszaki_vizs_lejarat` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `szervizek`
--

INSERT INTO `szervizek` (`id`, `muszaki_vizs_lejarat`) VALUES
(1, 2025),
(2, 2026),
(3, 2027),
(4, 2028),
(5, 2029),
(6, 2030);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `velemenyek`
--

CREATE TABLE `velemenyek` (
  `velemeny_id` int(11) NOT NULL,
  `felhasznalo_nev` varchar(255) NOT NULL,
  `uzenet` text NOT NULL,
  `datum` datetime DEFAULT current_timestamp(),
  `admin_valasz` text DEFAULT NULL,
  `jarmu_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `velemenyek`
--

INSERT INTO `velemenyek` (`velemeny_id`, `felhasznalo_nev`, `uzenet`, `datum`, `admin_valasz`, `jarmu_id`) VALUES
(4, 'janos', 'minden király\r\n', '2025-02-16 17:13:58', NULL, 0),
(13, 'Janos', 'Nagyon jó minden', '2025-03-11 09:15:58', NULL, 0),
(25, 'rolika', 'Gyönyörű autó, úgy megy mint az állat!', '2025-04-03 12:44:28', NULL, 80);

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `akciok`
--
ALTER TABLE `akciok`
  ADD PRIMARY KEY (`akcio_id`),
  ADD KEY `jarmu_id` (`jarmu_id`);

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
-- AUTO_INCREMENT a táblához `akciok`
--
ALTER TABLE `akciok`
  MODIFY `akcio_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT a táblához `berlesek`
--
ALTER TABLE `berlesek`
  MODIFY `berles_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT a táblához `jarmuvek`
--
ALTER TABLE `jarmuvek`
  MODIFY `jarmu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT a táblához `szervizek`
--
ALTER TABLE `szervizek`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT a táblához `velemenyek`
--
ALTER TABLE `velemenyek`
  MODIFY `velemeny_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `akciok`
--
ALTER TABLE `akciok`
  ADD CONSTRAINT `akciok_ibfk_1` FOREIGN KEY (`jarmu_id`) REFERENCES `jarmuvek` (`jarmu_id`);

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
