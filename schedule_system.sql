-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3306
-- Время создания: Ноя 09 2024 г., 20:20
-- Версия сервера: 10.6.18-MariaDB-0ubuntu0.22.04.1
-- Версия PHP: 8.1.2-1ubuntu2.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `schedule_system`
--

-- --------------------------------------------------------

--
-- Структура таблицы `colors`
--

CREATE TABLE `colors` (
  `color_id` bigint(20) NOT NULL,
  `color_rgb` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `colors`
--

INSERT INTO `colors` (`color_id`, `color_rgb`) VALUES
(1, '29,29,29'),
(2, '12,166,120'),
(3, '240,73,57'),
(4, '51,71,255'),
(5, '247,103,7');

-- --------------------------------------------------------

--
-- Структура таблицы `date`
--

CREATE TABLE `date` (
  `date_id` bigint(20) NOT NULL,
  `date_ymd` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Структура таблицы `meeting`
--

CREATE TABLE `meeting` (
  `meet_id` bigint(20) NOT NULL,
  `date_id` bigint(20) DEFAULT NULL,
  `time_id` bigint(20) DEFAULT NULL,
  `delete_user_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Структура таблицы `meet_data`
--

CREATE TABLE `meet_data` (
  `meet_data_id` bigint(20) NOT NULL,
  `meet_id` bigint(20) DEFAULT NULL,
  `meet_data_time` varchar(10) DEFAULT NULL,
  `meet_data_text` text DEFAULT NULL,
  `meet_data_file` varchar(255) DEFAULT NULL,
  `color_id` bigint(20) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `meet_data_registered` timestamp NULL DEFAULT current_timestamp(),
  `meet_data_notified` tinyint(1) NOT NULL DEFAULT 0,
  `meet_users` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Структура таблицы `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL,
  `permission_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `permissions`
--

INSERT INTO `permissions` (`permission_id`, `permission_name`) VALUES
(1, 'Пользователь'),
(2, 'Редактор'),
(3, 'Администратор'),
(4, 'Супер-Администратор');

-- --------------------------------------------------------

--
-- Структура таблицы `schedule_user_notifications`
--

CREATE TABLE `schedule_user_notifications` (
  `schedule_id` bigint(20) NOT NULL,
  `user_number` bigint(20) DEFAULT NULL,
  `meet_id` bigint(20) DEFAULT NULL,
  `meet_read` tinyint(1) DEFAULT 0,
  `schedule_registered` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Структура таблицы `time`
--

CREATE TABLE `time` (
  `time_id` bigint(20) NOT NULL,
  `time_hours` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `time`
--

INSERT INTO `time` (`time_id`, `time_hours`) VALUES
(1, '8:00'),
(2, '9:00'),
(3, '10:00'),
(4, '11:00'),
(5, '12:00'),
(6, '13:00'),
(7, '14:00'),
(8, '15:00'),
(9, '16:00'),
(10, '17:00'),
(11, '18:00'),
(12, '19:00'),
(13, '20:00');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) NOT NULL,
  `user_name` varchar(35) DEFAULT NULL,
  `user_surname` varchar(35) DEFAULT NULL,
  `user_middlename` varchar(35) DEFAULT NULL,
  `user_number` bigint(20) DEFAULT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `user_password` varchar(160) DEFAULT NULL,
  `permission_id` int(11) DEFAULT 1,
  `user_access_token` varchar(120) DEFAULT NULL,
  `user_registered` timestamp NULL DEFAULT current_timestamp(),
  `user_admin_id` bigint(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `user_surname`, `user_middlename`, `user_number`, `user_email`, `user_password`, `permission_id`, `user_access_token`, `user_registered`, `user_admin_id`) VALUES
(2, 'Admin', 'Admin', 'Admin', 77000000000, 'admin@example.com', '468f5077dfa51ac178c1aab6c4935bd2bd8844bc780afb1958c218e39ed2d971', 4, 'bf15e1384e544b9b6e4e628e79d769b8', '2024-05-19 18:50:06', NULL);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`color_id`);

--
-- Индексы таблицы `date`
--
ALTER TABLE `date`
  ADD PRIMARY KEY (`date_id`);

--
-- Индексы таблицы `meeting`
--
ALTER TABLE `meeting`
  ADD PRIMARY KEY (`meet_id`);

--
-- Индексы таблицы `meet_data`
--
ALTER TABLE `meet_data`
  ADD PRIMARY KEY (`meet_data_id`);

--
-- Индексы таблицы `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`);

--
-- Индексы таблицы `schedule_user_notifications`
--
ALTER TABLE `schedule_user_notifications`
  ADD PRIMARY KEY (`schedule_id`);

--
-- Индексы таблицы `time`
--
ALTER TABLE `time`
  ADD PRIMARY KEY (`time_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `colors`
--
ALTER TABLE `colors`
  MODIFY `color_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `date`
--
ALTER TABLE `date`
  MODIFY `date_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT для таблицы `meeting`
--
ALTER TABLE `meeting`
  MODIFY `meet_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=217;

--
-- AUTO_INCREMENT для таблицы `meet_data`
--
ALTER TABLE `meet_data`
  MODIFY `meet_data_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=214;

--
-- AUTO_INCREMENT для таблицы `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `schedule_user_notifications`
--
ALTER TABLE `schedule_user_notifications`
  MODIFY `schedule_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=542;

--
-- AUTO_INCREMENT для таблицы `time`
--
ALTER TABLE `time`
  MODIFY `time_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
