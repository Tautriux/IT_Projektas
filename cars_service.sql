-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 28, 2025 at 03:46 AM
-- Server version: 8.0.41-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cars_service`
--
CREATE DATABASE IF NOT EXISTS `cars_service` DEFAULT CHARACTER SET utf32 COLLATE utf32_general_ci;
USE `cars_service`;

-- --------------------------------------------------------

--
-- Table structure for table `FAQ`
--

DROP TABLE IF EXISTS `FAQ`;
CREATE TABLE `FAQ` (
  `id` int NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `FAQ`
--

INSERT INTO `FAQ` (`id`, `question`, `answer`) VALUES
(6, 'Kaip užsiregistruoti vizitui?', 'Norėdami užsiregistruoti, prisijunkite prie sistemos, pasirinkite paslaugą ir mechaniką, o tada patvirtinkite registraciją.'),
(7, 'Ar galiu atšaukti registraciją?', 'Taip, registraciją galite atšaukti bet kuriuo metu savo paskyros skiltyje „Mano registracijos“.'),
(8, 'Kokios paslaugos yra teikiamos?', 'Teikiame automobilio remonto, diagnostikos, dalių keitimo bei profilaktinės priežiūros paslaugas.'),
(9, 'Ar galiu pasirinkti mechaniką?', 'Taip, registracijos metu galite pasirinkti norimą mechaniką, jei jis yra laisvas pasirinktu laiku.'),
(10, 'Kaip sužinoti paslaugos kainą?', 'Kainos pateikiamos prie kiekvienos paslaugos aprašymo. Jei reikalinga detalesnė informacija, susisiekite su mūsų administracija.'),
(11, 'Ar gausiu pranešimą apie vizitą?', 'Taip, likus 24 valandoms iki vizito gausite el. laišką arba SMS priminimą (jei pasirinkote tokią funkciją).'),
(12, 'Ką daryti, jei negaliu atvykti laiku?', 'Prašome kuo greičiau informuoti mus arba perkelti vizitą į kitą laiką savo paskyroje.'),
(13, 'Kaip ilgai trunka paslauga?', 'Paslaugos trukmė priklauso nuo tipo, bet dažniausiai tai trunka nuo 30 minučių iki kelių valandų.'),
(14, 'Ar suteikiate garantiją atliktoms paslaugoms?', 'Taip, visoms atliktoms paslaugoms taikoma garantija, kurios trukmė priklauso nuo atliktų darbų pobūdžio.'),
(15, 'Kaip galiu susisiekti su administracija?', 'Susisiekti galite telefonu, el. paštu arba per sistemoje esančią kontaktų formą.');

-- --------------------------------------------------------

--
-- Table structure for table `REGISTRATION`
--

DROP TABLE IF EXISTS `REGISTRATION`;
CREATE TABLE `REGISTRATION` (
  `id` int NOT NULL,
  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fk_client` int NOT NULL,
  `fk_mechanic` int NOT NULL,
  `fk_completion` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `REGISTRATION`
--

INSERT INTO `REGISTRATION` (`id`, `date_time`, `fk_client`, `fk_mechanic`, `fk_completion`) VALUES
(27, '2025-11-14 09:30:00', 8, 7, 8),
(28, '2025-11-17 07:00:00', 8, 9, 15),
(29, '2025-11-24 12:30:00', 8, 13, 24),
(31, '2025-11-14 07:30:00', 8, 9, 16),
(32, '2025-11-14 11:45:00', 8, 9, 12),
(33, '2025-11-14 07:00:00', 6, 7, 7),
(34, '2025-11-14 08:00:00', 6, 7, 11),
(35, '2025-11-14 11:15:00', 6, 13, 24),
(36, '2025-11-14 08:15:00', 6, 9, 12),
(37, '2025-11-14 08:30:00', 6, 7, 7),
(38, '2025-11-22 10:30:00', 6, 7, 9),
(39, '2025-11-21 08:30:00', 6, 9, 14),
(40, '2025-11-19 12:00:00', 6, 7, 7),
(41, '2025-11-20 12:00:00', 6, 7, 7),
(42, '2025-11-20 11:15:00', 6, 7, 7),
(43, '2025-11-20 06:00:00', 6, 9, 14),
(44, '2025-11-21 06:00:00', 6, 13, 22),
(45, '2025-11-21 12:00:00', 6, 13, 23),
(46, '2025-11-21 08:30:00', 6, 7, 7),
(47, '2025-11-21 06:00:00', 6, 7, 28),
(48, '2025-11-21 06:15:00', 6, 7, 28),
(49, '2025-12-02 06:00:00', 6, 7, 8),
(53, '2025-12-01 08:00:00', 6, 7, 8),
(54, '2025-12-02 07:45:00', 10, 7, 9),
(55, '2025-11-29 07:00:00', 6, 7, 10),
(56, '2025-11-28 07:00:00', 6, 7, 10),
(57, '2025-11-29 08:00:00', 6, 7, 10),
(58, '2025-11-29 10:30:00', 6, 7, 10),
(59, '2025-12-10 06:00:00', 6, 9, 27),
(60, '2025-12-16 11:15:00', 6, 9, 27),
(61, '2025-12-17 08:00:00', 6, 9, 13),
(62, '2025-12-20 07:00:00', 6, 9, 16),
(73, '2025-11-30 02:45:00', 6, 7, 10);

-- --------------------------------------------------------

--
-- Table structure for table `REVIEW`
--

DROP TABLE IF EXISTS `REVIEW`;
CREATE TABLE `REVIEW` (
  `id` int NOT NULL,
  `rating` int NOT NULL,
  `comment` text NOT NULL,
  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fk_client` int NOT NULL,
  `fk_mechanic` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `REVIEW`
--

INSERT INTO `REVIEW` (`id`, `rating`, `comment`, `date_time`, `fk_client`, `fk_mechanic`) VALUES
(9, 5, 'Puikiai atliko darbą, automobilis veikia kaip naujas.', '2025-11-14 00:54:50', 6, 7),
(10, 4, 'Greita ir kokybiška diagnostika, rekomenduoju.', '2025-11-14 00:54:50', 8, 7),
(11, 5, 'Labai draugiška ir profesionali. Ačiū!', '2025-11-14 00:54:50', 10, 7),
(12, 3, 'Darbas atliktas gerai, bet užtruko ilgiau nei tikėjausi.', '2025-11-14 00:54:50', 12, 7),
(13, 5, 'Likau labai patenkintas – viskas išspręsta tą pačią dieną.', '2025-11-14 00:54:50', 15, 7),
(14, 4, 'Malonus bendravimas ir aiškiai paaiškino problemą.', '2025-11-14 00:54:50', 6, 9),
(15, 5, 'Sutvarkė greitai ir kokybiškai. Tikrai sugrįšiu.', '2025-11-14 00:54:50', 7, 9),
(16, 4, 'Geras aptarnavimas, viskas atlikta laiku.', '2025-11-14 00:54:50', 10, 9),
(18, 3, 'Darbas geras, bet kaina galėjo būti mažesnė.', '2025-11-14 00:54:50', 13, 9),
(24, 5, 'Tikrai profesionali meistrė. Greitai nustatė gedimą.', '2025-11-14 00:54:50', 6, 13),
(25, 4, 'Esu patenkintas darbo kokybe, viskas tvarkoje.', '2025-11-14 00:54:50', 7, 13),
(26, 5, 'Labai gera komunikacija ir atliktas darbas.', '2025-11-14 00:54:50', 8, 13),
(27, 3, 'Užtruko ilgiau nei planuota, bet rezultatas geras.', '2025-11-14 00:54:50', 10, 13),
(29, 5, 'Gerai dirbantis meistras.', '2025-11-14 01:51:51', 6, 7),
(31, 5, 'Gerai dirbantis meistras', '2025-11-27 14:52:13', 6, 7),
(32, 5, 'puikiai dirba', '2025-11-27 17:51:20', 6, 7),
(33, 1, 'blogas', '2025-11-27 17:59:38', 6, 7),
(34, 5, 'Puikiai dirba.', '2025-11-28 02:23:30', 6, 7);

-- --------------------------------------------------------

--
-- Table structure for table `SERVICE`
--

DROP TABLE IF EXISTS `SERVICE`;
CREATE TABLE `SERVICE` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `SERVICE`
--

INSERT INTO `SERVICE` (`id`, `name`, `description`) VALUES
(6, 'Pilna automobilio diagnostika', 'Kompiuterinė variklio, elektrinės sistemos ir kitų mazgų diagnostika, skirta nustatyti gedimus.'),
(7, 'Alyvos ir filtrų keitimas', 'Variklio alyvos, alyvos filtro, oro filtro ir salono filtro pakeitimas naudojant kokybiškas medžiagas.'),
(8, 'Stabdžių sistemos remontas', 'Stabdžių diskų, kaladėlių ir stabdžių skysčio keitimas bei sistemos patikra.'),
(9, 'Važiuoklės patikra ir remontas', 'Amortizatorių, šarnyrų, trauklių, įvorių bei kitų važiuoklės komponentų diagnostika ir keitimas.'),
(10, 'Ratų suvedimas (geometrija)', 'Ratų suvedimo ir išvirtimo kampų reguliavimas siekiant užtikrinti stabilų automobilio važiavimą.'),
(11, 'Kondicionavimo sistemos pildymas', 'Kondicionieriaus sistemos patikrinimas, nuotėkio testas ir freono papildymas.'),
(12, 'Akumuliatoriaus patikra ir keitimas', 'Akumuliatoriaus įkrovos testavimas, gnybtų valymas ir, jei reikia, naujo akumuliatoriaus montavimas.'),
(13, 'Variklio diržų ir grandinių keitimas', 'Paskirstymo diržo/grandinės keitimas kartu su įtempėjais ir vandens pompa.'),
(14, 'Išmetimo sistemos remontas', 'Duslintuvo, lankstų, vamzdžių ir tvirtinimo elementų suvirinimas ar keitimas.'),
(15, 'Padangų montavimas ir balansavimas', 'Padangų permontavimas, balansavimas ir oro slėgio patikrinimas pagal gamintojo reikalavimus.');

-- --------------------------------------------------------

--
-- Table structure for table `SERVICE_COMPLETION`
--

DROP TABLE IF EXISTS `SERVICE_COMPLETION`;
CREATE TABLE `SERVICE_COMPLETION` (
  `id` int NOT NULL,
  `duration_in_minutes` int NOT NULL,
  `fk_service` int NOT NULL,
  `fk_mechanic` int NOT NULL,
  `usedInRegistration` tinyint(1) NOT NULL,
  `removed` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `SERVICE_COMPLETION`
--

INSERT INTO `SERVICE_COMPLETION` (`id`, `duration_in_minutes`, `fk_service`, `fk_mechanic`, `usedInRegistration`, `removed`) VALUES
(7, 45, 6, 7, 1, 1),
(8, 30, 7, 7, 1, 1),
(9, 90, 8, 7, 1, 1),
(10, 60, 9, 7, 1, 1),
(11, 30, 11, 7, 1, 0),
(12, 30, 7, 9, 1, 0),
(13, 120, 14, 9, 1, 0),
(14, 15, 15, 9, 1, 0),
(15, 75, 10, 9, 1, 0),
(16, 45, 12, 9, 1, 0),
(22, 60, 14, 13, 1, 0),
(23, 45, 15, 13, 1, 0),
(24, 90, 6, 13, 1, 0),
(25, 45, 7, 13, 0, 0),
(26, 75, 11, 13, 0, 0),
(27, 90, 6, 9, 1, 0),
(28, 15, 6, 7, 1, 1),
(33, 30, 6, 7, 0, 0),
(34, 120, 7, 7, 0, 0),
(36, 75, 8, 7, 0, 0),
(40, 15, 10, 7, 0, 0),
(41, 60, 9, 7, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `SYSTEM_USER`
--

DROP TABLE IF EXISTS `SYSTEM_USER`;
CREATE TABLE `SYSTEM_USER` (
  `id` int NOT NULL,
  `username` varchar(30) NOT NULL,
  `encrypted_password` varchar(60) NOT NULL,
  `name` varchar(30) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `fk_role` int NOT NULL,
  `blocked` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `SYSTEM_USER`
--

INSERT INTO `SYSTEM_USER` (`id`, `username`, `encrypted_password`, `name`, `surname`, `email`, `phone`, `fk_role`, `blocked`) VALUES
(6, 'jonas1', '$2y$10$cfEypAQzm4DbDvfvVJNcSeCeFYfHK7pjegShtNOTD6Y4oWNUXZl9a', 'Jonas', 'Petrauskas', 'jonas1@example.com', '+37061234561', 1, 0),
(7, 'ieva22', '$2y$10$cfEypAQzm4DbDvfvVJNcSeCeFYfHK7pjegShtNOTD6Y4oWNUXZl9a', 'Ieva', 'Jonaitytė', 'ieva22@example.com', '+37061234562', 2, 0),
(8, 'mantas_x', '$2y$10$cfEypAQzm4DbDvfvVJNcSeCeFYfHK7pjegShtNOTD6Y4oWNUXZl9a', 'Mantas', 'Kairys', 'mantas_x@example.com', '+37061234563', 2, 1),
(9, 'laura.k', '$2y$10$cfEypAQzm4DbDvfvVJNcSeCeFYfHK7pjegShtNOTD6Y4oWNUXZl9a', 'Laura', 'Kazlauskaitė', 'laura.k@example.com', '+37061234564', 2, 0),
(10, 'paulius', '$2y$10$cfEypAQzm4DbDvfvVJNcSeCeFYfHK7pjegShtNOTD6Y4oWNUXZl9a', 'Paulius', 'Mockus', 'paulius@example.com', '+37061234565', 1, 0),
(12, 'karolis', '$2y$10$cfEypAQzm4DbDvfvVJNcSeCeFYfHK7pjegShtNOTD6Y4oWNUXZl9a', 'Karolis', 'Barauskas', 'karolis@example.com', '+37061234567', 1, 0),
(13, 'simona', '$2y$10$cfEypAQzm4DbDvfvVJNcSeCeFYfHK7pjegShtNOTD6Y4oWNUXZl9a', 'Simona', 'Stankevičiūtė', 'simona@example.com', '+37061234568', 2, 0),
(14, 'admin3', '$2y$10$cfEypAQzm4DbDvfvVJNcSeCeFYfHK7pjegShtNOTD6Y4oWNUXZl9a', 'Adminas', 'Didysis', 'admin3@example.com', '+37061234569', 3, 0),
(15, 'tomas', '$2y$10$cfEypAQzm4DbDvfvVJNcSeCeFYfHK7pjegShtNOTD6Y4oWNUXZl9a', 'Tomas', 'Urbonas', 'tomas@example.com', '+37061234570', 1, 0),
(20, 'tautrimas', '$2y$10$Flv4cumCtCuK53KlkcRfi./XbjDcy8ISjl1NA1eAn5QWrJd2bI.De', 'Tautrimas', 'Ram', 'tautrimas@example.com', '+37065787877', 1, 0),
(21, 'romas', '$2y$10$OGFrxhyI3gGt2GCjB6hJ8u00y67Eh8NME/wiIgdXb188r416PScue', 'Romas', 'Jankauskas', 'romas@gmail.com', '+37065787877', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `USER_ROLE`
--

DROP TABLE IF EXISTS `USER_ROLE`;
CREATE TABLE `USER_ROLE` (
  `id` int NOT NULL,
  `name` char(16) CHARACTER SET utf32 COLLATE utf32_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `USER_ROLE`
--

INSERT INTO `USER_ROLE` (`id`, `name`) VALUES
(1, 'Klientas'),
(2, 'Mechanikas'),
(3, 'Administratorius');

-- --------------------------------------------------------

--
-- Table structure for table `WORK_HOURS`
--

DROP TABLE IF EXISTS `WORK_HOURS`;
CREATE TABLE `WORK_HOURS` (
  `id` int NOT NULL,
  `week_day` int NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `fk_mechanic` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `WORK_HOURS`
--

INSERT INTO `WORK_HOURS` (`id`, `week_day`, `start_time`, `end_time`, `fk_mechanic`) VALUES
(51, 1, '10:00:00', '12:15:00', 7),
(53, 2, '08:00:00', '12:00:00', 7),
(54, 2, '13:00:00', '21:30:00', 7),
(56, 3, '13:00:00', '20:00:00', 7),
(57, 4, '08:00:00', '12:00:00', 7),
(58, 4, '13:00:00', '17:00:00', 7),
(59, 5, '08:00:00', '12:00:00', 7),
(60, 5, '13:00:00', '17:00:00', 7),
(61, 6, '09:00:00', '12:00:00', 7),
(62, 6, '12:30:00', '14:00:00', 7),
(63, 1, '08:00:00', '12:00:00', 9),
(65, 2, '08:00:00', '12:00:00', 9),
(67, 3, '08:00:00', '12:00:00', 9),
(68, 3, '13:00:00', '17:00:00', 9),
(69, 4, '08:00:00', '12:00:00', 9),
(70, 4, '13:00:00', '17:00:00', 9),
(71, 5, '08:00:00', '12:00:00', 9),
(72, 5, '13:00:00', '17:00:00', 9),
(73, 6, '09:00:00', '12:00:00', 9),
(74, 6, '12:30:00', '14:00:00', 9),
(87, 1, '08:00:00', '12:00:00', 13),
(88, 1, '13:00:00', '17:00:00', 13),
(89, 2, '08:00:00', '12:00:00', 13),
(90, 2, '13:00:00', '17:00:00', 13),
(91, 3, '08:00:00', '12:00:00', 13),
(92, 3, '13:00:00', '17:00:00', 13),
(93, 4, '08:00:00', '12:00:00', 13),
(94, 4, '13:00:00', '17:00:00', 13),
(95, 5, '08:00:00', '12:00:00', 13),
(96, 5, '13:00:00', '17:00:00', 13),
(97, 6, '09:00:00', '12:00:00', 13),
(98, 6, '12:30:00', '14:00:00', 13),
(100, 2, '13:00:00', '17:00:00', 9),
(103, 1, '13:00:00', '20:00:00', 9),
(104, 1, '00:00:00', '00:00:00', 14),
(105, 3, '11:30:00', '12:00:00', 7),
(108, 3, '20:30:00', '21:00:00', 7),
(109, 3, '22:00:00', '23:45:00', 7),
(114, 1, '13:15:00', '13:45:00', 7);

-- --------------------------------------------------------

--
-- Table structure for table `WORK_HOURS_RESTRICTION`
--

DROP TABLE IF EXISTS `WORK_HOURS_RESTRICTION`;
CREATE TABLE `WORK_HOURS_RESTRICTION` (
  `id` int NOT NULL,
  `week_day` int NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `fk_mechanic` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Dumping data for table `WORK_HOURS_RESTRICTION`
--

INSERT INTO `WORK_HOURS_RESTRICTION` (`id`, `week_day`, `start_time`, `end_time`, `fk_mechanic`) VALUES
(22, 3, '07:00:00', '07:45:00', 7),
(24, 3, '21:00:00', '22:00:00', 7),
(25, 3, '20:00:00', '20:30:00', 7),
(33, 1, '00:00:00', '01:30:00', 8),
(35, 1, '01:30:00', '02:00:00', 8),
(36, 2, '08:00:00', '08:30:00', 8),
(37, 2, '08:30:00', '08:45:00', 8);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `FAQ`
--
ALTER TABLE `FAQ`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `REGISTRATION`
--
ALTER TABLE `REGISTRATION`
  ADD PRIMARY KEY (`id`),
  ADD KEY `handles` (`fk_mechanic`),
  ADD KEY `leads_to` (`fk_completion`),
  ADD KEY `makes` (`fk_client`);

--
-- Indexes for table `REVIEW`
--
ALTER TABLE `REVIEW`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creates` (`fk_client`),
  ADD KEY `gets` (`fk_mechanic`);

--
-- Indexes for table `SERVICE`
--
ALTER TABLE `SERVICE`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `SERVICE_COMPLETION`
--
ALTER TABLE `SERVICE_COMPLETION`
  ADD PRIMARY KEY (`id`),
  ADD KEY `relates_to` (`fk_service`),
  ADD KEY `performs` (`fk_mechanic`);

--
-- Indexes for table `SYSTEM_USER`
--
ALTER TABLE `SYSTEM_USER`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_role` (`fk_role`);

--
-- Indexes for table `USER_ROLE`
--
ALTER TABLE `USER_ROLE`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `WORK_HOURS`
--
ALTER TABLE `WORK_HOURS`
  ADD PRIMARY KEY (`id`),
  ADD KEY `has` (`fk_mechanic`);

--
-- Indexes for table `WORK_HOURS_RESTRICTION`
--
ALTER TABLE `WORK_HOURS_RESTRICTION`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cant_work_on` (`fk_mechanic`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `FAQ`
--
ALTER TABLE `FAQ`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `REGISTRATION`
--
ALTER TABLE `REGISTRATION`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `REVIEW`
--
ALTER TABLE `REVIEW`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `SERVICE`
--
ALTER TABLE `SERVICE`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `SERVICE_COMPLETION`
--
ALTER TABLE `SERVICE_COMPLETION`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `SYSTEM_USER`
--
ALTER TABLE `SYSTEM_USER`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `USER_ROLE`
--
ALTER TABLE `USER_ROLE`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `WORK_HOURS`
--
ALTER TABLE `WORK_HOURS`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `WORK_HOURS_RESTRICTION`
--
ALTER TABLE `WORK_HOURS_RESTRICTION`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `REGISTRATION`
--
ALTER TABLE `REGISTRATION`
  ADD CONSTRAINT `handles` FOREIGN KEY (`fk_mechanic`) REFERENCES `SYSTEM_USER` (`id`),
  ADD CONSTRAINT `leads_to` FOREIGN KEY (`fk_completion`) REFERENCES `SERVICE_COMPLETION` (`id`),
  ADD CONSTRAINT `makes` FOREIGN KEY (`fk_client`) REFERENCES `SYSTEM_USER` (`id`);

--
-- Constraints for table `REVIEW`
--
ALTER TABLE `REVIEW`
  ADD CONSTRAINT `creates` FOREIGN KEY (`fk_client`) REFERENCES `SYSTEM_USER` (`id`),
  ADD CONSTRAINT `gets` FOREIGN KEY (`fk_mechanic`) REFERENCES `SYSTEM_USER` (`id`);

--
-- Constraints for table `SERVICE_COMPLETION`
--
ALTER TABLE `SERVICE_COMPLETION`
  ADD CONSTRAINT `performs` FOREIGN KEY (`fk_mechanic`) REFERENCES `SYSTEM_USER` (`id`),
  ADD CONSTRAINT `relates_to` FOREIGN KEY (`fk_service`) REFERENCES `SERVICE` (`id`);

--
-- Constraints for table `SYSTEM_USER`
--
ALTER TABLE `SYSTEM_USER`
  ADD CONSTRAINT `SYSTEM_USER_ibfk_1` FOREIGN KEY (`fk_role`) REFERENCES `USER_ROLE` (`id`);

--
-- Constraints for table `WORK_HOURS`
--
ALTER TABLE `WORK_HOURS`
  ADD CONSTRAINT `has` FOREIGN KEY (`fk_mechanic`) REFERENCES `SYSTEM_USER` (`id`);

--
-- Constraints for table `WORK_HOURS_RESTRICTION`
--
ALTER TABLE `WORK_HOURS_RESTRICTION`
  ADD CONSTRAINT `cant_work_on` FOREIGN KEY (`fk_mechanic`) REFERENCES `SYSTEM_USER` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
