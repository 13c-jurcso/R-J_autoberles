-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Ápr 03. 12:52
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
(97, 81, 'rolika', '2025-04-04', '2025-04-08', 1);

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
(82, 4, 2, 'Maserati', 'GranCabrio V8', '4.7 V8', '2013-10-16', 'Valószínűleg az egyik legfontosabb elem, amire egy kabriónak szüksége van, az a stílus, amit a Maserati GranCabrio Sport V8 maximálisan képvisel, külsőben és az utastérben egyaránt. Kiváló arányokkal és ízléses, finom tervezési kikacsintásokkal. A Maserati egyszerre testesíti meg a kivételes eleganciát, a sportosságot és exkluzivitást, ami minden Maserati modell sajátja. Igazi Ferrari V8 életérzés 460 lóerővel, akár 4 fővel. Felszereltség: Full extra.', 190500, '[\"\\/R-J_autoberles\\/kepek\\/maseratigrancabriov8_1.webp\",\"\\/R-J_autoberles\\/kepek\\/maseratigrancabriov8_2.webp\",\"\\/R-J_autoberles\\/kepek\\/maseratigrancabriov8_3.webp\"]'),
(83, 4, 2, 'Chevrolet', 'Corvette C6 6.2 V8', '6.2 V8', '2012-10-12', 'A Chevrolet Corvette C6 egy legendás sportautó, ami minden részletében a sebességet és a teljesítményt hordozza. A motor ereje lebilincselő, a 430 lóerő V8 4,2 másodperc alatt már 100 km/h-ra is repíti az autót, amitől garantáltan a gyomrodban dobog a szíved! Az erőátvitel pedig simán és precízen veszi az akadályokat. A kifinomult futómű és az agilis kormányzás számára egy kanyar sem akadály. A belső tér tágas, elegáns és sportos, valódi Corvette élményt ad. Erő, adrenalin és hamisítatlan amerikai stílus.', 64900, '[\"\\/R-J_autoberles\\/kepek\\/chevroletcorvettec662v8_2.webp\",\"\\/R-J_autoberles\\/kepek\\/chevroletcorvettec662v8_1.webp\",\"\\/R-J_autoberles\\/kepek\\/chevroletcorvettec662v8_3.webp\"]'),
(84, 2, 1, 'Opel', 'Crossland X', '1.2', '2019-09-10', 'Az Opel Crossland X az ideális választás a családi kalandokhoz. Stílusosan kompakt és mégis tágas, könnyedén illeszkedik a városi életbe. Korszerű motor biztosítja a hatékonyságot és dinamikát. Felszereltségének fontos részei az intelligens technológiák, mint például az adaptív sebességtartó automatika vagy az automatikus vészfékrendszer. Az elegáns belső kialakítás, a kényelmes ülések és a sokoldalú csomagtér kombinációja a maximális kényelmet és praktikusságot nyújtja. Biztos támasz a mindennapokban, megbízható társ a kalandokban.', 11430, '[\"\\/R-J_autoberles\\/kepek\\/opelcrosslandx_3.webp\",\"\\/R-J_autoberles\\/kepek\\/opelcrosslandx_1.webp\",\"\\/R-J_autoberles\\/kepek\\/opelcrosslandx_2.webp\"]'),
(85, 2, 2, 'KIA', 'Sportage Hybrid Platina', '1.6', '2025-09-26', 'Az új Kia Sportage hamar a rivaldafénybe került, hiszen a gyártó gondoskodott a kellően meghökkentő formatervről, amely minden szempontból figyelemfelkeltő. Az autó külső megjelenése egyedi és dinamikus, a modern vonalak és éles kontúrok pedig sportos, mégis elegáns hatást keltenek. Az áramvonalas forma nemcsak a design szempontjából kiemelkedő, hanem a légellenállás csökkentésére is figyelmet fordítottak, így nemcsak jól néz ki, hanem hatékonyan is működik.', 20320, '[\"\\/R-J_autoberles\\/kepek\\/kiasportagehybridplatina_3.webp\",\"\\/R-J_autoberles\\/kepek\\/kiasportagehybridplatina_1.webp\",\"\\/R-J_autoberles\\/kepek\\/kiasportagehybridplatina_2.webp\"]'),
(86, 1, 3, 'Mini', 'Countryman', '1.6D', '2012-11-11', 'A legnagyobb MINI erőteljes 1,6-os dízelmotorjával, 112 lóerővel és ALL4 négykerék-meghajtással készen áll az utad során előforduló eseményekre. A Countryman vezethetősége kiugróan jó, és nemcsak elviseli, ha gyorsabb tempóra kapcsolunk, hanem szinte adja magát a feszesebb tempóra kanyargós országúton. Az oldaldőlése csekély, a rugózás mégsem kényelmetlen. Külsejében merész vizuális energiát hordoz, a drámai első lökhárító kialakításától és a légbeömlőktől a jellegzetes fényszórókig kényelmet és erőt egyaránt ígér. Az egyik legkarakteresebb kocsi a szegmensen belül, mert van stílusa és jó vezetni, és mert a különleges forma nem ment a használhatóság rovására. Bőséges helyet biztosít öt embernek, nem beszélve az egészen nagy (450 literes) csomagtartóról.', 10000, '[\"\\/R-J_autoberles\\/kepek\\/minicountryman_2.webp\",\"\\/R-J_autoberles\\/kepek\\/minicountryman_1.webp\",\"\\/R-J_autoberles\\/kepek\\/minicountryman_3.webp\"]'),
(87, 2, 3, 'Toyota', 'C-HR 2.0 GR Sport Hybrid', '2.0 Hybrid', '2020-11-14', 'A futurisztikus dizájn nem hazudik, korszerű hibrid SUV 4 liter alatti fogyasztással, szép teljesítménnyel.  Grandiózus élek, szigorú, de bújtatottan játékos formák. A japán professzionalizmus iskolapéldája, csak a jól bevált megoldásokat megtartva adtak nagy teret a formai innovációnak, sikeres végeredménnyel. Ezt többek között a megbízhatósága is hűen tükrözi. A látványvilág kellően tükrözi az autó lényegét: nyugodt erő, kompromisszum nélküli precizitás.', 12700, '[\"\\/R-J_autoberles\\/kepek\\/toyotachr20grsporthybrid_1.webp\",\"\\/R-J_autoberles\\/kepek\\/toyotachr20grsporthybrid_2.webp\",\"\\/R-J_autoberles\\/kepek\\/toyotachr20grsporthybrid_3.webp\"]'),
(88, 2, 3, 'Volkswagen', 'Tiguan 2.0TDI DSG', '2.0TDI', '2020-11-13', 'A VW Tiguan 2.0TDI DSG egy kényelmes és gazdaságos kompakt SUV, amely ideális bérlésre. A 2.0 TDI motor jó teljesítményt és alacsony fogyasztást biztosít, míg a DSG váltó sima vezetést garantál. Tágas belső tere és modern felszereltsége, mint az infotainment és vezetéstámogató rendszerek, kényelmes utazást biztosítanak. Praktikus, stabil és biztonságos választás hosszú utazásokhoz is.', 26670, '[\"\\/R-J_autoberles\\/kepek\\/volkswagentiguan20tdidsg_1.webp\",\"\\/R-J_autoberles\\/kepek\\/volkswagentiguan20tdidsg_2.webp\",\"\\/R-J_autoberles\\/kepek\\/volkswagentiguan20tdidsg_3.webp\"]'),
(89, 1, 1, 'Toyota', 'Yaris Hybrid Selection', '1.5 Hybrid', '2021-01-20', 'Ez a Yaris tud néhány elég durva dolgot, olyat is, amit eddig a Lexus LS alatt nem lehetett megkapni. Az autó akár 130 km/h-s sebességig tisztán villanyárammal is képes menni. A rendszer összteljesítménye 116 lóerő ami több mint elegendő a tempós haladáshoz. A hivatalos fogyasztási adat 3,7 l/100 km, mely könnyedén teljesíthető. Felszereltség mely a luxus kategóriában sem alap: álló helyzettől a végsebességig használható, távolságtartós tempomat, a rendszer már kereszteződésekre ráfordulva is figyel – nem üti el a gyalogost, kerékpárost. Automatikus távfény/tompított kapcsolás, sávtartó, tolatáskor, keresztforgalom vagy akadály esetén vészfékező rendszerrel. 2 zónás digit klíma, ülésfűtés.', 10160, '[\"\\/R-J_autoberles\\/kepek\\/toyotayarishybridselection_1.webp\",\"\\/R-J_autoberles\\/kepek\\/toyotayarishybridselection_2.webp\",\"\\/R-J_autoberles\\/kepek\\/toyotayarishybridselection_3.webp\"]'),
(91, 3, 4, 'Volkswagen', 'Multivan', '2.0 CR TDI', '2020-05-03', 'Ez valóban luxusautó, nem pedig egy prémiumos furgon. Olyan autó, ami bármely család igényeit kielégíti! Helykínálatban elsőosztályú, a műszerfal igazi prémium érzést ad. Az ülések kényelmesek, hátul gyakorlatilag bármilyen ülésrend lehetséges, összesen pedig 7 személyt tud befogadni. Jó a kilátás belőle, üdvös a magas vezetési pozíció, megdöbbentő, hogy a bő 5,1 méter hosszú autó milyen fordulékony, s az is, hogy mennyire jó képet ad a teljesen kamerarendszer. Az autóban 190 lóerő és a 440 Nm rejlik, ami 9,1 másodperces 100 km/órára gyorsulást biztosít.', 33000, '[\"\\/R-J_autoberles\\/kepek\\/volkswagenmultivan_1.webp\",\"\\/R-J_autoberles\\/kepek\\/volkswagenmultivan_2.webp\",\"\\/R-J_autoberles\\/kepek\\/volkswagenmultivan_3.webp\"]'),
(92, 3, 4, 'Mercedes-Benz', 'eSprinter', 'Elektromos 150kW', '2024-01-01', 'A Mercedes-Benz eSprinter 2.0 (2024) egy fejlett, tisztán elektromos furgon, amely ideális választás üzleti célokra és szállítmányozásra. Az 2.0 literes elektromos motor dinamikus teljesítményt biztosít, miközben a nulla károsanyag-kibocsátás a fenntarthatóságot szolgálja. Az eSprinter nagy hatótávolságot és gyors töltési lehetőségeket kínál, így ideális hosszú távú, napi szállítmányozási feladatokra. A modern vezetéstámogató rendszerek és a kényelmes, tágas belső tér biztosítják a biztonságos és produktív munkavégzést. Kiváló választás a zöld közlekedésre és gazdaságos fuvarozásra.', 25000, '[\"\\/R-J_autoberles\\/kepek\\/mercedesbenzesprinter_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/mercedesbenzesprinter_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/mercedesbenzesprinter_3.jpg\"]'),
(93, 1, 3, 'Fiat', '500e Cabrio', 'Elektromos 87kW', '2024-03-15', 'A Fiat 500e Cabrio 2024 egy stílusos, tisztán elektromos városi kabrió, amely a környezetbarát közlekedést és a szórakoztató vezetést ötvözi. A kompakt méretének köszönhetően ideális városi közlekedéshez, miközben az elektromos hajtásnak köszönhetően nulla károsanyag-kibocsátást biztosít. Az 500e Cabrio sportos megjelenésével és nyitható tetővel tökéletes választás azoknak, akik szeretnék élvezni a nyílt égboltot, miközben fenntartható módon közlekednek. A könnyű manőverezhetőség és a modern infotainment rendszer kényelmes vezetést kínál, miközben a stílus és a praktikum is megmarad.', 15000, '[\"\\/R-J_autoberles\\/kepek\\/fiat500ecabrio_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/fiat500ecabrio_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/fiat500ecabrio_3.jpg\"]'),
(94, 1, 1, 'Nissan', 'Leaf e+ Tekna', 'Elektromos 120Kw', '2017-11-24', 'A Nissan Leaf e+ Tekna 62 kWh (2024) egy fejlett elektromos autó, amely hosszú hatótávolságot és kényelmes vezetési élményt kínál. A 62 kWh akkumulátornak köszönhetően akár 385 km-es hatótávolságot is elérhet, így ideális választás hosszabb utakra is. A Tekna felszereltség prémium szintű kényelmet és modern technológiát biztosít, mint a ProPilot vezetéstámogató rendszerek és a fejlett infotainment. Az erősebb motor dinamikus vezetési élményt nyújt, miközben a nulla károsanyag-kibocsátás környezetbarát közlekedést tesz lehetővé. A Nissan Leaf e+ Tekna tökéletes választás azoknak, akik megbízható, hosszú hatótávolságú elektromos autót keresnek.', 15000, '[\"\\/R-J_autoberles\\/kepek\\/nissanleafetekna_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/nissanleafetekna_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/nissanleafetekna_3.jpg\"]'),
(95, 3, 4, 'Lada', 'Niva', '1.7 i', '2003-06-03', 'A 2003-as Lada Niva egy klasszikus, rendkívül robusztus és egyszerű terepjáró, amely híres a kiváló terepalkalmasságáról. A 4x4-es hajtás és a könnyű, de erős motor lehetővé teszi, hogy szinte bármilyen nehezebb terepen megállja a helyét, legyen szó sáros, hegyes vagy havas környezetekről. A Lada Niva egyszerű mechanikai felépítése miatt könnyen javítható és fenntartható, ami ideálissá teszi a vidéki vagy extrém körülmények közötti használatra. Bár a felszereltsége nem a legmodernebb, a robusztus konstrukció és a megbízhatóság a fő erősségei. Ha egy erős és praktikus terepjárót keresel, amely nem igényel sok elektronikai rendszert, a 2003-as Niva jó választás lehet.', 10000, '[\"\\/R-J_autoberles\\/kepek\\/ladaniva_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/ladaniva_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/ladaniva_3.jpg\"]'),
(96, 3, 4, 'Ford', 'Ranger Wildtrak', '3.0 V6 EcoBlue', '2024-12-03', 'A 2024-es Ford Ranger Wildtrak 3.0 V6 EcoBlue egy erőteljes és prémium megjelenésű pickup, amely ideális választás azok számára, akik erőre, teljesítményre és stílusra vágynak. A 3.0 literes V6 EcoBlue dízelmotor kiemelkedő, 250+ lóerős teljesítményt biztosít, így könnyedén megbirkózik a nehezebb terhekkel és a terepviszonyokkal. Az intelligens 4x4-es hajtás és a fejlett felfüggesztési rendszer a legkeményebb terepen is biztosítja a stabilitást és kényelmet. A Wildtrak felszereltség prémium anyagokkal és modern technológiai jellemzőkkel, például a SYNC 4 infotainment rendszerrel és vezetéstámogató rendszerekkel, biztosítja a komfortot és a biztonságot. A Ford Ranger Wildtrak 3.0 V6 EcoBlue egy ideális választás azoknak, akik egy erős, kényelmes és jól felszerelt munkagépre vágynak.', 17000, '[\"\\/R-J_autoberles\\/kepek\\/fordrangerwildtrak_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/fordrangerwildtrak_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/fordrangerwildtrak_3.jpg\"]'),
(97, 5, 1, 'Weinsberg', 'CaraCore 700', '2.3 TD', '2024-03-12', 'A Weinsberg CaraCore 700 MEG egy prémium minőségű, integrált lakóautó, amely ideális választás hosszú távú utazásokhoz és kényelmes kempingezéshez. A 700 MEG változat tágas belső teret kínál, modern dizájnnal és kényelmi felszereltséggel, mint például a jól felszerelt konyha, kényelmes hálóhelyek és praktikus tárolóhelyek. Az autó erősebb motorral rendelkezik, így könnyedén teljesíti a hosszú távú utakat és a különböző terepeken is jól teljesít. A vezetési élmény kényelmes, az integrált technológiai rendszerek (például navigáció, infotainment) pedig még kényelmesebbé teszik az utazást. A Weinsberg CaraCore 700 MEG ideális választás azok számára, akik szeretnék élvezni a kempingezést a modern kényelmet és teljes szabadságot biztosítva.', 58000, '[\"\\/R-J_autoberles\\/kepek\\/weinsbergcaracore700_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/weinsbergcaracore700_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/weinsbergcaracore700_3.jpg\"]'),
(98, 5, 1, 'Mobilvetta', 'KEA I64 “2023”', '2.2 TD', '2023-04-18', 'A Mobilvetta KEA I64 (2023) egy prémium minőségű, integrált lakóautó, amely a kényelmes és stílusos utazás élményét biztosít. A 2023-as modell modern dizájnnal és kifinomult technológiai megoldásokkal rendelkezik, ideális választás hosszú távú utazásokhoz vagy akár hétvégi kirándulásokhoz. A KEA I64 tágas belső teret kínál, ahol egy teljesen felszerelt konyha, kényelmes étkező és hálóhelyek találhatók. A jármű különböző komfort jellemzőkkel van felszerelve, mint például a fűtés, klímaberendezés és fejlett infotainment rendszer.', 58000, '[\"\\/R-J_autoberles\\/kepek\\/mobilvettakeai642023_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/mobilvettakeai642023_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/mobilvettakeai642023_3.jpg\"]'),
(99, 5, 1, 'Weinsberg', 'CaraTour 600 MQ', '2 TD', '2023-04-07', 'A Weinsberg CaraTour 600 MQ egy kompakt, de rendkívül jól felszerelt, integrált kis lakóautó, amely ideális választás a kalandvágyó utazók számára. A 600 MQ modell különösen népszerű a közepes méretű járműveket keresők körében, mivel kiválóan ötvözi a kényelmet és a praktikusságot. A jármű tágas belső teret kínál, jól kihasználva a rendelkezésre álló helyet: egy teljesen felszerelt konyha, kényelmes étkező és egy praktikus hálóhely található benne. Az ergonomikus dizájn és a minőségi anyagok biztosítják a kényelmes utazást, miközben az autó kompakt mérete könnyű manőverezést tesz lehetővé városi és szűkebb terepeken is.', 48000, '[\"\\/R-J_autoberles\\/kepek\\/weinsbergcaratour600mq_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/weinsbergcaratour600mq_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/weinsbergcaratour600mq_3.jpg\"]'),
(100, 5, 1, 'GiottiVan', '60B', '2.2 TD', '2023-01-21', 'A GiottiVan 60B egy prémium minőségű, kompakt méretű lakóautó, amely ideális választás azok számára, akik kényelmes, mégis könnyen manőverezhető járművet keresnek. A 60B modell a GiottiVan kínálatában kiemelkedik a modern dizájnjával és magas szintű komfortjával, amelyet az utazók számára kínál.\r\n\r\nA GiottiVan 60B tágas belső teret biztosít, amely jól kihasználja a rendelkezésre álló helyet. A praktikus konyha, étkező és kényelmes hálóhelyek mellett egy rugalmas fürdőszoba is helyet kapott a járműben. Az ergonomikus elrendezés és a minőségi anyagok kényelmes utazást biztosítanak, miközben az autó kompakt méretének köszönhetően könnyedén navigálható, akár szűkebb városi környezetben is.', 53000, '[\"\\/R-J_autoberles\\/kepek\\/giottivan60b_1.jpg\",\"\\/R-J_autoberles\\/kepek\\/giottivan60b_2.jpg\",\"\\/R-J_autoberles\\/kepek\\/giottivan60b_3.jpg\"]');

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
(25, 'rolika', 'Gyönyörű autó, úgy megy mint az állat!', '2025-04-03 12:44:28', NULL, 80),
(26, 'rolika', 'Nagyon komfortos utazásunk volt, a fogyasztása kedvező!', '2025-04-03 12:45:25', NULL, 88),
(27, 'rolika', 'Nagyon kényelmes volt, rengeteg helyre elutaztunk vele. ', '2025-04-03 12:46:03', NULL, 98);

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
  MODIFY `akcio_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT a táblához `berlesek`
--
ALTER TABLE `berlesek`
  MODIFY `berles_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

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
  MODIFY `velemeny_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

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
