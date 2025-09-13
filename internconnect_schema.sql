-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2025 at 03:49 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `internconnect`
--

-- --------------------------------------------------------

--
-- Table structure for table `academe_accounts`
--

CREATE TABLE `academe_accounts` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `section_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academe_accounts`
--

INSERT INTO `academe_accounts` (`user_id`, `section_id`, `created_at`, `updated_at`) VALUES
(4, 1, '2025-09-11 13:47:48', '2025-09-11 13:47:48');

-- --------------------------------------------------------

--
-- Table structure for table `advisers`
--

CREATE TABLE `advisers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `adviser_fname` varchar(50) NOT NULL,
  `adviser_lname` varchar(50) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `section_id` int(10) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `advisers`
--

INSERT INTO `advisers` (`id`, `adviser_fname`, `adviser_lname`, `is_active`, `section_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'Emmanuel', 'Santos', 1, 1, 3, '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(2, 'Maria', 'Garcia', 1, 2, 20, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(3, 'John', 'Doe', 1, 3, 21, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(4, 'Sarah', 'Wilson', 1, 4, 22, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(5, 'Michael', 'Brown', 0, 5, 23, '2025-09-11 13:47:50', '2025-09-11 13:47:50');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('internconnect_bulsu_cache_clairo@example.com|::1', 'i:1;', 1757598610),
('internconnect_bulsu_cache_clairo@example.com|::1:timer', 'i:1757598610;', 1757598610);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `created_at`, `updated_at`) VALUES
(1, 'Language Proficiency', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(2, 'Technical Skill', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(3, 'Soft Skill', '2025-09-11 13:47:48', '2025-09-11 13:47:48');

-- --------------------------------------------------------

--
-- Table structure for table `deadlines`
--

CREATE TABLE `deadlines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('active','expired') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_verification_attempts`
--

CREATE TABLE `email_verification_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `attempt_count` int(11) NOT NULL DEFAULT 1,
  `last_attempt_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `next_attempt_allowed_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `htes`
--

CREATE TABLE `htes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `company_address` varchar(255) DEFAULT NULL,
  `company_email` varchar(100) DEFAULT NULL,
  `cperson_fname` varchar(50) DEFAULT NULL,
  `cperson_lname` varchar(50) DEFAULT NULL,
  `cperson_position` varchar(50) DEFAULT NULL,
  `cperson_contactnum` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_submit` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `htes`
--

INSERT INTO `htes` (`id`, `user_id`, `company_name`, `company_address`, `company_email`, `cperson_fname`, `cperson_lname`, `cperson_position`, `cperson_contactnum`, `is_active`, `is_submit`, `created_at`, `updated_at`) VALUES
(1, 5, 'TechCorp Solutions', '123 Innovation Drive, Tech City, TC 12345', 'hr@techcorp.com', 'John', 'Smith', 'HR Manager', '+1-555-0101', 1, 0, '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(2, 6, 'DataFlow Analytics', '456 Data Street, Analytics Town, AT 67890', 'internships@dataflow.com', 'Sarah', 'Johnson', 'Talent Acquisition Specialist', '+1-555-0102', 1, 0, '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(3, 7, 'GreenTech Industries', '789 Eco Boulevard, Green City, GC 11111', 'careers@greentech.com', 'Michael', 'Brown', 'Recruitment Coordinator', '+1-555-0103', 1, 0, '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(4, 8, 'Creative Marketing Pro', '321 Creative Lane, Marketing City, MC 22222', 'hr@creativemarketing.com', 'Emily', 'Davis', 'HR Director', '+1-555-0104', 1, 0, '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(5, 9, 'FinanceFirst Bank', '654 Finance Avenue, Banking City, BC 33333', 'internships@financefirst.com', 'David', 'Wilson', 'Talent Manager', '+1-555-0105', 1, 0, '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(6, 10, 'Borer, Batz and Ratke', '44734 Moises Shoal\nSporertown, CA 50257', 'pfannerstill.grady@kuphal.com', 'Shakira', 'Conn', 'HR Manager', '+1-979-844-7298', 1, 0, '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(7, 11, 'Dare, Reichel and Schulist', '4090 Cristopher Brooks Apt. 696\nQuitzonmouth, OR 78290-8385', 'bergstrom.josh@wisozk.com', 'Floyd', 'Boyle', 'HR Director', '+1-774-814-0394', 1, 0, '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(8, 12, 'Bins Group', '5393 Alverta Circles\nPort Ernest, MO 03485', 'salvatore61@jast.com', 'Elna', 'Rohan', 'HR Director', '+1-913-790-6515', 1, 0, '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(9, 13, 'Mosciski-Gutmann', '543 Natalia Walks\nLake Georgetteborough, ME 72372-4761', 'swaniawski.rodolfo@dietrich.com', 'Weldon', 'Gusikowski', 'Talent Acquisition', '+1-339-645-3337', 1, 0, '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(10, 14, 'Thompson, Ferry and Schaden', '751 Goldner Overpass Suite 667\nTillmanmouth, CT 26788-3278', 'csatterfield@osinski.com', 'Consuelo', 'Koch', 'Talent Manager', '+1.805.535.8993', 1, 0, '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(11, 15, 'Huels, Fritsch and Donnelly', '550 Bayer Pike Suite 762\nAdelineton, PA 95763', 'raphael08@feil.com', 'Lincoln', 'Erdman', 'Talent Manager', '1-847-719-8219', 1, 0, '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(12, 16, 'Grady-Collier', '243 Jany Forest Suite 109\nSouth Meghanfurt, NE 72621-2223', 'satterfield.kaleigh@bergstrom.com', 'Zaria', 'Rolfson', 'Recruitment Specialist', '+18455720194', 1, 0, '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(13, 17, 'Nienow and Sons', '49494 Connelly Walks Suite 545\nSouth Edwin, WI 49435-4366', 'oframi@strosin.biz', 'Eveline', 'Boyle', 'Talent Acquisition', '404.781.1775', 1, 0, '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(14, 18, 'Heller-Wunsch', '587 Cole Points Apt. 789\nLake Agustin, MA 52728-8169', 'hessel.maritza@skiles.com', 'Rowena', 'Mueller', 'Talent Acquisition', '+1 (934) 574-3609', 1, 0, '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(15, 19, 'Kerluke-Will', '14338 Jeffry Loaf\nEast Stefanie, SC 79344', 'quinton.stiedemann@daniel.com', 'Arely', 'Schamberger', 'Talent Manager', '+1-419-350-0313', 1, 0, '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(16, 24, 'Klein, Kautzer and Strosin', '4718 Lueilwitz Walk Suite 190\nKimberlyport, MD 97117', 'connelly.jaquelin@maggio.com', 'Georgianna', 'Cummerata', 'HR Manager', '480-385-0433', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(17, 25, 'Dickinson Inc', '92714 Mireille Junctions Suite 214\nNorth Marcelinoton, CT 09677-0889', 'howell61@keebler.net', 'Krystel', 'Yundt', 'Recruitment Specialist', '+1 (351) 254-2469', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(18, 26, 'Homenick, Ryan and Mohr', '11825 Tessie Ville Suite 859\nKeiraborough, AL 49434', 'austin.murphy@wiegand.com', 'Cornell', 'Wunsch', 'Talent Acquisition', '864-690-4413', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(19, 27, 'Dickinson PLC', '3615 Wehner Forest Apt. 655\nNikkoborough, TN 02730', 'enid23@kemmer.info', 'Leonora', 'Prohaska', 'HR Director', '1-763-380-0207', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(20, 28, 'Littel Ltd', '33375 Upton Keys Apt. 337\nNorth Bridgetteview, TX 30542', 'garett94@wyman.org', 'Dashawn', 'Rowe', 'Recruitment Specialist', '(872) 959-2577', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(21, 29, 'Erdman and Sons', '5343 Hill Forest Suite 927\nJevonberg, AZ 11924', 'rosemarie22@marquardt.info', 'Sydnee', 'Herman', 'HR Manager', '+1 (517) 851-5678', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(22, 30, 'Ziemann and Sons', '956 Nikolaus Ways\nLake Yesenia, NY 25123-1170', 'qparisian@blanda.info', 'Aileen', 'Rowe', 'Recruitment Specialist', '516-234-2037', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(23, 31, 'Fadel-Hermiston', '815 Jones Ranch\nColbyside, IN 15890', 'garry.block@hegmann.com', 'Reid', 'Barrows', 'Talent Manager', '+1 (689) 559-9981', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(24, 32, 'Robel PLC', '94986 Considine Brook Suite 555\nLednermouth, UT 23887', 'nicklaus40@bergnaum.info', 'Josefa', 'Kiehn', 'HR Manager', '229-510-7837', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(25, 33, 'Hill, Stokes and Bauch', '81974 Waelchi Hollow Apt. 646\nWest Demetrisfort, NH 31437-6171', 'wbayer@mayer.com', 'Naomi', 'Kshlerin', 'HR Manager', '878-794-9108', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(26, 34, 'Gleichner, Deckow and Wiegand', '6140 Bernier Ville Apt. 942\nSouth Grady, IN 75979-9591', 'anderson.skye@oconnell.com', 'Billy', 'Friesen', 'Talent Manager', '231-362-3458', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(27, 35, 'Buckridge-Jakubowski', '7979 Quitzon Pike\nWest Cassandra, CT 22495-9739', 'armstrong.beau@wolf.info', 'Emilia', 'Kuhlman', 'Talent Manager', '423.302.5115', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(28, 36, 'Ondricka, Sporer and Langworth', '7005 Douglas Greens Suite 685\nWest Murphy, GA 47109-7966', 'arch.kreiger@daugherty.com', 'Cayla', 'Ziemann', 'HR Director', '+1 (941) 581-9893', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(29, 37, 'Dicki-Veum', '451 Granville Green Suite 225\nNorth Prudencehaven, MT 95204-5123', 'daugherty.santiago@wiza.com', 'Heidi', 'Simonis', 'HR Director', '+1-954-264-2355', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(30, 38, 'Ledner Inc', '51764 Crist Summit Suite 939\nHackettmouth, WV 58054-7519', 'hettinger.berry@jerde.com', 'Haylee', 'Rath', 'Recruitment Specialist', '605.333.9260', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(31, 39, 'Wolf-Howell', '7980 Auer Valleys\nHermistonfort, MD 81162-7629', 'drutherford@koelpin.com', 'Kay', 'Nolan', 'HR Manager', '(640) 762-6437', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(32, 40, 'Hoeger-Fritsch', '86041 Arch Mill\nPort Sofiashire, FL 85691-7312', 'reilly.ari@smith.net', 'Elaina', 'Pacocha', 'HR Manager', '1-223-781-2913', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(33, 41, 'Keeling-Smith', '767 Wolf Greens Suite 707\nLayneshire, RI 43019', 'magnus.moen@thiel.biz', 'Fay', 'Dare', 'HR Director', '+16147307360', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(34, 42, 'Bogisich-Denesik', '1815 Ruecker Ports Suite 515\nGerholdbury, ME 14776', 'bsporer@goyette.com', 'Casper', 'Haag', 'Talent Manager', '817-843-5785', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(35, 43, 'Strosin, Nikolaus and Wilkinson', '3919 Jesse Rue\nRickymouth, GA 86863-3820', 'braulio.parker@wyman.com', 'Lonnie', 'Hayes', 'HR Director', '952-502-0214', 1, 0, '2025-09-11 13:47:50', '2025-09-11 13:47:50');

-- --------------------------------------------------------

--
-- Table structure for table `internships`
--

CREATE TABLE `internships` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `hte_id` bigint(20) UNSIGNED NOT NULL,
  `position_title` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `placement_description` text NOT NULL,
  `slot_count` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `internships`
--

INSERT INTO `internships` (`id`, `hte_id`, `position_title`, `department`, `placement_description`, `slot_count`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Software Development Intern', 'Information Technology', 'Join our development team and work on real-world projects using modern technologies like React, Node.js, and Python. You will participate in code reviews, attend team meetings, and contribute to our product development process.', 3, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(2, 1, 'Data Science Intern', 'Information Technology', 'Work with our data science team to analyze large datasets, build predictive models, and create data visualizations. Experience with Python, SQL, and machine learning frameworks is preferred.', 2, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(3, 2, 'Data Analytics Intern', 'Research & Development', 'Assist in collecting, cleaning, and analyzing data to provide insights for business decisions. You will work with tools like Tableau, Power BI, and SQL.', 4, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(4, 2, 'Business Intelligence Intern', 'Operations', 'Help develop and maintain dashboards and reports for various business units. Experience with data visualization and business intelligence tools is a plus.', 2, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(5, 3, 'Environmental Research Intern', 'Research & Development', 'Conduct research on sustainable technologies and environmental impact assessments. You will work with our sustainability team to develop eco-friendly solutions.', 3, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(6, 3, 'Marketing Intern', 'Marketing', 'Support our marketing team in promoting our green technology solutions. You will help create content, manage social media, and assist with marketing campaigns.', 2, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(7, 4, 'Digital Marketing Intern', 'Marketing', 'Learn digital marketing strategies including SEO, SEM, social media marketing, and content creation. You will work on real client campaigns and track performance metrics.', 5, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(8, 4, 'Graphic Design Intern', 'Marketing', 'Create visual content for various marketing campaigns including social media graphics, website assets, and print materials. Proficiency in Adobe Creative Suite is preferred.', 2, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(9, 5, 'Finance Intern', 'Finance', 'Assist with financial analysis, budgeting, and reporting. You will work with our finance team to analyze financial data and prepare reports for management.', 3, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(10, 5, 'Risk Management Intern', 'Operations', 'Support our risk management team in identifying, assessing, and monitoring various types of risks. You will help develop risk mitigation strategies.', 2, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(11, 16, 'Research Intern', 'Operations', 'Natus minima non consequuntur et nisi praesentium voluptatem. Iure ducimus sed placeat fugit ea. Est quia dolorem occaecati ipsa qui. Aut consectetur molestiae dolorem quis distinctio.\n\nDolor autem dolor cumque in. Ea voluptate eveniet odit voluptatem adipisci. Dolores aut repellat error non necessitatibus et.\n\nSunt sed et maxime id omnis provident. Placeat est nisi consectetur aspernatur earum magnam. Officiis voluptas placeat iure reiciendis ducimus.', 4, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(12, 17, 'Content Writing Intern', 'Human Resources', 'Quia eum quidem et perspiciatis. Rerum omnis tempore quam corporis vitae officia quis. Sapiente dolores eius ut atque ratione unde beatae.\n\nVeritatis numquam blanditiis sed et molestiae. Et qui est ipsam magni numquam a aut facilis. Est suscipit nihil in tempore corporis.\n\nReprehenderit nulla soluta voluptates qui qui nostrum molestiae. Inventore rerum consectetur ut aut et aut. Ex asperiores alias ratione voluptatum. Facere accusantium accusantium vel error perferendis impedit ex. Quo numquam magni praesentium magnam dolorem excepturi magni.', 5, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(13, 18, 'Content Writing Intern', 'Customer Service', 'Accusantium eius ratione quam blanditiis explicabo voluptatem sed. Sint mollitia tempore ipsum non sunt non. Accusantium est ipsam iste aut quo omnis explicabo.\n\nEos voluptatum quas quo non. Quas ut nam laborum incidunt fugit voluptas ad repellat. Aut molestiae voluptatem corporis id. Ut dignissimos eum qui ratione laboriosam velit.\n\nDolores repellendus qui error ut et tempora. Sapiente aliquam velit minima impedit est. Suscipit soluta vero occaecati molestias necessitatibus.', 5, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(14, 19, 'Content Writing Intern', 'Customer Service', 'Qui voluptatem numquam adipisci non voluptas magni. Ipsum corrupti magni autem dignissimos vitae. Labore ut minus quia.\n\nVoluptatem dolor deserunt exercitationem voluptas qui. Molestias et at nemo enim quis repellat. Eum quia repellendus aut.\n\nVoluptas magni consequatur esse ipsa facilis ratione aut. Eius voluptates similique voluptas. Et magni molestiae aut ratione eum cupiditate rem.', 5, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(15, 20, 'Human Resources Intern', 'Legal', 'Quisquam quis voluptatem quia dolorem et illum. Ut quibusdam saepe velit id magnam unde. Aut aspernatur sit sit voluptas quos eos corporis velit. Mollitia at qui qui natus deleniti.\n\nQuos aut quod ullam est fugit. Reprehenderit eligendi autem ea quis voluptate. Dolorum quae enim qui laborum vitae aut.\n\nAdipisci veniam labore repellat quisquam aut itaque eum. Quo sit est voluptatum vel eum et nulla. Illum non ut unde qui dolor dolorem.', 2, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(16, 21, 'Software Development Intern', 'Information Technology', 'Voluptate modi voluptatem laudantium dolor qui fugiat in assumenda. Minus quis commodi mollitia ipsum. Architecto a dolor corrupti voluptatum perspiciatis voluptatibus. Illo dolorem alias laborum ut.\n\nSit voluptatem in molestiae soluta et tempore est. Dolorem harum consectetur natus aut dicta. Eaque dolores cupiditate autem est ut. Perspiciatis qui sapiente reprehenderit eligendi voluptas.\n\nTempora odio dolores error velit voluptas. In aut qui odit neque dignissimos in sint. Et blanditiis est est iusto iure. Similique officiis perspiciatis at id voluptate voluptas dolor.', 3, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(17, 22, 'Content Writing Intern', 'Communications', 'Id voluptatibus repellat sit eos neque optio quisquam omnis. Corrupti at impedit consectetur nam atque quis incidunt. Debitis fugit consequatur adipisci inventore nisi.\n\nFugit reiciendis cum necessitatibus saepe ducimus eum. Molestiae et qui eum qui facere. Ipsa ipsum dolorum eligendi ab. Consequuntur amet quo qui consequatur.\n\nIpsa rem aliquid odit voluptas sunt ut. Recusandae facilis molestiae non labore voluptas a consequatur. Aut velit quas quas et eveniet vel ducimus. Saepe nihil dolorem dolorem possimus debitis.', 5, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(18, 23, 'Finance Intern', 'Communications', 'Consequuntur recusandae voluptatem quaerat ut minus sunt. Dolore et dolorem nesciunt. Et tenetur expedita vero id dolorum vitae. Sed eos dolorem harum dolorem exercitationem et nihil.\n\nEa quia maxime voluptatum atque. Cum quas labore aut pariatur provident nihil. Et error animi tempora. Temporibus et magni iusto omnis esse soluta. Exercitationem temporibus aut hic.\n\nSimilique at blanditiis aliquid aliquid esse. Voluptate modi rem corrupti itaque quia repudiandae. Minus et ab atque dolorum voluptatum quas. Explicabo accusamus rerum aut velit non.', 2, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(19, 24, 'Research Intern', 'Human Resources', 'Aperiam exercitationem culpa qui. Vel reprehenderit molestiae laudantium.\n\nNon beatae aut nostrum. Laboriosam nostrum nemo magnam quis.\n\nAut voluptates velit impedit repellat ad. Similique sit ut tempora perspiciatis iusto. Quas omnis numquam tempora consequatur consectetur necessitatibus totam odit.', 3, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(20, 25, 'Finance Intern', 'Sales', 'Quidem dolorem reprehenderit quod ut dolor molestiae. Eveniet quis ullam laboriosam mollitia earum. Sed sit excepturi incidunt numquam soluta qui sapiente.\n\nDolorem iusto rerum cupiditate. Dicta aperiam nulla consequatur ducimus est sint. Delectus tempora dolorum accusamus ut nesciunt aspernatur. Qui fuga tempore et.\n\nArchitecto non nostrum consequuntur. Vero ea et nam. Maxime alias enim doloribus impedit. Modi necessitatibus sit eveniet molestias.', 1, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(21, 26, 'Customer Service Intern', 'Customer Service', 'Aspernatur rerum aut aut. Pariatur minus adipisci eos. A est quam dolor molestiae nobis non. Dolore ducimus quasi atque vel vitae. Veniam repellendus in sed.\n\nMagni sit libero sit atque nesciunt. Voluptatem non animi odit maxime odio tempore eveniet.\n\nOfficia vel ut explicabo et soluta. Voluptatem ipsam rerum ut magnam deleniti. Necessitatibus est vero nobis ipsa sed. Hic nemo quos qui reprehenderit reiciendis laudantium.', 4, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(22, 27, 'Finance Intern', 'Communications', 'Ipsam cupiditate eum molestiae cumque id voluptatem distinctio. Dicta fugiat velit architecto velit aut nisi consequatur. Voluptatem assumenda ratione ullam vero.\n\nOmnis repudiandae saepe deleniti voluptates. Aliquid dolorum accusantium ipsum minima non aut. Commodi recusandae nam optio non.\n\nVelit qui eos ipsam eos repellendus. Ipsam voluptas expedita dolor eligendi dolores consequatur. Aut quos voluptatem qui est provident omnis et. Explicabo ex eligendi ut officiis repellendus tempore sequi.', 5, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(23, 28, 'Software Development Intern', 'Communications', 'Blanditiis dolores accusantium hic sunt fugiat quidem reiciendis. Debitis iusto provident libero. Dolorem ad maxime dignissimos quas debitis autem. Nesciunt dolor ratione est unde.\n\nEnim qui assumenda consequatur veritatis adipisci odio. Totam omnis dolor modi. Numquam dolor excepturi consequatur ratione voluptatem commodi. Eveniet consequatur expedita id dolorem accusantium fuga sapiente ut.\n\nIllum dicta dolor repellendus molestiae veniam eos. Voluptatum maiores rerum qui omnis molestias. Eius dolor eos laudantium quia saepe beatae id delectus. Omnis perferendis et quod.', 4, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(24, 29, 'Software Development Intern', 'Customer Service', 'Quibusdam porro reprehenderit qui et unde exercitationem. Eveniet voluptatibus sequi aliquid culpa et. Eos consequatur vel similique aspernatur.\n\nSint adipisci quia ea explicabo delectus sunt inventore. Ab tempore nihil nulla nihil. Et quia neque doloribus repellendus. Ut placeat nostrum rerum ut voluptate.\n\nAut culpa unde et consequatur minima ea. Culpa beatae mollitia temporibus pariatur molestiae laborum ipsa.', 3, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(25, 30, 'Software Development Intern', 'Marketing', 'Quisquam cumque ut ut voluptates sit. Expedita odio accusamus voluptatibus aut ut ut facere enim. Sit repellat deleniti sunt aut tempore fugit at quasi. Quod hic numquam a quasi beatae cum numquam temporibus.\n\nVero rem et aliquid ex qui autem. Culpa rerum impedit ipsum accusamus voluptas est.\n\nAd nihil inventore dolores quidem harum. Iure exercitationem veritatis cupiditate qui quasi. Voluptatem quibusdam omnis rerum architecto.', 5, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(26, 31, 'Content Writing Intern', 'Legal', 'Architecto nisi exercitationem est non rem et. Ipsum quam ex culpa sit eum consequatur. Reprehenderit ut eum adipisci qui at. Sapiente repudiandae molestiae vero sit at.\n\nQuod maxime sit quas. Dolor id rerum voluptas consequuntur sed in. Rerum magnam ea enim sed consequuntur vero et ullam.\n\nNulla autem quos enim eos totam et. Laudantium alias dolor corrupti assumenda et vero quidem. Dicta aut eligendi necessitatibus et reiciendis.', 3, 1, '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(27, 32, 'Marketing Intern', 'Sales', 'Nulla ipsa ipsum non consequuntur laboriosam. Assumenda enim quod magnam ipsum rerum.\n\nArchitecto sit pariatur dicta placeat. Autem aspernatur placeat quia qui. Ea aut saepe et dolore.\n\nExcepturi ut culpa ut dolorum est aliquid. Quis fuga ut nulla nam excepturi eum. Adipisci et et ad iste sed. Porro placeat nihil aliquid dolorem ut reiciendis.', 3, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(28, 33, 'Finance Intern', 'Human Resources', 'Et consectetur dolorem maiores. Maiores magni provident sint facilis beatae rem ullam. Vitae perferendis accusamus enim id est. Maiores earum impedit sed. Dolore modi quibusdam doloremque iure.\n\nSunt et fugiat eum aliquid voluptatem totam repudiandae. Dolor perferendis dolorem dolore vel.\n\nDolores quia error dignissimos molestiae velit hic sunt. Dolor itaque id animi qui ipsam architecto fugiat. Esse nihil qui consequuntur sapiente impedit aut.', 1, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(29, 34, 'Finance Intern', 'Marketing', 'Voluptatem et adipisci dolores id non. Doloremque quia possimus possimus deserunt. Officiis incidunt magnam similique. Vero aspernatur cupiditate dolorum quas laboriosam consequatur.\n\nQuisquam animi deserunt enim aliquam ipsum quo. Quos dolorum commodi eligendi consectetur. Dolore quidem voluptatem molestiae nihil corrupti voluptatem vero odit. Ut ex veritatis sunt commodi ut excepturi.\n\nVel consequatur ex eos et molestias et. Dolor ut quo et expedita ut voluptates maiores. Fuga praesentium quae minima omnis amet aspernatur sit.', 1, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(30, 35, 'Human Resources Intern', 'Operations', 'Eius facere sint vero labore vel alias. Vitae nihil ratione voluptatem itaque eum. Voluptas explicabo porro illo odio. Architecto ipsum doloribus ut eius.\n\nNon sed eos ut laudantium illo. Eligendi ipsam possimus aut tempore. Beatae alias ut aut temporibus aut sunt debitis.\n\nQui assumenda qui laboriosam molestiae delectus iste hic quaerat. A dolor dolorem et reiciendis sed quia. Nihil facilis quae quidem sit.', 2, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(31, 3, 'Cybersecurity Intern', 'Information Security', 'Assist in cybersecurity operations, threat analysis, and security assessments. Learn about network security, vulnerability management, and incident response.', 3, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(32, 11, 'UX/UI Design Intern', 'Product Design', 'Work on user experience and interface design projects. Learn design principles, prototyping tools, and user research methodologies.', 2, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(33, 28, 'AI/ML Intern', 'Artificial Intelligence', 'Contribute to machine learning projects, data preprocessing, and model development. Gain experience with AI frameworks and algorithms.', 2, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(34, 9, 'DevOps Intern', 'Infrastructure', 'Learn about continuous integration/deployment, cloud infrastructure, and automation tools. Work with Docker, Kubernetes, and CI/CD pipelines.', 2, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(35, 17, 'Mobile App Development Intern', 'Mobile Development', 'Develop mobile applications for iOS and Android platforms. Learn mobile development frameworks and best practices.', 3, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(36, 20, 'Quality Assurance Intern', 'Quality Assurance', 'Learn software testing methodologies, automated testing tools, and quality assurance processes. Contribute to ensuring software quality.', 2, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(37, 8, 'Business Intelligence Intern', 'Business Intelligence', 'Work with data warehousing, reporting tools, and business analytics. Learn to create dashboards and data-driven insights.', 2, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(38, 10, 'Cloud Computing Intern', 'Cloud Infrastructure', 'Learn about cloud platforms (AWS, Azure, GCP), infrastructure as code, and cloud-native development practices.', 2, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(39, 12, 'Game Development Intern', 'Game Development', 'Contribute to game development projects using Unity or Unreal Engine. Learn game design principles and development workflows.', 2, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(40, 2, 'Blockchain Intern', 'Blockchain Technology', 'Learn about blockchain technology, smart contracts, and decentralized applications. Work with blockchain platforms and tools.', 1, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(41, 24, 'IoT Development Intern', 'Internet of Things', 'Work on IoT projects involving sensors, embedded systems, and connected devices. Learn about IoT protocols and platforms.', 2, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(42, 5, 'Data Engineering Intern', 'Data Engineering', 'Learn about data pipelines, ETL processes, and big data technologies. Work with data processing frameworks and tools.', 2, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(43, 1, 'Frontend Development Intern', 'Frontend Development', 'Focus on modern frontend technologies like React, Vue.js, and Angular. Learn responsive design and user interface development.', 3, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(44, 22, 'Backend Development Intern', 'Backend Development', 'Work on server-side development, APIs, and database design. Learn about microservices architecture and backend frameworks.', 3, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(45, 22, 'Full Stack Development Intern', 'Full Stack Development', 'Gain experience in both frontend and backend development. Work on complete web applications and learn full-stack technologies.', 2, 1, '2025-09-11 13:47:51', '2025-09-11 13:47:51');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_06_17_033508_create_categories_table', 1),
(5, '2025_06_17_034508_create_sub_categories_table', 1),
(6, '2025_06_17_040929_create_hte_table', 1),
(7, '2025_06_17_041659_create_internships_table', 1),
(8, '2025_06_17_043120_create_subcategory_weights_table', 1),
(9, '2025_06_17_044526_create_questions_table', 1),
(10, '2025_06_17_044631_create_deadlines_table', 1),
(11, '2025_07_16_003715_create_permission_tables', 1),
(12, '2025_08_05_125841_create_sections_table', 1),
(13, '2025_08_05_125842_create_students_table', 1),
(14, '2025_08_05_125843_create_student_score_table', 1),
(15, '2025_08_05_125844_create_student_matches_table', 1),
(16, '2025_08_05_125845_create_student_placements_table', 1),
(17, '2025_08_05_125921_create_requests_table', 1),
(18, '2025_08_06_154622_create_academe_accounts_table', 1),
(19, '2025_08_31_120751_create_advisers_table', 1),
(20, '2025_09_03_080441_create_email_verification_attempts_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 4),
(1, 'App\\Models\\User', 44),
(1, 'App\\Models\\User', 45),
(1, 'App\\Models\\User', 46),
(1, 'App\\Models\\User', 47),
(1, 'App\\Models\\User', 48),
(1, 'App\\Models\\User', 49),
(1, 'App\\Models\\User', 50),
(1, 'App\\Models\\User', 51),
(2, 'App\\Models\\User', 2),
(2, 'App\\Models\\User', 5),
(2, 'App\\Models\\User', 6),
(2, 'App\\Models\\User', 7),
(2, 'App\\Models\\User', 8),
(2, 'App\\Models\\User', 9),
(2, 'App\\Models\\User', 10),
(2, 'App\\Models\\User', 11),
(2, 'App\\Models\\User', 12),
(2, 'App\\Models\\User', 13),
(2, 'App\\Models\\User', 14),
(2, 'App\\Models\\User', 15),
(2, 'App\\Models\\User', 16),
(2, 'App\\Models\\User', 17),
(2, 'App\\Models\\User', 18),
(2, 'App\\Models\\User', 19),
(2, 'App\\Models\\User', 24),
(2, 'App\\Models\\User', 25),
(2, 'App\\Models\\User', 26),
(2, 'App\\Models\\User', 27),
(2, 'App\\Models\\User', 28),
(2, 'App\\Models\\User', 29),
(2, 'App\\Models\\User', 30),
(2, 'App\\Models\\User', 31),
(2, 'App\\Models\\User', 32),
(2, 'App\\Models\\User', 33),
(2, 'App\\Models\\User', 34),
(2, 'App\\Models\\User', 35),
(2, 'App\\Models\\User', 36),
(2, 'App\\Models\\User', 37),
(2, 'App\\Models\\User', 38),
(2, 'App\\Models\\User', 39),
(2, 'App\\Models\\User', 40),
(2, 'App\\Models\\User', 41),
(2, 'App\\Models\\User', 42),
(2, 'App\\Models\\User', 43),
(3, 'App\\Models\\User', 1),
(4, 'App\\Models\\User', 3),
(4, 'App\\Models\\User', 20),
(4, 'App\\Models\\User', 21),
(4, 'App\\Models\\User', 22),
(4, 'App\\Models\\User', 23);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'manage users', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(2, 'login', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(3, 'manage profiles', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(4, 'approve internships', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(5, 'manage assessments', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(6, 'monitor applications', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(7, 'view dashboard metrics', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(8, 'edit company profile', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(9, 'post internships', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(10, 'set assessment criteria', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(11, 'reset password', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(12, 'register account', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(13, 'edit student profile', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(14, 'apply internships', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(15, 'complete assessments', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(16, 'view student applications', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question` text NOT NULL,
  `access` enum('HTE','Student') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `subcategory_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `question`, `access`, `is_active`, `subcategory_id`, `created_at`, `updated_at`) VALUES
(1, 'What is your proficiency level in Java?', 'Student', 1, 1, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(2, 'Have you worked with Java frameworks such as Spring?', 'Student', 1, 1, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(3, 'Can you write Java programs following OOP principles?', 'Student', 1, 1, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(4, 'Are you comfortable with C++ memory management?', 'Student', 1, 2, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(5, 'Have you used STL in C++ programming?', 'Student', 1, 2, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(6, 'Can you develop applications using C++ classes and objects?', 'Student', 1, 2, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(7, 'Do you have experience with Python scripting?', 'Student', 1, 3, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(8, 'Have you worked with Python frameworks like Django or Flask?', 'Student', 1, 3, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(9, 'Can you automate tasks using Python?', 'Student', 1, 3, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(10, 'Are you proficient in writing semantic HTML?', 'Student', 1, 4, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(11, 'Can you style websites effectively using CSS?', 'Student', 1, 4, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(12, 'Have you worked with CSS preprocessors like SASS or LESS?', 'Student', 1, 4, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(13, 'Do you have experience with vanilla JavaScript?', 'Student', 1, 5, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(14, 'Have you used any JS frameworks like React or Angular?', 'Student', 1, 5, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(15, 'Can you manipulate the DOM with JavaScript?', 'Student', 1, 5, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(16, 'Are you familiar with PHP syntax and features?', 'Student', 1, 6, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(17, 'Have you developed web applications using PHP?', 'Student', 1, 6, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(18, 'Can you work with PHP frameworks such as Laravel or CodeIgniter?', 'Student', 1, 6, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(19, 'Can you write complex SQL queries?', 'Student', 1, 7, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(20, 'Have you worked with database normalization?', 'Student', 1, 7, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(21, 'Do you know how to optimize SQL queries for performance?', 'Student', 1, 7, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(22, 'Designing Databases', 'Student', 1, 8, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(23, 'Writing SQL Queries', 'Student', 1, 8, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(24, 'Database Administration', 'Student', 1, 8, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(25, 'Using tools like MySQL, Oracle etc.', 'Student', 1, 8, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(26, 'Designing user interfaces (UI)', 'Student', 1, 9, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(27, 'Developing responsive websites', 'Student', 1, 9, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(28, 'Using front-end frameworks (e.g., Bootstrap, React)', 'Student', 1, 9, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(29, 'Back-end development (e.g., Node.js, Django)', 'Student', 1, 9, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(30, 'Gathering and analyzing requirements', 'Student', 1, 10, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(31, 'Software design and architecture', 'Student', 1, 10, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(32, 'Development using Agile/Scrum', 'Student', 1, 10, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(33, 'Testing and debugging applications', 'Student', 1, 10, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(34, 'System maintenance and troubleshooting', 'Student', 1, 10, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(35, 'Explaining technical concepts to non-technical people', 'Student', 1, 11, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(36, 'Collaborating with team members', 'Student', 1, 11, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(37, 'Writing clear documentation and reports', 'Student', 1, 11, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(38, 'Independently solve complex problems or debug issues?', 'Student', 1, 12, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(39, 'Research solutions before seeking help from others?', 'Student', 1, 12, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(40, 'Think critically when troubleshooting technical problems?', 'Student', 1, 12, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(41, 'Prioritizing tasks effectively', 'Student', 1, 13, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(42, 'Meeting project deadlines', 'Student', 1, 13, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(43, 'Adapt to new tools and technologies quickly?', 'Student', 1, 14, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(44, 'Show a willingness to learn independently?', 'Student', 1, 14, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(45, 'Stay updated on emerging IT trends?', 'Student', 1, 14, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(46, 'Data privacy and security protocols?', 'Student', 1, 15, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(47, 'Ethical issues like intellectual property rights?', 'Student', 1, 15, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(48, 'Punctuality and reliability', 'Student', 1, 16, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(49, 'Following company policies and procedures', 'Student', 1, 16, '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(50, 'Being receptive to constructive feedback and improving performance accordingly', 'Student', 1, 16, '2025-09-11 13:47:48', '2025-09-11 13:47:48');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int(10) UNSIGNED NOT NULL,
  `stud_num` varchar(10) NOT NULL,
  `section_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'student', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(2, 'hte', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(3, 'admin', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(4, 'adviser', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(5, 'guest', 'web', '2025-09-11 13:47:47', '2025-09-11 13:47:47');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 3),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(3, 3),
(4, 3),
(5, 3),
(6, 3),
(7, 3),
(8, 2),
(9, 2),
(10, 2),
(11, 1),
(11, 2),
(11, 4),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 4);

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(10) UNSIGNED NOT NULL,
  `section_name` varchar(10) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `section_name`, `status`, `created_at`, `updated_at`) VALUES
(1, '3A-G1', 'active', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(2, '3A-G2', 'active', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(3, '3A-G3', 'active', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(4, '3A-G4', 'active', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(5, '3B-G1', 'active', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(6, '3B-G2', 'active', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(7, '3B-G3', 'active', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(8, '3B-G4', 'active', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(9, '3C-G1', 'active', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(10, '3C-G2', 'active', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(11, '3C-G3', 'active', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(12, '3C-G4', 'active', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(13, 'BSIT-4A', 'active', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(14, 'BSIT-4B', 'active', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(15, 'BSIT-4C', 'active', '2025-09-11 13:47:48', '2025-09-11 13:47:48');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('ftAAl1fcIGran2g2RDOjhJVH7eH4guflQuicuiDT', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoic1BZYm1iRmRBOTdjSlNOTUo5YmV5dDZLMWhmdHRFRDVwekJJSWV5QiI7czoyMjoiUEhQREVCVUdCQVJfU1RBQ0tfREFUQSI7YToyOntzOjI2OiIwMUs0V0dLMk03QTJGRjlLMkZFNEsxS01SRiI7TjtzOjI2OiIwMUs0V0dLN1c3M1AwTTY1MDY2MjI0V0U3WSI7Tjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODA4MC9wcm9qZWN0cy9JbnRlcm5Db25uZWN0QnVsU1UvcHVibGljIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mzt9', 1757598558);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `section_id` int(10) UNSIGNED NOT NULL,
  `specialization` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `birth_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_submit` tinyint(1) NOT NULL DEFAULT 0,
  `is_placed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `student_number`, `first_name`, `middle_name`, `last_name`, `phone`, `section_id`, `specialization`, `address`, `birth_date`, `is_active`, `is_submit`, `is_placed`, `created_at`, `updated_at`) VALUES
(1, 44, '2021-0001', 'Juan', 'Santos', 'Dela Cruz', '09123456789', 13, 'Programming', '123 Main St, Quezon City', '2000-01-15', 1, 1, 0, '2025-09-11 13:47:51', '2025-09-11 13:47:54'),
(2, 45, '2021-0002', 'Maria', 'Garcia', 'Santos', '09123456790', 14, 'Networking', '456 Oak Ave, Manila', '2000-03-20', 1, 1, 0, '2025-09-11 13:47:51', '2025-09-11 13:47:54'),
(3, 46, '2021-0003', 'Pedro', 'Lopez', 'Gonzales', '09123456791', 15, 'Database', '789 Pine Rd, Makati', '2000-05-10', 1, 1, 0, '2025-09-11 13:47:51', '2025-09-11 13:47:54'),
(4, 47, '2021-0004', 'Ana', 'Reyes', 'Martinez', '09123456792', 13, 'Web Development', '321 Elm St, Taguig', '2000-11-25', 1, 1, 0, '2025-09-11 13:47:51', '2025-09-11 13:47:54'),
(5, 48, '2021-0005', 'Carlos', 'Torres', 'Fernandez', '09123456793', 14, 'Mobile Development', '654 Maple St, Pasig', '2000-07-05', 1, 1, 0, '2025-09-11 13:47:52', '2025-09-11 13:47:54'),
(6, 49, '2021-0006', 'Isabella', 'Cruz', 'Ramos', '09123456794', 15, 'Data Science', '987 Cedar Ave, Mandaluyong', '2000-09-12', 1, 1, 0, '2025-09-11 13:47:52', '2025-09-11 13:47:54'),
(7, 50, '2021-0007', 'Miguel', 'Santos', 'Torres', '09123456795', 13, 'Cybersecurity', '147 Birch Rd, San Juan', '2000-12-03', 1, 1, 0, '2025-09-11 13:47:52', '2025-09-11 13:47:54'),
(8, 51, '2021-0008', 'Sofia', 'Garcia', 'Lopez', '09123456796', 14, 'Artificial Intelligence', '258 Willow St, Marikina', '2000-04-18', 1, 1, 0, '2025-09-11 13:47:52', '2025-09-11 13:47:54');

-- --------------------------------------------------------

--
-- Table structure for table `student_matches`
--

CREATE TABLE `student_matches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `internship_id` bigint(20) UNSIGNED NOT NULL,
  `rank` int(10) UNSIGNED NOT NULL,
  `compatibility_score` decimal(5,2) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_matches`
--

INSERT INTO `student_matches` (`id`, `student_id`, `internship_id`, `rank`, `compatibility_score`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 89.40, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(2, 1, 3, 2, 89.40, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(3, 1, 5, 3, 89.40, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(4, 1, 7, 4, 89.40, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(5, 1, 9, 5, 89.40, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(6, 1, 31, 6, 89.40, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(7, 1, 32, 7, 89.40, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(8, 1, 33, 8, 89.40, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(9, 1, 2, 9, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(10, 1, 4, 10, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(11, 1, 6, 11, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(12, 1, 8, 12, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(13, 1, 10, 13, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(14, 1, 11, 14, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(15, 1, 12, 15, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(16, 1, 13, 16, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(17, 1, 14, 17, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(18, 1, 15, 18, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(19, 1, 16, 19, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(20, 1, 17, 20, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(21, 1, 18, 21, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(22, 1, 19, 22, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(23, 1, 20, 23, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(24, 1, 21, 24, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(25, 1, 22, 25, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(26, 1, 23, 26, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(27, 1, 24, 27, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(28, 1, 25, 28, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(29, 1, 26, 29, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(30, 1, 27, 30, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(31, 1, 28, 31, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(32, 1, 29, 32, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(33, 1, 30, 33, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(34, 1, 34, 34, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(35, 1, 35, 35, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(36, 1, 36, 36, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(37, 1, 37, 37, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(38, 1, 38, 38, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(39, 1, 39, 39, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(40, 1, 40, 40, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(41, 1, 41, 41, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(42, 1, 42, 42, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(43, 1, 43, 43, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(44, 1, 44, 44, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(45, 1, 45, 45, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(46, 2, 1, 1, 89.40, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(47, 2, 3, 2, 89.40, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(48, 2, 5, 3, 89.40, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(49, 2, 7, 4, 89.40, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(50, 2, 9, 5, 89.40, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(51, 2, 31, 6, 89.40, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(52, 2, 32, 7, 89.40, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(53, 2, 33, 8, 89.40, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(54, 2, 2, 9, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(55, 2, 4, 10, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(56, 2, 6, 11, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(57, 2, 8, 12, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(58, 2, 10, 13, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(59, 2, 11, 14, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(60, 2, 12, 15, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(61, 2, 13, 16, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(62, 2, 14, 17, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(63, 2, 15, 18, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(64, 2, 16, 19, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(65, 2, 17, 20, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(66, 2, 18, 21, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(67, 2, 19, 22, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(68, 2, 20, 23, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(69, 2, 21, 24, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(70, 2, 22, 25, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(71, 2, 23, 26, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(72, 2, 24, 27, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(73, 2, 25, 28, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(74, 2, 26, 29, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(75, 2, 27, 30, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(76, 2, 28, 31, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(77, 2, 29, 32, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(78, 2, 30, 33, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(79, 2, 34, 34, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(80, 2, 35, 35, 0.00, 'pending', '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(81, 2, 36, 36, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(82, 2, 37, 37, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(83, 2, 38, 38, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(84, 2, 39, 39, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(85, 2, 40, 40, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(86, 2, 41, 41, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(87, 2, 42, 42, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(88, 2, 43, 43, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(89, 2, 44, 44, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(90, 2, 45, 45, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(91, 3, 1, 1, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(92, 3, 3, 2, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(93, 3, 5, 3, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(94, 3, 7, 4, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(95, 3, 9, 5, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(96, 3, 31, 6, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(97, 3, 32, 7, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(98, 3, 33, 8, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(99, 3, 2, 9, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(100, 3, 4, 10, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(101, 3, 6, 11, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(102, 3, 8, 12, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(103, 3, 10, 13, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(104, 3, 11, 14, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(105, 3, 12, 15, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(106, 3, 13, 16, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(107, 3, 14, 17, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(108, 3, 15, 18, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(109, 3, 16, 19, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(110, 3, 17, 20, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(111, 3, 18, 21, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(112, 3, 19, 22, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(113, 3, 20, 23, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(114, 3, 21, 24, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(115, 3, 22, 25, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(116, 3, 23, 26, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(117, 3, 24, 27, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(118, 3, 25, 28, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(119, 3, 26, 29, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(120, 3, 27, 30, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(121, 3, 28, 31, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(122, 3, 29, 32, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(123, 3, 30, 33, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(124, 3, 34, 34, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(125, 3, 35, 35, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(126, 3, 36, 36, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(127, 3, 37, 37, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(128, 3, 38, 38, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(129, 3, 39, 39, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(130, 3, 40, 40, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(131, 3, 41, 41, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(132, 3, 42, 42, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(133, 3, 43, 43, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(134, 3, 44, 44, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(135, 3, 45, 45, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(136, 4, 1, 1, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(137, 4, 3, 2, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(138, 4, 5, 3, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(139, 4, 7, 4, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(140, 4, 9, 5, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(141, 4, 31, 6, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(142, 4, 32, 7, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(143, 4, 33, 8, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(144, 4, 2, 9, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(145, 4, 4, 10, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(146, 4, 6, 11, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(147, 4, 8, 12, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(148, 4, 10, 13, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(149, 4, 11, 14, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(150, 4, 12, 15, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(151, 4, 13, 16, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(152, 4, 14, 17, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(153, 4, 15, 18, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(154, 4, 16, 19, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(155, 4, 17, 20, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(156, 4, 18, 21, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(157, 4, 19, 22, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(158, 4, 20, 23, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(159, 4, 21, 24, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(160, 4, 22, 25, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(161, 4, 23, 26, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(162, 4, 24, 27, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(163, 4, 25, 28, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(164, 4, 26, 29, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(165, 4, 27, 30, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(166, 4, 28, 31, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(167, 4, 29, 32, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(168, 4, 30, 33, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(169, 4, 34, 34, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(170, 4, 35, 35, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(171, 4, 36, 36, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(172, 4, 37, 37, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(173, 4, 38, 38, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(174, 4, 39, 39, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(175, 4, 40, 40, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(176, 4, 41, 41, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(177, 4, 42, 42, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(178, 4, 43, 43, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(179, 4, 44, 44, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(180, 4, 45, 45, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(181, 5, 1, 1, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(182, 5, 3, 2, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(183, 5, 5, 3, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(184, 5, 7, 4, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(185, 5, 9, 5, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(186, 5, 31, 6, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(187, 5, 32, 7, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(188, 5, 33, 8, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(189, 5, 2, 9, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(190, 5, 4, 10, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(191, 5, 6, 11, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(192, 5, 8, 12, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(193, 5, 10, 13, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(194, 5, 11, 14, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(195, 5, 12, 15, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(196, 5, 13, 16, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(197, 5, 14, 17, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(198, 5, 15, 18, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(199, 5, 16, 19, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(200, 5, 17, 20, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(201, 5, 18, 21, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(202, 5, 19, 22, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(203, 5, 20, 23, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(204, 5, 21, 24, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(205, 5, 22, 25, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(206, 5, 23, 26, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(207, 5, 24, 27, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(208, 5, 25, 28, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(209, 5, 26, 29, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(210, 5, 27, 30, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(211, 5, 28, 31, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(212, 5, 29, 32, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(213, 5, 30, 33, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(214, 5, 34, 34, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(215, 5, 35, 35, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(216, 5, 36, 36, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(217, 5, 37, 37, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(218, 5, 38, 38, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(219, 5, 39, 39, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(220, 5, 40, 40, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(221, 5, 41, 41, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(222, 5, 42, 42, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(223, 5, 43, 43, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(224, 5, 44, 44, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(225, 5, 45, 45, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(226, 6, 1, 1, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(227, 6, 3, 2, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(228, 6, 5, 3, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(229, 6, 7, 4, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(230, 6, 9, 5, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(231, 6, 31, 6, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(232, 6, 32, 7, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(233, 6, 33, 8, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(234, 6, 2, 9, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(235, 6, 4, 10, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(236, 6, 6, 11, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(237, 6, 8, 12, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(238, 6, 10, 13, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(239, 6, 11, 14, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(240, 6, 12, 15, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(241, 6, 13, 16, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(242, 6, 14, 17, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(243, 6, 15, 18, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(244, 6, 16, 19, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(245, 6, 17, 20, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(246, 6, 18, 21, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(247, 6, 19, 22, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(248, 6, 20, 23, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(249, 6, 21, 24, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(250, 6, 22, 25, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(251, 6, 23, 26, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(252, 6, 24, 27, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(253, 6, 25, 28, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(254, 6, 26, 29, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(255, 6, 27, 30, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(256, 6, 28, 31, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(257, 6, 29, 32, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(258, 6, 30, 33, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(259, 6, 34, 34, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(260, 6, 35, 35, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(261, 6, 36, 36, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(262, 6, 37, 37, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(263, 6, 38, 38, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(264, 6, 39, 39, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(265, 6, 40, 40, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(266, 6, 41, 41, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(267, 6, 42, 42, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(268, 6, 43, 43, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(269, 6, 44, 44, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(270, 6, 45, 45, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(271, 7, 1, 1, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(272, 7, 3, 2, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(273, 7, 5, 3, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(274, 7, 7, 4, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(275, 7, 9, 5, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(276, 7, 31, 6, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(277, 7, 32, 7, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(278, 7, 33, 8, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(279, 7, 2, 9, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(280, 7, 4, 10, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(281, 7, 6, 11, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(282, 7, 8, 12, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(283, 7, 10, 13, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(284, 7, 11, 14, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(285, 7, 12, 15, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(286, 7, 13, 16, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(287, 7, 14, 17, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(288, 7, 15, 18, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(289, 7, 16, 19, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(290, 7, 17, 20, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(291, 7, 18, 21, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(292, 7, 19, 22, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(293, 7, 20, 23, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(294, 7, 21, 24, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(295, 7, 22, 25, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(296, 7, 23, 26, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(297, 7, 24, 27, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(298, 7, 25, 28, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(299, 7, 26, 29, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(300, 7, 27, 30, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(301, 7, 28, 31, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(302, 7, 29, 32, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(303, 7, 30, 33, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(304, 7, 34, 34, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(305, 7, 35, 35, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(306, 7, 36, 36, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(307, 7, 37, 37, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(308, 7, 38, 38, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(309, 7, 39, 39, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(310, 7, 40, 40, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(311, 7, 41, 41, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(312, 7, 42, 42, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(313, 7, 43, 43, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(314, 7, 44, 44, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(315, 7, 45, 45, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(316, 8, 1, 1, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(317, 8, 3, 2, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(318, 8, 5, 3, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(319, 8, 7, 4, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(320, 8, 9, 5, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(321, 8, 31, 6, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(322, 8, 32, 7, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(323, 8, 33, 8, 89.40, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(324, 8, 2, 9, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(325, 8, 4, 10, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(326, 8, 6, 11, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(327, 8, 8, 12, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(328, 8, 10, 13, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(329, 8, 11, 14, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(330, 8, 12, 15, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(331, 8, 13, 16, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(332, 8, 14, 17, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(333, 8, 15, 18, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(334, 8, 16, 19, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(335, 8, 17, 20, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(336, 8, 18, 21, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(337, 8, 19, 22, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(338, 8, 20, 23, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(339, 8, 21, 24, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(340, 8, 22, 25, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(341, 8, 23, 26, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(342, 8, 24, 27, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(343, 8, 25, 28, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(344, 8, 26, 29, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(345, 8, 27, 30, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(346, 8, 28, 31, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(347, 8, 29, 32, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(348, 8, 30, 33, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(349, 8, 34, 34, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(350, 8, 35, 35, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(351, 8, 36, 36, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(352, 8, 37, 37, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(353, 8, 38, 38, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(354, 8, 39, 39, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(355, 8, 40, 40, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(356, 8, 41, 41, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(357, 8, 42, 42, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(358, 8, 43, 43, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(359, 8, 44, 44, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(360, 8, 45, 45, 0.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(361, 1, 1, 1, 71.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(362, 2, 2, 1, 95.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55'),
(363, 3, 3, 1, 90.00, 'pending', '2025-09-11 13:47:55', '2025-09-11 13:47:55');

-- --------------------------------------------------------

--
-- Table structure for table `student_placements`
--

CREATE TABLE `student_placements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `internship_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `compatibility_score` decimal(5,2) NOT NULL,
  `placement_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_score`
--

CREATE TABLE `student_score` (
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `sub_category_id` bigint(20) UNSIGNED NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_score`
--

INSERT INTO `student_score` (`student_id`, `sub_category_id`, `score`, `created_at`, `updated_at`) VALUES
(1, 1, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(1, 2, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(1, 3, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(1, 4, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(1, 5, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(1, 6, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(1, 7, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(1, 8, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(1, 9, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(1, 10, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(1, 11, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(1, 12, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(1, 13, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(1, 14, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(1, 15, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(1, 16, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(2, 1, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(2, 2, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(2, 3, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(2, 4, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(2, 5, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(2, 6, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(2, 7, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(2, 8, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(2, 9, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(2, 10, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(2, 11, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(2, 12, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(2, 13, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(2, 14, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(2, 15, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(2, 16, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(3, 1, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(3, 2, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(3, 3, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(3, 4, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(3, 5, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(3, 6, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(3, 7, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(3, 8, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(3, 9, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(3, 10, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(3, 11, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(3, 12, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(3, 13, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(3, 14, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(3, 15, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(3, 16, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(4, 1, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(4, 2, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(4, 3, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(4, 4, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(4, 5, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(4, 6, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(4, 7, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(4, 8, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(4, 9, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(4, 10, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(4, 11, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(4, 12, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(4, 13, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(4, 14, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(4, 15, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(4, 16, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(5, 1, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(5, 2, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(5, 3, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(5, 4, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(5, 5, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(5, 6, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(5, 7, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(5, 8, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(5, 9, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(5, 10, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(5, 11, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(5, 12, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(5, 13, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(5, 14, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(5, 15, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(5, 16, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(6, 1, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(6, 2, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(6, 3, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(6, 4, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(6, 5, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(6, 6, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(6, 7, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(6, 8, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(6, 9, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(6, 10, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(6, 11, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(6, 12, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(6, 13, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(6, 14, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(6, 15, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(6, 16, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(7, 1, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(7, 2, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(7, 3, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(7, 4, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(7, 5, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(7, 6, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(7, 7, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(7, 8, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(7, 9, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(7, 10, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(7, 11, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(7, 12, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(7, 13, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(7, 14, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(7, 15, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(7, 16, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(8, 1, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(8, 2, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(8, 3, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(8, 4, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(8, 5, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(8, 6, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(8, 7, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(8, 8, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(8, 9, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(8, 10, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(8, 11, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(8, 12, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(8, 13, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(8, 14, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(8, 15, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(8, 16, 4.47, '2025-09-11 13:47:54', '2025-09-11 13:47:54');

-- --------------------------------------------------------

--
-- Table structure for table `subcategory_weights`
--

CREATE TABLE `subcategory_weights` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `internship_id` bigint(20) UNSIGNED NOT NULL,
  `subcategory_id` bigint(20) UNSIGNED NOT NULL,
  `weight` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subcategory_weights`
--

INSERT INTO `subcategory_weights` (`id`, `internship_id`, `subcategory_id`, `weight`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 20, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(2, 1, 3, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(3, 1, 5, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(4, 1, 2, 10, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(5, 1, 7, 5, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(6, 1, 4, 5, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(7, 1, 10, 25, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(8, 1, 9, 20, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(9, 1, 8, 10, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(10, 1, 12, 20, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(11, 1, 11, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(12, 1, 13, 10, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(13, 3, 3, 25, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(14, 3, 7, 20, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(15, 3, 5, 5, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(16, 3, 1, 5, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(17, 3, 8, 30, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(18, 3, 10, 20, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(19, 3, 12, 25, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(20, 3, 11, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(21, 3, 14, 10, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(22, 5, 3, 20, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(23, 5, 7, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(24, 5, 5, 5, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(25, 5, 10, 25, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(26, 5, 11, 25, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(27, 5, 12, 20, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(28, 5, 14, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(29, 7, 4, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(30, 7, 5, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(31, 7, 3, 10, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(32, 7, 7, 5, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(33, 7, 9, 25, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(34, 7, 10, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(35, 7, 11, 25, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(36, 7, 12, 20, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(37, 7, 13, 10, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(38, 7, 14, 10, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(39, 9, 7, 25, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(40, 9, 3, 20, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(41, 9, 1, 5, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(42, 9, 8, 25, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(43, 9, 10, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(44, 9, 11, 20, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(45, 9, 12, 20, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(46, 9, 16, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(47, 9, 13, 10, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(48, 31, 3, 25, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(49, 31, 5, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(50, 31, 2, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(51, 31, 7, 10, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(52, 31, 10, 30, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(53, 31, 9, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(54, 31, 8, 10, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(55, 31, 12, 25, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(56, 31, 11, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(57, 31, 15, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(58, 31, 14, 10, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(59, 32, 4, 20, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(60, 32, 5, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(61, 32, 3, 10, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(62, 32, 7, 5, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(63, 32, 9, 25, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(64, 32, 10, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(65, 32, 11, 25, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(66, 32, 12, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(67, 32, 13, 10, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(68, 33, 3, 30, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(69, 33, 7, 15, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(70, 33, 2, 10, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(71, 33, 10, 25, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(72, 33, 8, 20, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(73, 33, 12, 25, '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(74, 33, 11, 15, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(75, 33, 14, 15, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(76, 1, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(77, 1, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(78, 1, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(79, 1, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(80, 2, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(81, 2, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(82, 2, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(83, 2, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(84, 2, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(85, 2, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(86, 2, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(87, 2, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(88, 2, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(89, 2, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(90, 2, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(91, 2, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(92, 2, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(93, 2, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(94, 2, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(95, 2, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(96, 3, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(97, 3, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(98, 3, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(99, 3, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(100, 3, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(101, 3, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(102, 3, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(103, 4, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(104, 4, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(105, 4, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(106, 4, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(107, 4, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(108, 4, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(109, 4, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(110, 4, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(111, 4, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(112, 4, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(113, 4, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(114, 4, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(115, 4, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(116, 4, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(117, 4, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(118, 4, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(119, 5, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(120, 5, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(121, 5, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(122, 5, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(123, 5, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(124, 5, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(125, 5, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(126, 5, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(127, 5, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(128, 6, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(129, 6, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(130, 6, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(131, 6, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(132, 6, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(133, 6, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(134, 6, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(135, 6, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(136, 6, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(137, 6, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(138, 6, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(139, 6, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(140, 6, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(141, 6, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(142, 6, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(143, 6, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(144, 7, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(145, 7, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(146, 7, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(147, 7, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(148, 7, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(149, 7, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(150, 8, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(151, 8, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(152, 8, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(153, 8, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(154, 8, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(155, 8, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(156, 8, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(157, 8, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(158, 8, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(159, 8, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(160, 8, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(161, 8, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(162, 8, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(163, 8, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(164, 8, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(165, 8, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(166, 9, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(167, 9, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(168, 9, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(169, 9, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(170, 9, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(171, 9, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(172, 9, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(173, 10, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(174, 10, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(175, 10, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(176, 10, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(177, 10, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(178, 10, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(179, 10, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(180, 10, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(181, 10, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(182, 10, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(183, 10, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(184, 10, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(185, 10, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(186, 10, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(187, 10, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(188, 10, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(189, 11, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(190, 11, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(191, 11, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(192, 11, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(193, 11, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(194, 11, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(195, 11, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(196, 11, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(197, 11, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(198, 11, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(199, 11, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(200, 11, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(201, 11, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(202, 11, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(203, 11, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(204, 11, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(205, 12, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(206, 12, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(207, 12, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(208, 12, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(209, 12, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(210, 12, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(211, 12, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(212, 12, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(213, 12, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(214, 12, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(215, 12, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(216, 12, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(217, 12, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(218, 12, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(219, 12, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(220, 12, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(221, 13, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(222, 13, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(223, 13, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(224, 13, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(225, 13, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(226, 13, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(227, 13, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(228, 13, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(229, 13, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(230, 13, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(231, 13, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(232, 13, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(233, 13, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(234, 13, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(235, 13, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(236, 13, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(237, 14, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(238, 14, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(239, 14, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(240, 14, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(241, 14, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(242, 14, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(243, 14, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(244, 14, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(245, 14, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(246, 14, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(247, 14, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(248, 14, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(249, 14, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(250, 14, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(251, 14, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(252, 14, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(253, 15, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(254, 15, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(255, 15, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(256, 15, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(257, 15, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(258, 15, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(259, 15, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(260, 15, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(261, 15, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(262, 15, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(263, 15, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(264, 15, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(265, 15, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(266, 15, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(267, 15, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(268, 15, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(269, 16, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(270, 16, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(271, 16, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(272, 16, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(273, 16, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(274, 16, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(275, 16, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(276, 16, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(277, 16, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(278, 16, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(279, 16, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(280, 16, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(281, 16, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(282, 16, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(283, 16, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(284, 16, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(285, 17, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(286, 17, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(287, 17, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(288, 17, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(289, 17, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(290, 17, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(291, 17, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(292, 17, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(293, 17, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(294, 17, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(295, 17, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(296, 17, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(297, 17, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(298, 17, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(299, 17, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(300, 17, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(301, 18, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(302, 18, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(303, 18, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(304, 18, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(305, 18, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(306, 18, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(307, 18, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(308, 18, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(309, 18, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(310, 18, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(311, 18, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(312, 18, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(313, 18, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(314, 18, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(315, 18, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(316, 18, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(317, 19, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(318, 19, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(319, 19, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(320, 19, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(321, 19, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(322, 19, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(323, 19, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(324, 19, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(325, 19, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(326, 19, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(327, 19, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(328, 19, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(329, 19, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(330, 19, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(331, 19, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(332, 19, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(333, 20, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(334, 20, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(335, 20, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(336, 20, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(337, 20, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(338, 20, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(339, 20, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(340, 20, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(341, 20, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(342, 20, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(343, 20, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(344, 20, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(345, 20, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(346, 20, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(347, 20, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(348, 20, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(349, 21, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(350, 21, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(351, 21, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(352, 21, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(353, 21, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(354, 21, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(355, 21, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(356, 21, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(357, 21, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(358, 21, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(359, 21, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(360, 21, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(361, 21, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(362, 21, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(363, 21, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(364, 21, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(365, 22, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(366, 22, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(367, 22, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(368, 22, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(369, 22, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(370, 22, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(371, 22, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(372, 22, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(373, 22, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(374, 22, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(375, 22, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(376, 22, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(377, 22, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(378, 22, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(379, 22, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(380, 22, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(381, 23, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(382, 23, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(383, 23, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(384, 23, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(385, 23, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(386, 23, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(387, 23, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(388, 23, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(389, 23, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(390, 23, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(391, 23, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(392, 23, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(393, 23, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(394, 23, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(395, 23, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(396, 23, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(397, 24, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(398, 24, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(399, 24, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(400, 24, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(401, 24, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(402, 24, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(403, 24, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(404, 24, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(405, 24, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(406, 24, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(407, 24, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(408, 24, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(409, 24, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(410, 24, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(411, 24, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(412, 24, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(413, 25, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(414, 25, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(415, 25, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(416, 25, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(417, 25, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(418, 25, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(419, 25, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(420, 25, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(421, 25, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(422, 25, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(423, 25, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(424, 25, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(425, 25, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(426, 25, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(427, 25, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(428, 25, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(429, 26, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(430, 26, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(431, 26, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(432, 26, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(433, 26, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(434, 26, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(435, 26, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(436, 26, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(437, 26, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(438, 26, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(439, 26, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(440, 26, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(441, 26, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(442, 26, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(443, 26, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(444, 26, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(445, 27, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(446, 27, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(447, 27, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(448, 27, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(449, 27, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(450, 27, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(451, 27, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(452, 27, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(453, 27, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(454, 27, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(455, 27, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(456, 27, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(457, 27, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(458, 27, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(459, 27, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(460, 27, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(461, 28, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(462, 28, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(463, 28, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(464, 28, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(465, 28, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(466, 28, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(467, 28, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(468, 28, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(469, 28, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(470, 28, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(471, 28, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(472, 28, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(473, 28, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(474, 28, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(475, 28, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(476, 28, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(477, 29, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(478, 29, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(479, 29, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(480, 29, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(481, 29, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(482, 29, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(483, 29, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(484, 29, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(485, 29, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(486, 29, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(487, 29, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(488, 29, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(489, 29, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(490, 29, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(491, 29, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(492, 29, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(493, 30, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(494, 30, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(495, 30, 3, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(496, 30, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(497, 30, 5, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(498, 30, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(499, 30, 7, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(500, 30, 8, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(501, 30, 9, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(502, 30, 10, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(503, 30, 11, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(504, 30, 12, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(505, 30, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(506, 30, 14, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(507, 30, 15, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(508, 30, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(509, 31, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(510, 31, 4, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(511, 31, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(512, 31, 13, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(513, 31, 16, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(514, 32, 1, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(515, 32, 2, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(516, 32, 6, 0, '2025-09-11 13:47:53', '2025-09-11 13:47:53'),
(517, 32, 8, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(518, 32, 14, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(519, 32, 15, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(520, 32, 16, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(521, 33, 1, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(522, 33, 4, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(523, 33, 5, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(524, 33, 6, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(525, 33, 9, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(526, 33, 13, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(527, 33, 15, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(528, 33, 16, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(529, 34, 1, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(530, 34, 2, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(531, 34, 3, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(532, 34, 4, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(533, 34, 5, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(534, 34, 6, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(535, 34, 7, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(536, 34, 8, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(537, 34, 9, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(538, 34, 10, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(539, 34, 11, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(540, 34, 12, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(541, 34, 13, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(542, 34, 14, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(543, 34, 15, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(544, 34, 16, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(545, 35, 1, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(546, 35, 2, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(547, 35, 3, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(548, 35, 4, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(549, 35, 5, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(550, 35, 6, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(551, 35, 7, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(552, 35, 8, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(553, 35, 9, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(554, 35, 10, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(555, 35, 11, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(556, 35, 12, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(557, 35, 13, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(558, 35, 14, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(559, 35, 15, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(560, 35, 16, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(561, 36, 1, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(562, 36, 2, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(563, 36, 3, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(564, 36, 4, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(565, 36, 5, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(566, 36, 6, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(567, 36, 7, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(568, 36, 8, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(569, 36, 9, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(570, 36, 10, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(571, 36, 11, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(572, 36, 12, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(573, 36, 13, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(574, 36, 14, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(575, 36, 15, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(576, 36, 16, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(577, 37, 1, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(578, 37, 2, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(579, 37, 3, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(580, 37, 4, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(581, 37, 5, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(582, 37, 6, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(583, 37, 7, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(584, 37, 8, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(585, 37, 9, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(586, 37, 10, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(587, 37, 11, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(588, 37, 12, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(589, 37, 13, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(590, 37, 14, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(591, 37, 15, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(592, 37, 16, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(593, 38, 1, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(594, 38, 2, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(595, 38, 3, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(596, 38, 4, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(597, 38, 5, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(598, 38, 6, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(599, 38, 7, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(600, 38, 8, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(601, 38, 9, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(602, 38, 10, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(603, 38, 11, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(604, 38, 12, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(605, 38, 13, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(606, 38, 14, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(607, 38, 15, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(608, 38, 16, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(609, 39, 1, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(610, 39, 2, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(611, 39, 3, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(612, 39, 4, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(613, 39, 5, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(614, 39, 6, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(615, 39, 7, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(616, 39, 8, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(617, 39, 9, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(618, 39, 10, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(619, 39, 11, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(620, 39, 12, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(621, 39, 13, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(622, 39, 14, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(623, 39, 15, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(624, 39, 16, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(625, 40, 1, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(626, 40, 2, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(627, 40, 3, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(628, 40, 4, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(629, 40, 5, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(630, 40, 6, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(631, 40, 7, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(632, 40, 8, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(633, 40, 9, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(634, 40, 10, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(635, 40, 11, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(636, 40, 12, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(637, 40, 13, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(638, 40, 14, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(639, 40, 15, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(640, 40, 16, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(641, 41, 1, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(642, 41, 2, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(643, 41, 3, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(644, 41, 4, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(645, 41, 5, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(646, 41, 6, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(647, 41, 7, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(648, 41, 8, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(649, 41, 9, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(650, 41, 10, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(651, 41, 11, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(652, 41, 12, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(653, 41, 13, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(654, 41, 14, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(655, 41, 15, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(656, 41, 16, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(657, 42, 1, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(658, 42, 2, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(659, 42, 3, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(660, 42, 4, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(661, 42, 5, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(662, 42, 6, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(663, 42, 7, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(664, 42, 8, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(665, 42, 9, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(666, 42, 10, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(667, 42, 11, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(668, 42, 12, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(669, 42, 13, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(670, 42, 14, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(671, 42, 15, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(672, 42, 16, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(673, 43, 1, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(674, 43, 2, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(675, 43, 3, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(676, 43, 4, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(677, 43, 5, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(678, 43, 6, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(679, 43, 7, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(680, 43, 8, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(681, 43, 9, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(682, 43, 10, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(683, 43, 11, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(684, 43, 12, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(685, 43, 13, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(686, 43, 14, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(687, 43, 15, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(688, 43, 16, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(689, 44, 1, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(690, 44, 2, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(691, 44, 3, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(692, 44, 4, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(693, 44, 5, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(694, 44, 6, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(695, 44, 7, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(696, 44, 8, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(697, 44, 9, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(698, 44, 10, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(699, 44, 11, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(700, 44, 12, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(701, 44, 13, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(702, 44, 14, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(703, 44, 15, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(704, 44, 16, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(705, 45, 1, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(706, 45, 2, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(707, 45, 3, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(708, 45, 4, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(709, 45, 5, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(710, 45, 6, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(711, 45, 7, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(712, 45, 8, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(713, 45, 9, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(714, 45, 10, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(715, 45, 11, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(716, 45, 12, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(717, 45, 13, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(718, 45, 14, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(719, 45, 15, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54'),
(720, 45, 16, 0, '2025-09-11 13:47:54', '2025-09-11 13:47:54');

-- --------------------------------------------------------

--
-- Table structure for table `sub_categories`
--

CREATE TABLE `sub_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `subcategory_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sub_categories`
--

INSERT INTO `sub_categories` (`id`, `category_id`, `subcategory_name`, `created_at`, `updated_at`) VALUES
(1, 1, 'Java', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(2, 1, 'C++', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(3, 1, 'Python', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(4, 1, 'HTML/CSS', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(5, 1, 'JavaScript', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(6, 1, 'PHP', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(7, 1, 'SQL', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(8, 2, 'Database Management', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(9, 2, 'Web Development', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(10, 2, 'System and Software Development', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(11, 3, 'Communication Skills', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(12, 3, 'Problem-Solving and Analytical Skills', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(13, 3, 'Time Management', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(14, 3, 'Adaptability and Learning', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(15, 3, 'Ethical Decision-Making', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(16, 3, 'Professionalism', '2025-09-11 13:47:48', '2025-09-11 13:47:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `status` enum('verified','archived','unverified') NOT NULL DEFAULT 'unverified',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `remember_token`, `status`, `created_at`, `updated_at`) VALUES
(1, 'faye', 'faye@example.com', '2025-09-11 13:47:47', '$2y$12$l.NchwlexK8ggDoAfdDvf.z7ciZqaAq29xGn6SHAaWm6oq.GUAZuq', 'wQ3bjjXKyn', 'verified', '2025-09-11 13:47:47', '2025-09-11 13:47:47'),
(2, 'maria', 'maria@example.com', '2025-09-11 13:47:48', '$2y$12$IalAjllk1q2YpnH4zssWG.dUjyBqWmrEHDoV9JSVw1o3k4wXMl1le', 'r2M6BGYciz', 'verified', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(3, 'emman', 'emman@example.com', '2025-09-11 13:47:48', '$2y$12$m4yi66sQN8.6b4FZIv0AYeD8F4Cstto13uZhsfGTQWmpv/k3pRFL2', 'hkw9FtH7gS', 'verified', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(4, 'clairo', 'clairo@example.com', '2025-09-11 13:47:48', '$2y$12$w/eYy.SHZh/wrATCVDSN/.N.OK5vN/vnpJMwzSF.8rsJH9fkdZeh2', 'fNv3Emfa1lanV90KwfK3vtARkIqg84s4yjajV5WrDwDqhGq9dtWk8tyu79LY', 'unverified', '2025-09-11 13:47:48', '2025-09-11 13:47:48'),
(5, 'hte_company1', 'hte1@example.com', NULL, '$2y$12$u8mjQvDj98ttkFjOSn0bIOC1RsLX.n3d74Nt.IJoJRX9SAnFmFnlq', NULL, 'unverified', '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(6, 'hte_company2', 'hte2@example.com', NULL, '$2y$12$peIn2p0syeemddQJg5V2yeXFFqf3cUYoKbtY4m6u/F7KrtktrHJxm', NULL, 'unverified', '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(7, 'hte_company3', 'hte3@example.com', NULL, '$2y$12$3gsq7bS5u6xklMroiyrZjuRCrcn9rMVqMHCClVCOB3gxdnmX8U8z2', NULL, 'unverified', '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(8, 'hte_company4', 'hte4@example.com', NULL, '$2y$12$9B.RNxUnSMFHD/ZvtmZJP.2QXLwixMOdbjdZXn4powOJpeNfMyGsG', NULL, 'unverified', '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(9, 'hte_company5', 'hte5@example.com', NULL, '$2y$12$CvefGuq239tvEAd5MAJn4O7TcTCLEqqngSrRKseJOrLSFOp7mMCfy', NULL, 'unverified', '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(10, 'bashirian.dortha', 'zwaelchi@example.com', '2025-09-11 13:47:49', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'hvbRfmYIfl', 'unverified', '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(11, 'jannie66', 'ike.hackett@example.org', '2025-09-11 13:47:49', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'K5Sh85Y2YI', 'unverified', '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(12, 'filomena.nikolaus', 'megane.oconner@example.org', '2025-09-11 13:47:49', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'GNg11uZmrA', 'unverified', '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(13, 'georgiana53', 'reinhold72@example.net', '2025-09-11 13:47:49', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', '5ABdq9lN58', 'unverified', '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(14, 'rwelch', 'dhartmann@example.net', '2025-09-11 13:47:49', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', '53skZSwVdk', 'unverified', '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(15, 'dillan21', 'esteban08@example.org', '2025-09-11 13:47:49', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'X9oKWf3rNC', 'unverified', '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(16, 'brianne.gorczany', 'heaney.shaniya@example.org', '2025-09-11 13:47:49', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'iqya88Aivc', 'unverified', '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(17, 'yesenia.predovic', 'jdach@example.com', '2025-09-11 13:47:49', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'EkFwugze5X', 'unverified', '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(18, 'fahey.tomasa', 'lavon.hoppe@example.com', '2025-09-11 13:47:49', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'OOjxQdlruy', 'unverified', '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(19, 'pmarks', 'schowalter.carroll@example.net', '2025-09-11 13:47:49', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'lq5zf7Xk7q', 'unverified', '2025-09-11 13:47:49', '2025-09-11 13:47:49'),
(20, 'maria.garcia', 'maria.garcia@example.com', NULL, '$2y$12$JK10Nhhdjq/YT97w6T0QA.63ooPJuSZQRTg5Rt5SuAkAp/M3Xgb9G', NULL, 'verified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(21, 'john.doe', 'john.doe@example.com', NULL, '$2y$12$G7AADKgu6TufjI2YkItZaujAMqQt24BgeolwZg0RW85v1ktzISu5q', NULL, 'verified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(22, 'sarah.wilson', 'sarah.wilson@example.com', NULL, '$2y$12$iscUArMH4RXvU/dWqz9lpu7OmMFLzaQJW/5LMkMRbrQfEga3htc36', NULL, 'verified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(23, 'michael.brown', 'michael.brown@example.com', NULL, '$2y$12$UaZ1UclzGtQfRGwsV4dOieJISZSvqk07t5Xko1BOEBsCt/ySSZXma', NULL, 'verified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(24, 'ebernhard', 'vschiller@example.com', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'bbWD3QlY5E', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(25, 'npurdy', 'lea.paucek@example.com', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'DEljaiNMzL', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(26, 'braden.bednar', 'schinner.daryl@example.com', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'Jtl4I1m8tx', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(27, 'chaya.kuhlman', 'fredrick.halvorson@example.net', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'sFIHi2A8Tp', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(28, 'hill.burley', 'erunolfsson@example.org', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', '0Y6hjxCeKB', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(29, 'adams.kianna', 'lang.reta@example.net', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'FDhpNd6Mtl', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(30, 'brandyn13', 'lsatterfield@example.com', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'Tb2IDhAeC3', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(31, 'nova66', 'tanner.cole@example.org', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', '2vcAaGAFHJ', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(32, 'zosinski', 'pdicki@example.org', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'G6dX7RpiZw', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(33, 'alvina.cormier', 'bode.christophe@example.net', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'x0lV3eKqs1', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(34, 'doyle.spencer', 'hirthe.giovanny@example.com', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'KyGKv5nhkV', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(35, 'vwalter', 'brent43@example.org', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'SyOw5njlGp', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(36, 'eweber', 'wehner.ella@example.org', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'qq78V0JSor', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(37, 'thea.murazik', 'gkuhn@example.net', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'YgtMgLeumG', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(38, 'qzieme', 'harber.jewell@example.net', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'GiRIfRryfC', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(39, 'aletha.denesik', 'ritchie.cleta@example.net', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'w5iMRDBXUo', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(40, 'bailey.reinhold', 'kboehm@example.org', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'Zo6pPNU1Qo', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(41, 'quigley.maurice', 'giovanni.corkery@example.org', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'gZkZ0KPTpK', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(42, 'london.braun', 'zachery58@example.org', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', '29AJdffvOM', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(43, 'kertzmann.leif', 'kamille.flatley@example.net', '2025-09-11 13:47:50', '$2y$12$7f0jvKC6v0LzVgO/P5P3d.dtRs98wcpRiTUmv/HB9xEzDVwV8rMdq', 'stZ8ZWsHyo', 'unverified', '2025-09-11 13:47:50', '2025-09-11 13:47:50'),
(44, 'juan.dela.cruz', 'juan.dela.cruz@example.com', NULL, '$2y$12$GBeXcwi1XtwkqCJgpbJsre6rCdvCYOjplEheblJDbXAk5zFUbkwKq', NULL, 'verified', '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(45, 'maria.santos', 'maria.santos@example.com', NULL, '$2y$12$zhc2IeeMR9uop/f6oNcq0uGxj9UMv/Gdz1uWMXZ/00mOf5jYISBYK', NULL, 'verified', '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(46, 'pedro.gonzales', 'pedro.gonzales@example.com', NULL, '$2y$12$cv1ADasXJQqRe8XCmWFnnuEXo.JSBdVhc2E9AKfDRaD3FquV1016W', NULL, 'verified', '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(47, 'ana.martinez', 'ana.martinez@example.com', NULL, '$2y$12$Kgt3GLAsdc2nPmc8Ogb8aO0.QvO96o7TSP57O3rjqQJNZE.pLPC4u', NULL, 'verified', '2025-09-11 13:47:51', '2025-09-11 13:47:51'),
(48, 'carlos.fernandez', 'carlos.fernandez@example.com', NULL, '$2y$12$iGgEif9k7c2Fo8ZUKSxDleiTqn.UE2QoXYhIQQEG.lwkryG./8alm', NULL, 'verified', '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(49, 'isabella.ramos', 'isabella.ramos@example.com', NULL, '$2y$12$BgMeLOLhLBz1LGtGZwuXGeHrvFYKC15Qm8YUAMHH8Dt1j67l6rQwS', NULL, 'verified', '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(50, 'miguel.torres', 'miguel.torres@example.com', NULL, '$2y$12$SVO1O.caep3VkrTGXYnkn.3e51zpkJJkxl1u7o5piuLcs8Bwa6nya', NULL, 'verified', '2025-09-11 13:47:52', '2025-09-11 13:47:52'),
(51, 'sofia.lopez', 'sofia.lopez@example.com', NULL, '$2y$12$y8h46yBOObsetuN4G84CfO0Kq4oiMbWNU1h6scCMEy7DKwbqHjRka', NULL, 'verified', '2025-09-11 13:47:52', '2025-09-11 13:47:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academe_accounts`
--
ALTER TABLE `academe_accounts`
  ADD PRIMARY KEY (`user_id`,`section_id`),
  ADD KEY `academe_accounts_section_id_foreign` (`section_id`);

--
-- Indexes for table `advisers`
--
ALTER TABLE `advisers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `advisers_user_id_foreign` (`user_id`),
  ADD KEY `advisers_section_id_foreign` (`section_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deadlines`
--
ALTER TABLE `deadlines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_verification_attempts`
--
ALTER TABLE `email_verification_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_verification_attempts_email_last_attempt_at_index` (`email`,`last_attempt_at`),
  ADD KEY `email_verification_attempts_email_index` (`email`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `htes`
--
ALTER TABLE `htes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `internships`
--
ALTER TABLE `internships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `internships_hte_id_foreign` (`hte_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `questions_subcategory_id_foreign` (`subcategory_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD UNIQUE KEY `requests_stud_num_unique` (`stud_num`),
  ADD KEY `requests_section_id_foreign` (`section_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD UNIQUE KEY `sections_section_name_unique` (`section_name`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `students_section_id_foreign` (`section_id`);

--
-- Indexes for table `student_matches`
--
ALTER TABLE `student_matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_matches_student_id_foreign` (`student_id`),
  ADD KEY `student_matches_internship_id_foreign` (`internship_id`);

--
-- Indexes for table `student_placements`
--
ALTER TABLE `student_placements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_placements_student_id_internship_id_unique` (`student_id`,`internship_id`),
  ADD KEY `student_placements_internship_id_foreign` (`internship_id`);

--
-- Indexes for table `student_score`
--
ALTER TABLE `student_score`
  ADD PRIMARY KEY (`student_id`,`sub_category_id`);

--
-- Indexes for table `subcategory_weights`
--
ALTER TABLE `subcategory_weights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subcategory_weights_internship_id_foreign` (`internship_id`),
  ADD KEY `subcategory_weights_subcategory_id_foreign` (`subcategory_id`);

--
-- Indexes for table `sub_categories`
--
ALTER TABLE `sub_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sub_categories_category_id_foreign` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `advisers`
--
ALTER TABLE `advisers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `deadlines`
--
ALTER TABLE `deadlines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_verification_attempts`
--
ALTER TABLE `email_verification_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `htes`
--
ALTER TABLE `htes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `internships`
--
ALTER TABLE `internships`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `student_matches`
--
ALTER TABLE `student_matches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=364;

--
-- AUTO_INCREMENT for table `student_placements`
--
ALTER TABLE `student_placements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subcategory_weights`
--
ALTER TABLE `subcategory_weights`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=721;

--
-- AUTO_INCREMENT for table `sub_categories`
--
ALTER TABLE `sub_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academe_accounts`
--
ALTER TABLE `academe_accounts`
  ADD CONSTRAINT `academe_accounts_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `academe_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `advisers`
--
ALTER TABLE `advisers`
  ADD CONSTRAINT `advisers_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `advisers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `internships`
--
ALTER TABLE `internships`
  ADD CONSTRAINT `internships_hte_id_foreign` FOREIGN KEY (`hte_id`) REFERENCES `htes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `sub_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_matches`
--
ALTER TABLE `student_matches`
  ADD CONSTRAINT `student_matches_internship_id_foreign` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_matches_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_placements`
--
ALTER TABLE `student_placements`
  ADD CONSTRAINT `student_placements_internship_id_foreign` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_placements_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subcategory_weights`
--
ALTER TABLE `subcategory_weights`
  ADD CONSTRAINT `subcategory_weights_internship_id_foreign` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subcategory_weights_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `sub_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sub_categories`
--
ALTER TABLE `sub_categories`
  ADD CONSTRAINT `sub_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
