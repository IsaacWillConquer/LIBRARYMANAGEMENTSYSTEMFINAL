-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 12, 2026 at 08:22 AM
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
-- Database: `librarydb`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetArchives` (IN `p_EntityType` VARCHAR(10))   BEGIN
    IF p_EntityType = 'Staff' THEN
        SELECT 
            a.ArchiveID,
            a.DateRemoved,
            a.EntityType,
            a.EntityID,
            a.ArchivedData
        FROM archives a
        WHERE a.EntityType = 'Staff'
        ORDER BY a.DateRemoved DESC;
    ELSEIF p_EntityType = 'Member' THEN
        SELECT 
            a.ArchiveID,
            a.DateRemoved,
            a.EntityType,
            a.EntityID,
            a.ArchivedData
        FROM archives a
        WHERE a.EntityType = 'Member'
        ORDER BY a.DateRemoved DESC;
    ELSE
        SELECT * FROM archives ORDER BY DateRemoved DESC;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetBorrowLogs` ()   BEGIN
    SELECT 
        bl.LogID,
        bl.LogTime,
        bl.Action,
        CONCAT(m.FirstName, ' ', m.LastName) AS MemberName,
        CONCAT(s.FirstName, ' ', s.LastName) AS StaffName,
        mt.Title AS Material
    FROM borrowlogs bl
    JOIN members m ON bl.MemberID = m.MemberID
    LEFT JOIN staffs s ON bl.StaffID = s.StaffID
    JOIN materials mt ON bl.MaterialID = mt.MaterialID
    ORDER BY bl.LogTime DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetLoginLogs` ()   BEGIN
    SELECT 
        LogID,
        LogTime,
        UserType,
        UserID,
        Email,
        Status,
        IPAddress
    FROM loginlogs
    ORDER BY LogTime DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetMaterialLogs` ()   BEGIN
    SELECT 
        ml.LogID,
        ml.LogTime,
        ml.Action,
        CONCAT(s.FirstName, ' ', s.LastName) AS StaffName,
        mt.Title AS Material,
        mt.TypeID
    FROM materiallogs ml
    JOIN staffs s ON ml.StaffID = s.StaffID
    JOIN materials mt ON ml.MaterialID = mt.MaterialID
    ORDER BY ml.LogTime DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetMemberLogs` ()   BEGIN
    SELECT 
        ml.LogID,
        ml.LogTime,
        ml.Action,
        CONCAT(s.FirstName, ' ', s.LastName) AS StaffName,
        CONCAT(m.FirstName, ' ', m.LastName) AS AffectedMember,
        m.Email AS MemberEmail
    FROM memberlogs ml
    JOIN staffs s ON ml.StaffID = s.StaffID
    JOIN members m ON ml.AffectedMemberID = m.MemberID
    ORDER BY ml.LogTime DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStaffLogs` ()   BEGIN
    SELECT 
        sl.LogID,
        sl.LogTime,
        sl.Action,
        CONCAT(admin.FirstName, ' ', admin.LastName) AS AdminName,
        CONCAT(affected.FirstName, ' ', affected.LastName) AS AffectedStaff,
        sr.RoleName AS AffectedRole
    FROM stafflogs sl
    JOIN staffs admin ON sl.AdminID = admin.StaffID
    JOIN staffs affected ON sl.AffectedStaffID = affected.StaffID
    JOIN staffroles sr ON affected.RoleID = sr.RoleID
    ORDER BY sl.LogTime DESC;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `archives`
--

CREATE TABLE `archives` (
  `ArchiveID` int(11) NOT NULL,
  `EntityType` enum('Member','Staff') NOT NULL,
  `EntityID` int(11) NOT NULL,
  `ArchivedData` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`ArchivedData`)),
  `DateRemoved` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archives`
--

INSERT INTO `archives` (`ArchiveID`, `EntityType`, `EntityID`, `ArchivedData`, `DateRemoved`) VALUES
(4, 'Staff', 7, '{\"StaffID\":7,\"FirstName\":\"Data2\",\"LastName\":\"Analyst\",\"Email\":\"Data2Analyst@gmail.com\",\"Password\":\"$2y$10$YSN7u41\\/K1mY1TTwiKHfFe8csXHEYglAbBDv5tou06pju0JTNO5zC\",\"DefaultPassword\":1,\"RoleID\":2,\"StatusID\":2,\"DateCreated\":\"2026-04-01 12:40:12\"}', '2026-04-01 12:44:32'),
(5, 'Staff', 8, '{\"StaffID\":8,\"FirstName\":\"Test\",\"LastName\":\"Archive\",\"Email\":\"TestArchive@gmail.com\",\"Password\":\"$2y$10$\\/4de6DBJH6Y5TwVAdn.Mqe7J.VGmvquFpDniWIfzT0ozjL9x82UcK\",\"DefaultPassword\":1,\"RoleID\":2,\"StatusID\":1,\"DateCreated\":\"2026-04-01 13:04:26\"}', '2026-04-01 13:04:32'),
(6, 'Staff', 9, '{\"StaffID\":9,\"FirstName\":\"Test\",\"LastName\":\"Archive\",\"Email\":\"TestArchive@gmail.com\",\"Password\":\"$2y$10$tOk.vuQ1dUlqI0lITGtM3ORf8pvlRU4TYwShe4j7rdZdCZfjmqu3S\",\"DefaultPassword\":1,\"RoleID\":2,\"StatusID\":1,\"DateCreated\":\"2026-04-01 13:08:41\"}', '2026-04-01 13:08:46'),
(7, 'Staff', 10, '{\"StaffID\":10,\"FirstName\":\"Test\",\"LastName\":\"Archive\",\"Email\":\"TestArchive@gmail.com\",\"Password\":\"$2y$10$OAmNYeD\\/LxkJ\\/q65oimJ6OHgNUObp3Q1p1vYZq3Doo.m7ylyuEhSG\",\"DefaultPassword\":1,\"RoleID\":2,\"StatusID\":1,\"DateCreated\":\"2026-04-01 13:10:26\"}', '2026-04-01 13:10:30'),
(8, 'Staff', 11, '{\"StaffID\":11,\"FirstName\":\"Test\",\"LastName\":\"Archive\",\"Email\":\"TestArchive@gmail.com\",\"Password\":\"$2y$10$cTRAIJlvGKEq76JALZYi3eB6ZSxMhQgQNpWAavUPgiyGV354GaH\\/G\",\"DefaultPassword\":1,\"RoleID\":2,\"StatusID\":1,\"DateCreated\":\"2026-04-01 13:11:38\"}', '2026-04-01 13:11:41'),
(9, 'Staff', 12, '{\"StaffID\":12,\"FirstName\":\"Test\",\"LastName\":\"Archive\",\"Email\":\"TestArchive@gmail.com\",\"Password\":\"$2y$10$c0HZYZp1cwkBW\\/iBDd4TcOBhw4ezj28OG3SZOBxP9e9PLbgroUbnG\",\"DefaultPassword\":1,\"RoleID\":2,\"StatusID\":1,\"DateCreated\":\"2026-04-01 13:17:20\"}', '2026-04-01 13:17:24'),
(10, 'Staff', 13, '{\"StaffID\":13,\"FirstName\":\"test\",\"LastName\":\"archive\",\"Email\":\"testarvhive@gmail.com\",\"Password\":\"$2y$10$eMFqC0989EezDFc4PCYi0..A2c1alOKLlW7jojWJ3icI\\/eh0TWBT6\",\"DefaultPassword\":1,\"RoleID\":2,\"StatusID\":1,\"DateCreated\":\"2026-04-01 13:19:15\"}', '2026-04-01 13:19:18'),
(11, 'Staff', 14, '{\"StaffID\":14,\"FirstName\":\"nigga\",\"LastName\":\"nigga\",\"Email\":\"NiggaData@gmai.com\",\"Password\":\"$2y$10$zDD0MKTwMfZx7C66SmtY0ezQDBNyWp6zjz8SwMl5wy4qlgYpkM6L6\",\"DefaultPassword\":1,\"RoleID\":4,\"StatusID\":1,\"DateCreated\":\"2026-04-01 21:37:03\"}', '2026-04-01 21:37:39'),
(12, 'Staff', 15, '{\"StaffID\":15,\"FirstName\":\"Test\",\"LastName\":\"DataAnalyst\",\"Email\":\"Datatest@gmail.com\",\"Password\":\"$2y$10$LP.3Ksd9.eXVr7LPlOQJTuDpKhlVMZkCHkBBogNDM\\/ZY8ZdHwIGpe\",\"DefaultPassword\":1,\"RoleID\":2,\"StatusID\":1,\"DateCreated\":\"2026-04-01 21:48:15\"}', '2026-04-01 21:48:21'),
(13, 'Member', 3, '{\"MemberID\":3,\"FirstName\":\"Member\",\"LastName\":\"2\",\"Email\":\"awedwad@gmail.com\",\"Password\":\"$2y$10$m\\/ZyNYz27L9d83ZHmcrv8.ZGHyAebGOCdr..ZEQYtLTNU674aDCWq\",\"DefaultPassword\":1,\"StatusID\":1,\"DateCreated\":\"2026-04-01 23:01:57\"}', '2026-04-01 23:03:06'),
(14, 'Member', 3, '{\"MemberID\":3,\"FirstName\":\"Member\",\"LastName\":\"2\",\"Email\":\"awedwad@gmail.com\",\"Password\":\"$2y$10$m\\/ZyNYz27L9d83ZHmcrv8.ZGHyAebGOCdr..ZEQYtLTNU674aDCWq\",\"DefaultPassword\":1,\"StatusID\":1,\"DateCreated\":\"2026-04-01 23:01:57\"}', '2026-04-01 23:05:34'),
(15, 'Member', 3, '{\"MemberID\":3,\"FirstName\":\"Member\",\"LastName\":\"2\",\"Email\":\"awedwad@gmail.com\",\"Password\":\"$2y$10$m\\/ZyNYz27L9d83ZHmcrv8.ZGHyAebGOCdr..ZEQYtLTNU674aDCWq\",\"DefaultPassword\":1,\"StatusID\":1,\"DateCreated\":\"2026-04-01 23:01:57\"}', '2026-04-01 23:10:21'),
(16, 'Member', 4, '{\"MemberID\":4,\"FirstName\":\"Member\",\"LastName\":\"2\",\"Email\":\"Member@gmail.com\",\"Password\":\"$2y$10$u3sdV8jGw5lu5al04qr5AunrEcy00yLBzKqYydvqC.LZy.jBc0Ay6\",\"DefaultPassword\":1,\"StatusID\":1,\"DateCreated\":\"2026-04-01 23:10:36\"}', '2026-04-01 23:10:46'),
(17, 'Member', 2, '{\"MemberID\":2,\"FirstName\":\"Member\",\"LastName\":\"Nigga\",\"Email\":\"MemberNigga@gmail.com\",\"Password\":\"$2y$10$QBWX1LzwNfeGfyT3EDvn6uoBb\\/RxZDlj6O1t9KLpsOkwz3vFN0zpS\",\"DefaultPassword\":1,\"StatusID\":1,\"DateCreated\":\"2026-04-01 23:00:18\"}', '2026-04-01 23:10:48'),
(18, 'Staff', 4, '{\"StaffID\":4,\"FirstName\":\"Cir\",\"LastName\":\"Librarian\",\"Email\":\"CirLibrarian@gmail.com\",\"Password\":\"$2b$10$3FkQO8a5Mp3iXt7umHho6.itaHGmOTnYQQnU9mRrKZeZunl1ckQtS\\r\\n\",\"DefaultPassword\":1,\"RoleID\":2,\"StatusID\":1,\"DateCreated\":\"2026-04-01 01:05:27\"}', '2026-04-02 16:28:25'),
(19, 'Staff', 5, '{\"StaffID\":5,\"FirstName\":\"Data\",\"LastName\":\"Analyst\",\"Email\":\"DataAnalyst@gmail.com\",\"Password\":\"$2b$10$3FkQO8a5Mp3iXt7umHho6.itaHGmOTnYQQnU9mRrKZeZunl1ckQtS\\r\\n\",\"DefaultPassword\":1,\"RoleID\":4,\"StatusID\":1,\"DateCreated\":\"2026-04-01 01:05:27\"}', '2026-04-02 16:28:27'),
(20, 'Staff', 17, '{\"StaffID\":17,\"FirstName\":\"Nyle\",\"LastName\":\"Librarian\",\"Email\":\"NyleLib@gmail.com\",\"Password\":\"$2y$10$NjCkC5YX0tp2e6Lw3dntAe6G1wRzP1rFwERWseIbYXGW5ljoHh2li\",\"DefaultPassword\":1,\"RoleID\":2,\"StatusID\":1,\"DateCreated\":\"2026-04-02 21:13:41\"}', '2026-04-02 21:13:50'),
(21, 'Member', 7, '{\"MemberID\":7,\"FirstName\":\"Member\",\"LastName\":\"Test\",\"Email\":\"Test@gmail.com\",\"Password\":\"$2y$10$5bayDrmuokkllRAPGf0iFeYjzhnq.6q6NX3AIigdTHx97EdArDGbm\",\"DefaultPassword\":0,\"StatusID\":1,\"DateCreated\":\"2026-04-10 18:06:20\"}', '2026-04-10 22:20:51'),
(22, 'Member', 7, '{\"MemberID\":7,\"FirstName\":\"Member\",\"LastName\":\"Test\",\"Email\":\"Test@gmail.com\",\"Password\":\"$2y$10$5bayDrmuokkllRAPGf0iFeYjzhnq.6q6NX3AIigdTHx97EdArDGbm\",\"DefaultPassword\":0,\"StatusID\":1,\"DateCreated\":\"2026-04-10 18:06:20\"}', '2026-04-10 22:20:58'),
(23, 'Member', 7, '{\"MemberID\":7,\"FirstName\":\"Member\",\"LastName\":\"Test\",\"Email\":\"Test@gmail.com\",\"Password\":\"$2y$10$5bayDrmuokkllRAPGf0iFeYjzhnq.6q6NX3AIigdTHx97EdArDGbm\",\"DefaultPassword\":0,\"StatusID\":1,\"DateCreated\":\"2026-04-10 18:06:20\"}', '2026-04-10 22:21:00'),
(24, 'Member', 7, '{\"MemberID\":7,\"FirstName\":\"Member\",\"LastName\":\"Test\",\"Email\":\"Test@gmail.com\",\"Password\":\"$2y$10$5bayDrmuokkllRAPGf0iFeYjzhnq.6q6NX3AIigdTHx97EdArDGbm\",\"DefaultPassword\":0,\"StatusID\":1,\"DateCreated\":\"2026-04-10 18:06:20\"}', '2026-04-10 22:21:12'),
(25, 'Member', 7, '{\"MemberID\":7,\"FirstName\":\"Member\",\"LastName\":\"Test\",\"Email\":\"Test@gmail.com\",\"Password\":\"$2y$10$5bayDrmuokkllRAPGf0iFeYjzhnq.6q6NX3AIigdTHx97EdArDGbm\",\"DefaultPassword\":0,\"StatusID\":1,\"DateCreated\":\"2026-04-10 18:06:20\"}', '2026-04-10 22:23:27'),
(26, 'Member', 7, '{\"MemberID\":7,\"FirstName\":\"Member\",\"LastName\":\"Test\",\"Email\":\"Test@gmail.com\",\"Password\":\"$2y$10$5bayDrmuokkllRAPGf0iFeYjzhnq.6q6NX3AIigdTHx97EdArDGbm\",\"DefaultPassword\":0,\"StatusID\":1,\"DateCreated\":\"2026-04-10 18:06:20\"}', '2026-04-10 22:24:50'),
(27, 'Member', 7, '{\"MemberID\":7,\"FirstName\":\"Member\",\"LastName\":\"Test\",\"Email\":\"Test@gmail.com\",\"Password\":\"$2y$10$5bayDrmuokkllRAPGf0iFeYjzhnq.6q6NX3AIigdTHx97EdArDGbm\",\"DefaultPassword\":0,\"StatusID\":1,\"DateCreated\":\"2026-04-10 18:06:20\"}', '2026-04-10 22:25:43'),
(28, 'Member', 7, '{\"MemberID\":7,\"FirstName\":\"Member\",\"LastName\":\"Test\",\"Email\":\"Test@gmail.com\",\"Password\":\"$2y$10$5bayDrmuokkllRAPGf0iFeYjzhnq.6q6NX3AIigdTHx97EdArDGbm\",\"DefaultPassword\":0,\"StatusID\":1,\"DateCreated\":\"2026-04-10 18:06:20\"}', '2026-04-10 22:26:22'),
(29, 'Member', 7, '{\"MemberID\":7,\"FirstName\":\"Member\",\"LastName\":\"Test\",\"Email\":\"Test@gmail.com\",\"Password\":\"$2y$10$5bayDrmuokkllRAPGf0iFeYjzhnq.6q6NX3AIigdTHx97EdArDGbm\",\"DefaultPassword\":0,\"StatusID\":1,\"DateCreated\":\"2026-04-10 18:06:20\"}', '2026-04-10 22:28:49'),
(30, 'Member', 7, '{\"MemberID\":7,\"FirstName\":\"Member\",\"LastName\":\"Test\",\"Email\":\"Test@gmail.com\",\"Password\":\"$2y$10$5bayDrmuokkllRAPGf0iFeYjzhnq.6q6NX3AIigdTHx97EdArDGbm\",\"DefaultPassword\":0,\"StatusID\":1,\"DateCreated\":\"2026-04-10 18:06:20\"}', '2026-04-10 22:32:06'),
(31, 'Member', 7, '{\"MemberID\":7,\"FirstName\":\"Member\",\"LastName\":\"Test\",\"Email\":\"Test@gmail.com\",\"Password\":\"$2y$10$5bayDrmuokkllRAPGf0iFeYjzhnq.6q6NX3AIigdTHx97EdArDGbm\",\"DefaultPassword\":0,\"StatusID\":1,\"DateCreated\":\"2026-04-10 18:06:20\"}', '2026-04-10 22:32:08'),
(32, 'Member', 7, '{\"MemberID\":7,\"FirstName\":\"Member\",\"LastName\":\"Test\",\"Email\":\"Test@gmail.com\",\"Password\":\"$2y$10$5bayDrmuokkllRAPGf0iFeYjzhnq.6q6NX3AIigdTHx97EdArDGbm\",\"DefaultPassword\":0,\"StatusID\":1,\"DateCreated\":\"2026-04-10 18:06:20\"}', '2026-04-10 22:32:12'),
(33, 'Member', 7, '{\"MemberID\":7,\"FirstName\":\"Member\",\"LastName\":\"Test\",\"Email\":\"Test@gmail.com\",\"Password\":\"$2y$10$5bayDrmuokkllRAPGf0iFeYjzhnq.6q6NX3AIigdTHx97EdArDGbm\",\"DefaultPassword\":0,\"StatusID\":1,\"DateCreated\":\"2026-04-10 18:06:20\"}', '2026-04-10 22:32:20'),
(35, 'Member', 7, '{\"MemberID\":7,\"FirstName\":\"Member\",\"LastName\":\"Test\",\"Email\":\"Test@gmail.com\",\"Password\":\"$2y$10$5bayDrmuokkllRAPGf0iFeYjzhnq.6q6NX3AIigdTHx97EdArDGbm\",\"DefaultPassword\":0,\"StatusID\":1,\"DateCreated\":\"2026-04-10 18:06:20\"}', '2026-04-10 22:34:17'),
(36, 'Member', 8, '{\"MemberID\":8,\"FirstName\":\"Delete\",\"LastName\":\"test\",\"Email\":\"Delete@gmail.com\",\"Password\":\"$2y$10$zwNRHwUn6NfNNVfCY\\/an\\/.sARCAJkbsG3AcLh3tvAkFni.tcBkmbG\",\"DefaultPassword\":0,\"StatusID\":1,\"DateCreated\":\"2026-04-10 22:36:45\"}', '2026-04-10 22:37:18'),
(37, 'Staff', 21, '{\"StaffID\":21,\"FirstName\":\"Delete\",\"LastName\":\"Test\",\"Email\":\"Delete@gmail.com\",\"Password\":\"$2y$10$HsQNZd9Z4lk44s6gcLY\\/TO1Ylc3bzw\\/AdZsH9Pf2heE\\/VRe9LZnrS\",\"DefaultPassword\":0,\"RoleID\":2,\"StatusID\":1,\"DateCreated\":\"2026-04-10 22:38:03\"}', '2026-04-10 22:38:31'),
(38, 'Staff', 20, '{\"StaffID\":20,\"FirstName\":\"Nyle2\",\"LastName\":\"Dalay\",\"Email\":\"Nyle2Test@gmail.com\",\"Password\":\"$2y$10$LrOxY9oOXWIbmf4aSqyarO5nmOXJNXJPyGuw3QLeLSV1UFc4NkuQi\",\"DefaultPassword\":0,\"RoleID\":2,\"StatusID\":1,\"DateCreated\":\"2026-04-10 21:47:05\"}', '2026-04-10 22:51:26'),
(39, 'Staff', 22, '{\"StaffID\":22,\"FirstName\":\"delete\",\"LastName\":\"test\",\"Email\":\"Delete@gmail.com\",\"Password\":\"$2y$10$.CCjL\\/PhQEiTeTuQEjr8LuKvzdbL9Rqm7ptpLqpOMRe5UoAHbofqi\",\"DefaultPassword\":1,\"RoleID\":2,\"StatusID\":1,\"DateCreated\":\"2026-04-10 23:11:09\"}', '2026-04-10 23:11:14'),
(42, 'Member', 9, '{\"MemberID\":9,\"FirstName\":\"archive\",\"LastName\":\"mem\",\"Email\":\"del@gmail.com\",\"Password\":\"$2y$10$XvzVzBoa\\/E9\\/rwLKqNmmI.8Eiplf5DxpaRkfJ6rVwnxB9HKlBSKqG\",\"DefaultPassword\":1,\"StatusID\":1,\"DateCreated\":\"2026-04-10 23:26:27\"}', '2026-04-10 23:26:30'),
(44, 'Member', 11, '{\"MemberID\":11,\"FirstName\":\"Nyle\",\"LastName\":\"Dalay\",\"Email\":\"Dalay@gmail.com\",\"Password\":\"$2y$10$7yaVjLbvXRd1fztU65QsA.tqVYBpw4ohppqOIi.VC.EbHpelRI8NC\",\"DefaultPassword\":1,\"StatusID\":1,\"DateCreated\":\"2026-04-10 23:27:28\"}', '2026-04-10 23:28:30'),
(45, 'Member', 10, '{\"MemberID\":10,\"FirstName\":\"Member\",\"LastName\":\"Nyle\",\"Email\":\"Nyle@gmail.com\",\"Password\":\"$2y$10$j67CXQjAYThZXkcUzrz0N.5V7KYjCgXBYuhV.Pd1Qc0OwJ6DjOFUC\",\"DefaultPassword\":1,\"StatusID\":1,\"DateCreated\":\"2026-04-10 23:27:01\"}', '2026-04-10 23:28:33'),
(50, 'Member', 6, '{\"MemberID\":6,\"FirstName\":\"Members\",\"LastName\":\"Adriel\",\"Email\":\"MemberAdriel@gmail.com\",\"Password\":\"$2y$10$jebp2kB1f5sHrE9AYVaES.Tx0.USv9x6pj.D9tUEIHle6UoCMJrMW\",\"DefaultPassword\":0,\"StatusID\":1,\"DateCreated\":\"2026-04-04 01:00:45\"}', '2026-04-10 23:32:41'),
(52, 'Member', 5, '{\"MemberID\":5,\"FirstName\":\"Member\",\"LastName\":\"Dalay\",\"Email\":\"MemberDalay@gmail.com\",\"Password\":\"$2y$10$w65jf\\/sizhMfGUWpdD8DEuZagqhoXK5C\\/PB5NIXgiXX8uCARxycx2\",\"DefaultPassword\":0,\"StatusID\":1,\"DateCreated\":\"2026-04-03 19:46:58\"}', '2026-04-10 23:33:15'),
(53, 'Staff', 16, '{\"StaffID\":16,\"FirstName\":\"Circ\",\"LastName\":\"Librarian\",\"Email\":\"CircLib@gmail.com\",\"Password\":\"$2y$10$pdpsQqfQjg8DKaFFWofi..X\\/s44x9bgxjasEny.lPoUgX08IXpQ2.\",\"DefaultPassword\":0,\"RoleID\":2,\"StatusID\":1,\"DateCreated\":\"2026-04-02 16:28:46\"}', '2026-04-10 23:34:12'),
(54, 'Staff', 19, '{\"StaffID\":19,\"FirstName\":\"Nyle\",\"LastName\":\"Test\",\"Email\":\"NyleTest@gmail.com\",\"Password\":\"$2y$10$jVQzIh8O4nnL\\/BAjeNMWFeYZFogVP9bRB1UB3a7j6wTcmyPR4r6Su\",\"DefaultPassword\":0,\"RoleID\":4,\"StatusID\":1,\"DateCreated\":\"2026-04-10 21:41:20\"}', '2026-04-10 23:34:23'),
(55, 'Staff', 16, '{\"StaffID\":16,\"FirstName\":\"Circ\",\"LastName\":\"Librarian\",\"Email\":\"CircLib@gmail.com\",\"Password\":\"$2y$10$pdpsQqfQjg8DKaFFWofi..X\\/s44x9bgxjasEny.lPoUgX08IXpQ2.\",\"DefaultPassword\":0,\"RoleID\":2,\"StatusID\":1,\"DateCreated\":\"2026-04-02 16:28:46\"}', '2026-04-10 23:34:33');

-- --------------------------------------------------------

--
-- Table structure for table `borrowlogs`
--

CREATE TABLE `borrowlogs` (
  `LogID` int(11) NOT NULL,
  `StaffID` int(11) DEFAULT NULL,
  `MemberID` int(11) NOT NULL,
  `MaterialID` int(11) NOT NULL,
  `Action` enum('Requested','Approved','Rejected','Cancelled','Returned','MarkedOverdue','Claimed','Unclaimed') NOT NULL,
  `LogTime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowlogs`
--

INSERT INTO `borrowlogs` (`LogID`, `StaffID`, `MemberID`, `MaterialID`, `Action`, `LogTime`) VALUES
(1, NULL, 1, 35, 'Requested', '2026-04-03 16:33:39'),
(2, NULL, 1, 24, 'Requested', '2026-04-03 16:33:51'),
(3, NULL, 1, 25, 'Requested', '2026-04-03 16:33:52'),
(4, NULL, 1, 25, 'Cancelled', '2026-04-03 16:34:01'),
(5, NULL, 1, 24, 'Cancelled', '2026-04-03 16:34:02'),
(6, NULL, 1, 35, 'Cancelled', '2026-04-03 16:34:03'),
(7, NULL, 1, 25, 'Requested', '2026-04-03 16:38:02'),
(8, NULL, 1, 26, 'Requested', '2026-04-03 16:38:04'),
(9, NULL, 1, 26, 'Cancelled', '2026-04-03 16:38:06'),
(10, NULL, 1, 25, 'Cancelled', '2026-04-03 16:38:07'),
(11, NULL, 1, 24, 'Requested', '2026-04-03 16:38:16'),
(12, NULL, 1, 35, 'Requested', '2026-04-03 16:38:17'),
(13, NULL, 1, 35, 'Cancelled', '2026-04-03 16:38:21'),
(14, NULL, 1, 24, 'Cancelled', '2026-04-03 16:38:22'),
(15, NULL, 1, 35, 'Requested', '2026-04-03 17:28:02'),
(16, NULL, 1, 24, 'Requested', '2026-04-03 17:33:32'),
(17, NULL, 1, 27, 'Requested', '2026-04-03 17:35:19'),
(18, 3, 1, 35, 'Rejected', '2026-04-03 17:43:04'),
(19, 3, 1, 24, 'Rejected', '2026-04-03 17:43:05'),
(20, 3, 1, 27, 'Rejected', '2026-04-03 17:43:06'),
(21, NULL, 1, 35, 'Requested', '2026-04-03 17:43:21'),
(22, 3, 1, 35, 'Rejected', '2026-04-03 17:52:30'),
(23, NULL, 1, 35, 'Requested', '2026-04-03 17:52:37'),
(24, 3, 1, 35, 'Approved', '2026-04-03 17:53:01'),
(25, NULL, 1, 26, 'Requested', '2026-04-03 17:53:13'),
(26, 3, 1, 26, 'Rejected', '2026-04-03 17:56:48'),
(27, NULL, 1, 26, 'Requested', '2026-04-03 17:57:22'),
(28, 3, 1, 26, 'Approved', '2026-04-03 17:57:25'),
(29, NULL, 1, 8, 'Requested', '2026-04-03 17:57:34'),
(30, 3, 1, 8, 'Approved', '2026-04-03 17:57:41'),
(39, 3, 1, 35, 'Returned', '2026-04-03 22:28:54'),
(40, 3, 1, 8, 'Returned', '2026-04-03 22:29:01'),
(42, 3, 1, 26, 'Returned', '2026-04-03 22:29:10'),
(45, NULL, 1, 24, 'Requested', '2026-04-03 22:33:20'),
(46, 3, 1, 24, 'Approved', '2026-04-03 22:33:26'),
(47, NULL, 1, 25, 'Requested', '2026-04-03 22:33:45'),
(48, NULL, 1, 27, 'Requested', '2026-04-03 22:33:48'),
(49, 3, 1, 25, 'Approved', '2026-04-03 22:33:57'),
(50, 3, 1, 27, 'Approved', '2026-04-03 22:34:00'),
(51, 3, 1, 27, 'Returned', '2026-04-03 22:34:03'),
(52, 3, 1, 24, 'Returned', '2026-04-03 22:34:04'),
(53, 3, 1, 25, 'Returned', '2026-04-03 22:34:07'),
(54, NULL, 1, 24, 'Requested', '2026-04-03 22:36:08'),
(55, 3, 1, 24, 'Approved', '2026-04-03 22:36:13'),
(56, 3, 1, 24, 'Returned', '2026-04-03 22:36:17'),
(57, NULL, 1, 24, 'Requested', '2026-04-03 22:36:22'),
(58, 3, 1, 24, 'Approved', '2026-04-03 22:36:27'),
(59, 3, 1, 24, 'Returned', '2026-04-03 22:39:01'),
(60, NULL, 1, 24, 'Requested', '2026-04-03 22:39:17'),
(61, 3, 1, 24, 'Approved', '2026-04-03 22:39:22'),
(62, NULL, 1, 27, 'Requested', '2026-04-03 22:43:29'),
(63, 3, 1, 27, 'Approved', '2026-04-03 22:43:38'),
(64, 3, 1, 24, 'Returned', '2026-04-03 22:56:29'),
(65, 3, 1, 27, 'Returned', '2026-04-03 22:56:34'),
(66, NULL, 1, 7, 'Requested', '2026-04-03 23:00:35'),
(67, 3, 1, 7, 'Approved', '2026-04-03 23:00:44'),
(68, 3, 1, 7, 'Returned', '2026-04-03 23:01:04'),
(74, NULL, 1, 26, 'Requested', '2026-04-04 01:04:02'),
(76, 3, 1, 26, 'Approved', '2026-04-04 01:04:48'),
(77, 3, 1, 26, 'Returned', '2026-04-04 01:04:57'),
(80, NULL, 1, 27, 'Requested', '2026-04-04 01:05:24'),
(81, 3, 1, 27, 'Approved', '2026-04-04 01:05:42'),
(84, NULL, 1, 26, 'Requested', '2026-04-04 01:06:04'),
(85, 3, 1, 26, 'Approved', '2026-04-04 01:06:14'),
(88, 3, 1, 26, 'Returned', '2026-04-04 01:06:28'),
(89, 3, 1, 27, 'Returned', '2026-04-04 01:06:30'),
(90, NULL, 1, 7, '', '2026-04-08 22:18:12'),
(91, NULL, 1, 8, '', '2026-04-08 22:18:18'),
(92, NULL, 1, 24, '', '2026-04-08 22:18:19'),
(93, NULL, 1, 24, '', '2026-04-08 22:18:24'),
(94, NULL, 1, 24, '', '2026-04-08 22:18:26'),
(95, NULL, 1, 24, '', '2026-04-08 22:18:28'),
(96, NULL, 1, 25, '', '2026-04-08 22:18:30'),
(97, NULL, 1, 26, '', '2026-04-08 22:18:32'),
(98, NULL, 1, 26, '', '2026-04-08 22:18:33'),
(99, NULL, 1, 26, '', '2026-04-08 22:18:35'),
(100, NULL, 1, 27, '', '2026-04-08 22:18:37'),
(101, NULL, 1, 27, '', '2026-04-08 22:18:38'),
(102, NULL, 1, 27, '', '2026-04-08 22:18:40'),
(103, NULL, 1, 35, '', '2026-04-08 22:18:42'),
(104, NULL, 1, 27, '', '2026-04-08 22:18:47'),
(136, NULL, 1, 8, 'Requested', '2026-04-09 04:22:35'),
(137, 16, 1, 8, 'Approved', '2026-04-09 04:22:49'),
(138, NULL, 1, 8, 'Cancelled', '2026-04-09 04:23:56'),
(141, NULL, 1, 7, 'Requested', '2026-04-09 04:53:12'),
(143, 3, 1, 7, 'Approved', '2026-04-09 04:57:26'),
(144, 3, 1, 7, 'Claimed', '2026-04-09 04:57:32'),
(145, 3, 1, 7, 'Returned', '2026-04-09 04:57:43'),
(147, NULL, 1, 8, 'Requested', '2026-04-09 04:58:13'),
(148, 3, 1, 8, 'Approved', '2026-04-09 04:59:41'),
(149, 3, 1, 8, 'Claimed', '2026-04-09 05:00:39'),
(150, NULL, 1, 8, 'Requested', '2026-04-09 05:00:51'),
(151, 3, 1, 8, 'Approved', '2026-04-09 05:06:29'),
(152, 3, 1, 8, 'Returned', '2026-04-09 05:07:53'),
(153, 3, 1, 8, 'Claimed', '2026-04-09 05:07:55'),
(154, NULL, 1, 7, 'Requested', '2026-04-09 05:08:00'),
(155, 3, 1, 7, 'Approved', '2026-04-09 05:08:16'),
(156, NULL, 1, 8, '', '2026-04-09 05:25:59'),
(160, 16, 1, 7, 'Claimed', '2026-04-10 03:05:23'),
(162, 16, 1, 7, 'Returned', '2026-04-10 03:05:38'),
(163, NULL, 1, 162, 'Requested', '2026-04-10 12:32:34'),
(164, 3, 1, 162, 'Approved', '2026-04-10 12:39:25'),
(165, 16, 1, 162, 'Claimed', '2026-04-10 15:37:54'),
(166, NULL, 1, 173, 'Requested', '2026-04-10 15:38:19'),
(167, 3, 1, 162, 'Returned', '2026-04-10 15:41:08'),
(169, 3, 1, 173, 'Approved', '2026-04-10 15:41:43'),
(171, 3, 1, 173, 'Claimed', '2026-04-10 15:41:58'),
(172, 3, 1, 173, '', '2026-04-10 15:42:02'),
(183, NULL, 1, 7, 'Requested', '2026-04-10 21:36:24'),
(184, 16, 1, 7, 'Approved', '2026-04-10 21:37:20'),
(185, 16, 1, 7, 'Claimed', '2026-04-10 21:37:36'),
(186, 16, 1, 7, 'Returned', '2026-04-10 21:37:47'),
(187, NULL, 12, 7, 'Requested', '2026-04-10 23:41:22'),
(188, NULL, 12, 8, 'Requested', '2026-04-10 23:41:25');

-- --------------------------------------------------------

--
-- Table structure for table `borrowrecords`
--

CREATE TABLE `borrowrecords` (
  `RecordID` int(11) NOT NULL,
  `RequestID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `MaterialID` int(11) NOT NULL,
  `BorrowedBy` int(11) NOT NULL,
  `BorrowDate` datetime DEFAULT NULL,
  `DueDate` date NOT NULL,
  `ReturnDate` datetime DEFAULT NULL,
  `Status` enum('Borrowed','Overdue','Returned','Lost','Damaged') DEFAULT NULL,
  `OverdueFine` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ReturnProcessedBy` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowrecords`
--

INSERT INTO `borrowrecords` (`RecordID`, `RequestID`, `MemberID`, `MaterialID`, `BorrowedBy`, `BorrowDate`, `DueDate`, `ReturnDate`, `Status`, `OverdueFine`, `ReturnProcessedBy`) VALUES
(1, 12, 1, 35, 3, '2026-04-03 17:53:01', '2026-04-15', '2026-04-03 22:28:54', 'Returned', 0.00, 3),
(2, 14, 1, 26, 3, '2026-04-03 17:57:25', '2026-04-15', '2026-04-03 22:29:10', 'Returned', 0.00, 3),
(3, 15, 1, 8, 3, '2026-04-03 17:57:41', '2026-04-15', '2026-04-03 22:29:01', 'Returned', 0.00, 3),
(7, 20, 1, 24, 3, '2026-04-03 22:33:26', '2026-04-15', '2026-04-03 22:34:04', 'Returned', 0.00, 3),
(8, 21, 1, 25, 3, '2026-04-03 22:33:57', '2026-04-15', '2026-04-03 22:34:07', 'Returned', 0.00, 3),
(9, 22, 1, 27, 3, '2026-04-03 22:34:00', '2026-04-15', '2026-04-03 22:34:03', 'Returned', 0.00, 3),
(10, 23, 1, 24, 3, '2026-04-03 22:36:13', '2026-04-15', '2026-04-03 22:36:17', 'Returned', 0.00, 3),
(11, 24, 1, 24, 3, '2026-04-03 22:36:27', '2026-04-15', '2026-04-03 22:39:01', 'Returned', 0.00, 3),
(12, 25, 1, 24, 3, '2026-04-03 22:39:22', '2026-04-15', '2026-04-03 22:56:29', 'Returned', 0.00, 3),
(13, 26, 1, 27, 3, '2026-04-03 22:43:38', '2026-04-15', '2026-04-03 22:56:34', 'Returned', 0.00, 3),
(14, 27, 1, 7, 3, '2026-04-03 23:00:44', '2026-04-15', '2026-04-03 23:01:04', 'Returned', 0.00, 3),
(17, 30, 1, 26, 3, '2026-04-04 01:04:48', '2026-04-16', '2026-04-04 01:04:57', 'Returned', 0.00, 3),
(18, 33, 1, 27, 3, '2026-04-04 01:05:42', '2026-04-16', '2026-04-04 01:06:30', 'Returned', 0.00, 3),
(21, 34, 1, 26, 3, '2026-04-04 01:06:14', '2026-04-16', '2026-04-04 01:06:28', 'Returned', 0.00, 3),
(32, 41, 1, 7, 3, '2026-04-09 04:57:32', '2026-04-22', '2026-04-09 04:57:43', 'Returned', 0.00, 3),
(33, 42, 1, 8, 3, '2026-04-09 05:00:39', '2026-04-22', '2026-04-09 05:07:53', 'Returned', 0.00, 3),
(34, 43, 1, 8, 3, '2026-04-09 05:07:55', '2026-04-22', '2026-04-09 05:25:59', 'Lost', 0.00, NULL),
(36, 44, 1, 7, 16, '2026-04-10 03:05:23', '2026-04-23', '2026-04-10 03:05:38', 'Returned', 0.00, 16),
(37, 46, 1, 162, 16, '2026-04-10 15:37:54', '2026-04-24', '2026-04-10 15:41:08', 'Returned', 0.00, 3),
(38, 47, 1, 173, 3, '2026-04-10 15:41:58', '2026-04-24', '2026-04-10 15:42:02', 'Lost', 0.00, 3),
(42, 51, 1, 7, 16, '2026-04-10 21:37:36', '2026-04-24', '2026-04-10 21:37:47', 'Returned', 0.00, 16);

-- --------------------------------------------------------

--
-- Table structure for table `borrowrequests`
--

CREATE TABLE `borrowrequests` (
  `RequestID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `MaterialID` int(11) NOT NULL,
  `Status` enum('Pending','Approved','Rejected','Cancelled') NOT NULL DEFAULT 'Pending',
  `RequestDate` datetime DEFAULT current_timestamp(),
  `ProcessedBy` int(11) DEFAULT NULL,
  `ProcessedDate` datetime DEFAULT NULL,
  `ClaimDeadline` date DEFAULT NULL,
  `ClaimedAt` datetime DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowrequests`
--

INSERT INTO `borrowrequests` (`RequestID`, `MemberID`, `MaterialID`, `Status`, `RequestDate`, `ProcessedBy`, `ProcessedDate`, `ClaimDeadline`, `ClaimedAt`, `Remarks`) VALUES
(1, 1, 35, 'Cancelled', '2026-04-03 16:33:39', NULL, NULL, NULL, NULL, NULL),
(2, 1, 24, 'Cancelled', '2026-04-03 16:33:51', NULL, NULL, NULL, NULL, NULL),
(3, 1, 25, 'Cancelled', '2026-04-03 16:33:52', NULL, NULL, NULL, NULL, NULL),
(4, 1, 25, 'Cancelled', '2026-04-03 16:38:02', NULL, NULL, NULL, NULL, NULL),
(5, 1, 26, 'Cancelled', '2026-04-03 16:38:04', NULL, NULL, NULL, NULL, NULL),
(6, 1, 24, 'Cancelled', '2026-04-03 16:38:16', NULL, NULL, NULL, NULL, NULL),
(7, 1, 35, 'Cancelled', '2026-04-03 16:38:17', NULL, NULL, NULL, NULL, NULL),
(8, 1, 35, 'Rejected', '2026-04-03 17:28:02', NULL, NULL, NULL, NULL, NULL),
(9, 1, 24, 'Rejected', '2026-04-03 17:33:32', NULL, NULL, NULL, NULL, NULL),
(10, 1, 27, 'Rejected', '2026-04-03 17:35:19', NULL, NULL, NULL, NULL, NULL),
(11, 1, 35, 'Rejected', '2026-04-03 17:43:21', 3, '2026-04-03 17:52:30', NULL, NULL, NULL),
(12, 1, 35, 'Cancelled', '2026-04-03 17:52:37', 3, '2026-04-03 17:53:01', NULL, NULL, NULL),
(13, 1, 26, 'Rejected', '2026-04-03 17:53:13', 3, '2026-04-03 17:56:48', NULL, NULL, NULL),
(14, 1, 26, 'Cancelled', '2026-04-03 17:57:22', 3, '2026-04-03 17:57:25', NULL, NULL, NULL),
(15, 1, 8, 'Cancelled', '2026-04-03 17:57:34', 3, '2026-04-03 17:57:41', NULL, NULL, NULL),
(20, 1, 24, 'Cancelled', '2026-04-03 22:33:20', 3, '2026-04-03 22:33:26', NULL, NULL, NULL),
(21, 1, 25, 'Cancelled', '2026-04-03 22:33:45', 3, '2026-04-03 22:33:57', NULL, NULL, NULL),
(22, 1, 27, 'Cancelled', '2026-04-03 22:33:48', 3, '2026-04-03 22:34:00', NULL, NULL, NULL),
(23, 1, 24, 'Cancelled', '2026-04-03 22:36:08', 3, '2026-04-03 22:36:13', NULL, NULL, NULL),
(24, 1, 24, 'Cancelled', '2026-04-03 22:36:22', 3, '2026-04-03 22:36:27', NULL, NULL, NULL),
(25, 1, 24, 'Cancelled', '2026-04-03 22:39:17', 3, '2026-04-03 22:39:22', NULL, NULL, NULL),
(26, 1, 27, 'Cancelled', '2026-04-03 22:43:29', 3, '2026-04-03 22:43:38', NULL, NULL, NULL),
(27, 1, 7, 'Cancelled', '2026-04-03 23:00:35', 3, '2026-04-03 23:00:44', NULL, NULL, NULL),
(30, 1, 26, 'Cancelled', '2026-04-04 01:04:02', 3, '2026-04-04 01:04:48', NULL, NULL, NULL),
(33, 1, 27, 'Cancelled', '2026-04-04 01:05:24', 3, '2026-04-04 01:05:42', NULL, NULL, NULL),
(34, 1, 26, 'Cancelled', '2026-04-04 01:06:04', 3, '2026-04-04 01:06:14', NULL, NULL, NULL),
(39, 1, 8, 'Cancelled', '2026-04-09 04:22:35', 16, '2026-04-09 04:22:49', NULL, NULL, NULL),
(41, 1, 7, 'Approved', '2026-04-09 04:53:12', 3, '2026-04-09 04:57:26', '2026-04-13', '2026-04-09 04:57:32', NULL),
(42, 1, 8, 'Approved', '2026-04-09 04:58:13', 3, '2026-04-09 04:59:41', '2026-04-11', '2026-04-09 05:00:39', NULL),
(43, 1, 8, 'Approved', '2026-04-09 05:00:51', 3, '2026-04-09 05:06:29', '2026-04-17', '2026-04-09 05:07:55', NULL),
(44, 1, 7, 'Approved', '2026-04-09 05:08:00', 3, '2026-04-09 05:08:16', '2026-04-16', '2026-04-10 03:05:23', NULL),
(46, 1, 162, 'Approved', '2026-04-10 12:32:34', 3, '2026-04-10 12:39:25', '2026-04-13', '2026-04-10 15:37:54', NULL),
(47, 1, 173, 'Approved', '2026-04-10 15:38:19', 3, '2026-04-10 15:41:43', '2026-04-16', '2026-04-10 15:41:58', NULL),
(51, 1, 7, 'Approved', '2026-04-10 21:36:24', 16, '2026-04-10 21:37:20', '2026-04-16', '2026-04-10 21:37:36', NULL),
(52, 12, 7, 'Pending', '2026-04-10 23:41:22', NULL, NULL, NULL, NULL, NULL),
(53, 12, 8, 'Pending', '2026-04-10 23:41:25', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `DonationID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Author` varchar(100) NOT NULL,
  `Genre` varchar(100) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `BookCondition` enum('New','Good','Fair','Poor') NOT NULL DEFAULT 'Good',
  `Status` enum('Pending','Accepted','Rejected') NOT NULL DEFAULT 'Pending',
  `ReviewedBy` int(11) DEFAULT NULL,
  `ReviewedDate` datetime DEFAULT NULL,
  `DateSubmitted` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ebookaccess`
--

CREATE TABLE `ebookaccess` (
  `AccessID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `MaterialID` int(11) NOT NULL,
  `AccessCount` int(11) NOT NULL DEFAULT 1,
  `LastAccessed` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ebookaccess`
--

INSERT INTO `ebookaccess` (`AccessID`, `MemberID`, `MaterialID`, `AccessCount`, `LastAccessed`) VALUES
(3, 1, 26, 14, '2026-04-10 22:49:28'),
(16, 1, 180, 2, '2026-04-10 21:36:15'),
(21, 12, 183, 1, '2026-04-10 23:41:30');

-- --------------------------------------------------------

--
-- Table structure for table `loginlogs`
--

CREATE TABLE `loginlogs` (
  `LogID` int(11) NOT NULL,
  `UserType` enum('Member','Staff') NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `Status` enum('Success','Failed') NOT NULL,
  `IPAddress` varchar(50) DEFAULT NULL,
  `LogTime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loginlogs`
--

INSERT INTO `loginlogs` (`LogID`, `UserType`, `UserID`, `Email`, `Status`, `IPAddress`, `LogTime`) VALUES
(1, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-01 01:08:47'),
(2, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-01 01:09:00'),
(3, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-01 01:32:10'),
(4, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-01 01:51:15'),
(5, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-01 02:41:17'),
(6, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-01 13:26:49'),
(7, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-01 13:27:05'),
(8, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-01 13:36:13'),
(9, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-01 21:36:42'),
(10, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 01:43:57'),
(11, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 02:04:12'),
(12, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 16:05:59'),
(13, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 16:17:38'),
(14, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 16:17:46'),
(15, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 16:24:19'),
(16, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 16:24:36'),
(17, 'Staff', NULL, 'CirLibrarian@gmail.com', 'Failed', '::1', '2026-04-02 16:28:01'),
(18, 'Staff', NULL, 'CirLibrarian@gmail.com', 'Failed', '::1', '2026-04-02 16:28:07'),
(19, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 16:28:16'),
(20, '', NULL, 'CirLib@gmail.com', 'Failed', '::1', '2026-04-02 16:29:04'),
(21, 'Staff', NULL, 'CircLib@gmail.com', 'Failed', '::1', '2026-04-02 16:29:07'),
(22, 'Staff', NULL, 'CircLib@gmail.com', 'Failed', '::1', '2026-04-02 16:29:13'),
(23, 'Staff', NULL, 'CircLib@gmail.com', 'Failed', '::1', '2026-04-02 16:29:56'),
(24, 'Staff', NULL, 'CircLib@gmail.com', 'Failed', '::1', '2026-04-02 16:30:06'),
(25, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-02 16:31:52'),
(26, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-02 16:32:15'),
(27, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-02 16:32:36'),
(28, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-02 16:32:59'),
(29, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 16:34:37'),
(30, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 16:35:09'),
(31, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 16:35:49'),
(32, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 16:37:32'),
(33, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 16:37:55'),
(34, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 16:39:02'),
(35, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 16:41:31'),
(36, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 17:06:12'),
(37, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 21:11:55'),
(38, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 21:13:18'),
(39, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-02 21:31:05'),
(40, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-03 16:29:28'),
(41, 'Member', NULL, 'MemberNyle@gmail.com', 'Failed', '::1', '2026-04-03 16:40:49'),
(42, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-03 16:40:52'),
(43, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-03 16:41:07'),
(44, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-03 17:26:09'),
(45, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-03 17:27:19'),
(46, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-03 17:27:30'),
(47, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-03 18:11:00'),
(48, 'Member', NULL, 'MemberNyle@gmail.com', 'Failed', '::1', '2026-04-03 18:11:06'),
(49, 'Member', NULL, 'MemberNyle@gmail.com', 'Failed', '::1', '2026-04-03 18:11:11'),
(50, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-03 18:11:15'),
(51, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-03 18:51:26'),
(52, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-03 19:46:03'),
(53, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-03 19:46:12'),
(54, 'Member', NULL, 'MemberDalay@gmail.com', 'Failed', '::1', '2026-04-03 19:47:23'),
(55, 'Member', NULL, 'MemberDalay@gmail.com', 'Failed', '::1', '2026-04-03 19:47:28'),
(56, 'Member', NULL, 'MemberDalay@gmail.com', 'Failed', '::1', '2026-04-03 19:47:57'),
(57, 'Member', NULL, 'MemberDalay@gmail.com', 'Failed', '::1', '2026-04-03 19:48:02'),
(58, 'Member', NULL, 'MemberDalay@gmail.com', 'Failed', '::1', '2026-04-03 19:48:03'),
(59, 'Member', 5, 'MemberDalay@gmail.com', 'Success', '::1', '2026-04-03 19:48:18'),
(60, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-03 19:52:23'),
(61, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-03 22:28:25'),
(62, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-03 22:28:27'),
(63, 'Member', 5, 'MemberDalay@gmail.com', 'Success', '::1', '2026-04-03 23:01:57'),
(64, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-04 00:59:20'),
(65, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-04 00:59:29'),
(66, 'Member', 5, 'MemberDalay@gmail.com', 'Success', '::1', '2026-04-04 00:59:59'),
(67, 'Member', 6, 'MemberAdriel@gmail.com', 'Success', '::1', '2026-04-04 01:01:02'),
(68, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-04 01:03:44'),
(69, 'Staff', NULL, 'AdminNyle@gmail.com', 'Failed', '::1', '2026-04-08 22:17:37'),
(70, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-08 22:17:41'),
(71, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-08 22:18:03'),
(72, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-09 00:30:14'),
(73, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-09 00:30:28'),
(74, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-09 00:36:37'),
(75, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-09 03:14:47'),
(76, 'Member', 5, 'MemberDalay@gmail.com', 'Success', '::1', '2026-04-09 03:16:56'),
(77, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-09 04:22:05'),
(78, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-09 04:22:24'),
(79, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-09 04:23:24'),
(80, 'Member', NULL, 'MemberData@gmail.com', 'Failed', '::1', '2026-04-09 04:24:02'),
(81, 'Member', 5, 'MemberDalay@gmail.com', 'Success', '::1', '2026-04-09 04:24:07'),
(82, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-09 04:25:16'),
(83, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-09 04:48:28'),
(84, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-09 04:53:07'),
(85, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-09 04:55:09'),
(86, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-09 04:55:29'),
(87, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-09 04:56:04'),
(88, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-09 04:58:04'),
(89, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 00:57:59'),
(90, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 00:59:21'),
(91, 'Member', NULL, 'AnalStaff@gmail.com', 'Failed', '::1', '2026-04-10 00:59:28'),
(92, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 00:59:40'),
(93, 'Member', NULL, 'AnalLibrary@gmail.com', 'Failed', '::1', '2026-04-10 01:00:44'),
(94, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 01:00:59'),
(95, 'Staff', NULL, 'AnalLibrarian@gmail.com', 'Failed', '::1', '2026-04-10 01:01:17'),
(96, 'Staff', NULL, 'AnalLibrarian@gmail.com', 'Failed', '::1', '2026-04-10 01:01:18'),
(97, 'Staff', 18, 'AnalLibrarian@gmail.com', 'Success', '::1', '2026-04-10 01:01:21'),
(98, 'Member', 5, 'MemberDalay@gmail.com', 'Success', '::1', '2026-04-10 01:06:14'),
(99, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-10 01:07:27'),
(100, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-10 01:08:15'),
(101, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-10 01:08:16'),
(102, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 01:08:26'),
(103, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 01:47:25'),
(104, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 01:53:16'),
(105, 'Staff', NULL, 'CircLib@gmail.com', 'Failed', '::1', '2026-04-10 01:57:36'),
(106, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 01:57:40'),
(107, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 02:08:38'),
(108, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 02:09:07'),
(109, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 02:09:29'),
(110, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-10 02:45:25'),
(111, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 02:48:14'),
(112, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 02:48:56'),
(113, 'Member', 5, 'MemberDalay@gmail.com', 'Success', '::1', '2026-04-10 02:49:13'),
(114, 'Member', 5, 'MemberDalay@gmail.com', 'Success', '::1', '2026-04-10 02:51:10'),
(115, 'Member', 5, 'MemberDalay@gmail.com', 'Success', '::1', '2026-04-10 02:53:22'),
(116, 'Staff', 18, 'AnalLibrarian@gmail.com', 'Success', '::1', '2026-04-10 02:56:19'),
(117, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 02:56:31'),
(118, 'Member', NULL, 'MemberNyle@gmail.com', 'Failed', '::1', '2026-04-10 03:04:06'),
(119, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-10 03:04:10'),
(120, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 03:04:58'),
(121, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 12:33:29'),
(122, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-10 12:36:25'),
(123, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 12:36:34'),
(124, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 12:38:31'),
(125, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 15:33:10'),
(126, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-10 15:37:30'),
(127, 'Member', NULL, 'MemberDalay@gmail.com', 'Failed', '::1', '2026-04-10 15:39:01'),
(128, 'Member', NULL, 'MemberDalay@gmail.com', 'Failed', '::1', '2026-04-10 15:39:24'),
(129, 'Member', NULL, 'MemberDalay@gmail.com', 'Failed', '::1', '2026-04-10 15:39:25'),
(130, 'Member', NULL, 'MemberDalay@gmail.com', 'Failed', '::1', '2026-04-10 15:39:28'),
(131, 'Member', NULL, 'MemberDalay@gmail.com', 'Failed', '::1', '2026-04-10 15:39:29'),
(132, 'Member', NULL, 'MemberDalay@gmail.com', 'Failed', '::1', '2026-04-10 15:39:32'),
(133, 'Member', NULL, 'MemberDalay@gmail.com', 'Failed', '::1', '2026-04-10 15:39:34'),
(134, 'Member', 5, 'MemberDalay@gmail.com', 'Success', '::1', '2026-04-10 15:39:50'),
(135, 'Member', 5, 'MemberDalay@gmail.com', 'Success', '::1', '2026-04-10 15:40:09'),
(136, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 15:40:16'),
(137, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 16:40:30'),
(138, 'Member', 6, 'MemberAdriel@gmail.com', 'Success', '::1', '2026-04-10 16:59:32'),
(139, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 17:05:41'),
(140, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 18:06:05'),
(141, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 18:30:01'),
(142, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-10 18:35:23'),
(143, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 18:52:22'),
(144, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-10 19:07:51'),
(145, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-10 19:17:25'),
(146, 'Staff', 18, 'AnalLibrarian@gmail.com', 'Success', '::1', '2026-04-10 19:53:45'),
(147, 'Staff', 18, 'AnalLibrarian@gmail.com', 'Success', '::1', '2026-04-10 19:54:28'),
(148, 'Member', 5, 'MemberDalay@gmail.com', 'Success', '::1', '2026-04-10 19:55:47'),
(149, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 19:58:35'),
(150, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 20:00:10'),
(151, 'Member', 5, 'MemberDalay@gmail.com', 'Success', '::1', '2026-04-10 20:00:43'),
(152, 'Member', 7, 'Test@gmail.com', 'Success', '::1', '2026-04-10 20:04:02'),
(153, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-10 20:30:58'),
(154, 'Staff', 18, 'AnalLibrarian@gmail.com', 'Success', '::1', '2026-04-10 20:37:14'),
(155, 'Member', 5, 'MemberDalay@gmail.com', 'Success', '::1', '2026-04-10 20:44:31'),
(156, 'Staff', 18, 'AnalLibrarian@gmail.com', 'Success', '::1', '2026-04-10 20:52:33'),
(157, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 21:21:56'),
(158, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 21:24:04'),
(159, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-10 21:36:07'),
(160, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 21:36:58'),
(161, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 21:37:56'),
(162, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 21:40:28'),
(163, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 21:40:44'),
(164, 'Member', NULL, 'NyleTest@gmaill.com', 'Failed', '::1', '2026-04-10 21:41:32'),
(165, 'Member', NULL, 'nyleTest@gmaill.com', 'Failed', '::1', '2026-04-10 21:41:41'),
(166, 'Member', NULL, 'TestNyle@gmaill.com', 'Failed', '::1', '2026-04-10 21:41:46'),
(167, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 21:42:07'),
(168, 'Staff', 19, 'NyleTest@gmail.com', 'Success', '::1', '2026-04-10 21:42:15'),
(169, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 21:46:42'),
(170, 'Staff', 20, 'Nyle2Test@gmail.com', 'Success', '::1', '2026-04-10 21:47:24'),
(171, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 21:51:35'),
(172, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 21:52:04'),
(173, 'Staff', 20, 'Nyle2Test@gmail.com', 'Success', '::1', '2026-04-10 21:53:00'),
(174, 'Staff', 20, 'Nyle2Test@gmail.com', 'Success', '::1', '2026-04-10 21:55:44'),
(175, 'Staff', 20, 'Nyle2Test@gmail.com', 'Success', '::1', '2026-04-10 21:56:50'),
(176, 'Staff', NULL, 'Nyle2Test@gmail.com', 'Failed', '::1', '2026-04-10 21:59:39'),
(177, 'Staff', 20, 'Nyle2Test@gmail.com', 'Success', '::1', '2026-04-10 21:59:45'),
(178, 'Staff', 20, 'Nyle2Test@gmail.com', 'Success', '::1', '2026-04-10 22:00:08'),
(179, 'Staff', 20, 'Nyle2Test@gmail.com', 'Success', '::1', '2026-04-10 22:01:11'),
(180, 'Staff', 19, 'NyleTest@gmail.com', 'Success', '::1', '2026-04-10 22:02:47'),
(181, 'Staff', 20, 'Nyle2Test@gmail.com', 'Success', '::1', '2026-04-10 22:03:31'),
(182, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 22:07:25'),
(183, 'Staff', 20, 'Nyle2Test@gmail.com', 'Success', '::1', '2026-04-10 22:07:53'),
(184, 'Staff', 20, 'Nyle2Test@gmail.com', 'Success', '::1', '2026-04-10 22:08:07'),
(185, 'Staff', 20, 'Nyle2Test@gmail.com', 'Success', '::1', '2026-04-10 22:11:39'),
(186, 'Staff', 20, 'Nyle2Test@gmail.com', 'Success', '::1', '2026-04-10 22:12:06'),
(187, 'Staff', 19, 'NyleTest@gmail.com', 'Success', '::1', '2026-04-10 22:15:06'),
(188, 'Staff', 20, 'Nyle2Test@gmail.com', 'Success', '::1', '2026-04-10 22:15:33'),
(189, 'Staff', 20, 'Nyle2Test@gmail.com', 'Success', '::1', '2026-04-10 22:17:29'),
(190, 'Staff', 19, 'NyleTest@gmail.com', 'Success', '::1', '2026-04-10 22:18:19'),
(191, 'Staff', 20, 'Nyle2Test@gmail.com', 'Success', '::1', '2026-04-10 22:18:32'),
(192, 'Staff', 16, 'CircLib@gmail.com', 'Success', '::1', '2026-04-10 22:19:43'),
(193, 'Staff', 18, 'AnalLibrarian@gmail.com', 'Success', '::1', '2026-04-10 22:19:57'),
(194, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 22:20:34'),
(195, 'Member', NULL, 'Test@gmail.com', 'Failed', '::1', '2026-04-10 22:23:46'),
(196, 'Member', 7, 'Test@gmail.com', 'Success', '::1', '2026-04-10 22:23:50'),
(197, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 22:35:24'),
(198, 'Member', 5, 'MemberDalay@gmail.com', 'Success', '::1', '2026-04-10 22:35:41'),
(199, 'Member', 8, 'Delete@gmail.com', 'Success', '::1', '2026-04-10 22:37:03'),
(200, 'Member', NULL, 'Delete@gmail.com', 'Failed', '::1', '2026-04-10 22:37:30'),
(201, 'Staff', 21, 'Delete@gmail.com', 'Success', '::1', '2026-04-10 22:38:15'),
(202, 'Member', NULL, 'Delete@gmail.com', 'Failed', '::1', '2026-04-10 22:38:45'),
(203, 'Member', NULL, 'MemberDalay@gmail.com', 'Failed', '::1', '2026-04-10 22:40:16'),
(204, 'Member', 5, 'MemberDalay@gmail.com', 'Success', '::1', '2026-04-10 22:40:19'),
(205, 'Staff', 20, 'Nyle2Test@gmail.com', 'Success', '::1', '2026-04-10 22:49:05'),
(206, 'Member', 1, 'MemberNyle@gmail.com', 'Success', '::1', '2026-04-10 22:49:22'),
(207, 'Staff', NULL, 'AdminNyle@gmail.com', 'Failed', '::1', '2026-04-10 22:49:46'),
(208, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 22:49:49'),
(209, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-10 22:55:55'),
(210, 'Member', 5, 'MemberDalay@gmail.com', 'Success', '::1', '2026-04-10 23:29:05'),
(211, 'Member', NULL, 'NyleDalay@gmail.com', 'Failed', '::1', '2026-04-10 23:40:42'),
(212, 'Member', 12, 'NyleDalay@gmail.com', 'Success', '::1', '2026-04-10 23:40:47'),
(213, 'Member', 12, 'NyleDalay@gmail.com', 'Success', '::1', '2026-04-10 23:41:08'),
(214, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-12 14:20:57'),
(215, 'Staff', 3, 'AdminNyle@gmail.com', 'Success', '::1', '2026-04-12 14:21:20');

-- --------------------------------------------------------

--
-- Table structure for table `materiallogs`
--

CREATE TABLE `materiallogs` (
  `LogID` int(11) NOT NULL,
  `StaffID` int(11) NOT NULL,
  `MaterialID` int(11) NOT NULL,
  `Action` enum('Added','Updated','Archived') NOT NULL,
  `LogTime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materiallogs`
--

INSERT INTO `materiallogs` (`LogID`, `StaffID`, `MaterialID`, `Action`, `LogTime`) VALUES
(1, 3, 10, 'Archived', '2026-04-02 02:16:18'),
(2, 3, 9, 'Archived', '2026-04-02 02:16:24'),
(3, 3, 11, 'Added', '2026-04-02 02:16:45'),
(4, 3, 11, 'Archived', '2026-04-02 02:16:50'),
(5, 3, 12, 'Added', '2026-04-02 16:43:22'),
(6, 3, 12, 'Archived', '2026-04-02 16:43:36'),
(7, 3, 13, 'Added', '2026-04-02 17:08:25'),
(8, 3, 13, 'Archived', '2026-04-02 17:11:29'),
(9, 3, 34, 'Added', '2026-04-02 17:29:15'),
(10, 3, 34, 'Archived', '2026-04-02 17:29:18'),
(11, 3, 35, 'Added', '2026-04-02 21:32:03'),
(12, 3, 36, 'Added', '2026-04-03 18:12:34'),
(13, 3, 36, 'Archived', '2026-04-03 18:12:43'),
(14, 3, 35, 'Archived', '2026-04-03 18:12:54'),
(15, 3, 218, 'Added', '2026-04-10 22:39:25'),
(16, 3, 218, 'Archived', '2026-04-10 22:39:32'),
(17, 3, 169, 'Updated', '2026-04-10 23:03:28'),
(18, 3, 169, 'Updated', '2026-04-10 23:25:17');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `MaterialID` int(11) NOT NULL,
  `TypeID` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Author` varchar(100) NOT NULL,
  `ISBN` varchar(20) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Genre` varchar(100) DEFAULT NULL,
  `PublishDate` date DEFAULT NULL,
  `Publisher` varchar(100) NOT NULL,
  `TotalQuantity` int(11) NOT NULL DEFAULT 1,
  `AvailableQuantity` int(11) NOT NULL DEFAULT 1,
  `EbookFormat` enum('EPUB','PDF','AZW3','MOBI') DEFAULT NULL,
  `FilePath` varchar(255) DEFAULT NULL,
  `AccessStart` datetime DEFAULT NULL,
  `AccessEnd` datetime DEFAULT NULL,
  `JournalType` enum('Magazine','Journal','Newspaper') DEFAULT NULL,
  `JournalInterval` enum('Daily','Weekly','Monthly','Quarterly') DEFAULT NULL,
  `AddedBy` int(11) NOT NULL,
  `DateAdded` datetime DEFAULT current_timestamp(),
  `IsArchived` tinyint(4) NOT NULL DEFAULT 0,
  `ArchivedDate` datetime DEFAULT NULL,
  `ReplacementCost` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`MaterialID`, `TypeID`, `Title`, `Author`, `ISBN`, `Description`, `Genre`, `PublishDate`, `Publisher`, `TotalQuantity`, `AvailableQuantity`, `EbookFormat`, `FilePath`, `AccessStart`, `AccessEnd`, `JournalType`, `JournalInterval`, `AddedBy`, `DateAdded`, `IsArchived`, `ArchivedDate`, `ReplacementCost`) VALUES
(6, 1, 'The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 'A story of the fabulously wealthy Jay Gatsby and his love for Daisy Buchanan.', 'Classic Fiction', '1925-04-10', 'Scribner', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-02 02:14:05', 0, NULL, 450.00),
(7, 1, 'To Kill a Mockingbird', 'Harper Lee', '9780061935466', 'A tale of racial injustice and moral growth in the American South.', 'Classic Fiction', '1960-07-11', 'J. B. Lippincott & Co.', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-02 02:14:05', 0, NULL, 380.00),
(8, 1, 'Harry Potter and the Sorcerers Stone', 'J.K. Rowling', '9780590353427', 'A young boy discovers he is a wizard and begins his education at Hogwarts.', 'Fantasy', '1997-06-26', 'Bloomsbury', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-02 02:14:05', 0, NULL, 520.00),
(9, 1, '1984', 'George Orwell', '9780451524935', 'A dystopian novel set in a totalitarian society under constant surveillance.', 'Dystopian Fiction', '1949-06-08', 'Secker & Warburg', 3, 3, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-02 02:14:05', 1, '2026-04-10 18:34:46', 0.00),
(10, 1, 'The Alchemist', 'Paulo Coelho', '9780062315007', 'A young shepherd journeys to find worldly treasure and discovers himself.', 'Adventure Fiction', '1988-01-01', 'HarperCollins', 4, 4, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-02 02:14:05', 1, '2026-04-10 18:34:46', 0.00),
(11, 1, 'nigga', 'nigga', '123', 'nigga', 'Nigga', '1885-12-12', 'Me', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-02 02:16:45', 1, '2026-04-10 18:34:46', 0.00),
(12, 2, 'Ebook', 'Nyle', '123345', 'BookKO', '', '1223-12-02', 'PubicHairs', 8, 8, '', 'assets/ebooks/sample.pdf', '2026-04-02 16:43:00', '2026-04-03 16:44:00', NULL, NULL, 3, '2026-04-02 16:43:22', 1, '2026-04-10 18:34:31', 0.00),
(13, 2, 'eBook', 'book', '1233', 'Nigga', 'Horror', '1512-02-05', 'adwadwa', 1, 1, '', 'assets/ebooks/sample.pdf', '2026-04-03 17:10:00', '2026-04-25 20:12:00', NULL, NULL, 3, '2026-04-02 17:08:25', 1, '2026-04-10 18:34:31', 0.00),
(24, 2, 'The Lean Startup', 'Eric Ries', '9780307887894', 'How entrepreneurs use continuous innovation to create successful businesses.', 'Business', '2011-09-13', 'Crown Business', 999, 999, NULL, 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-02 17:17:47', 0, NULL, 0.00),
(25, 2, 'Atomic Habits', 'James Clear', '9780735211292', 'An easy and proven way to build good habits and break bad ones.', 'Self-Help', '2018-10-16', 'Avery', 999, 999, NULL, 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-02 17:17:47', 0, NULL, 0.00),
(26, 2, 'Clean Code', 'Robert C. Martin', '9780132350884', 'A handbook of agile software craftsmanship.', 'Technology', '2008-08-01', 'Prentice Hall', 999, 999, NULL, 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-02 17:17:47', 0, NULL, 0.00),
(27, 2, 'Sapiens', 'Yuval Noah Harari', '9780062316097', 'A brief history of humankind from ancient times to the present.', 'History', '2015-02-10', 'Harper Perennial', 999, 999, NULL, 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-02 17:17:47', 0, NULL, 0.00),
(28, 2, 'Deep Work', 'Cal Newport', '9781455586691', 'Rules for focused success in a distracted world.', 'Self-Help', '2016-01-05', 'Grand Central Publishing', 999, 999, NULL, 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-02 17:17:47', 0, NULL, 0.00),
(34, 3, 'wadsa', 'awd', '12', '1211', 'Horror', '2010-08-26', 'q124', 1, 1, NULL, NULL, NULL, NULL, 'Magazine', 'Daily', 3, '2026-04-02 17:29:15', 1, '2026-04-10 18:34:46', 0.00),
(35, 3, 'Journal', 'Nyle', '123321', 'Journal', 'Horror', '1998-09-02', 'NiggaPublisher', 6, 7, NULL, NULL, NULL, NULL, 'Newspaper', 'Daily', 3, '2026-04-02 21:32:03', 1, '2026-04-10 18:34:46', 0.00),
(36, 1, 'BookKo', 'Boko', '1231', 'Bokoko', 'Code', '2004-12-05', 'nikker', 3, 3, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-03 18:12:34', 1, '2026-04-10 18:34:46', 0.00),
(158, 1, 'The Pragmatic Programmer', 'David Thomas', '9780135957059', 'A guide to becoming a better software developer.', 'Technology', '1999-10-20', 'Addison-Wesley', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 350.00),
(159, 1, 'Introduction to Algorithms', 'Thomas H. Cormen', '9780262033848', 'Comprehensive introduction to algorithms and data structures.', 'Technology', '2009-07-31', 'MIT Press', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 600.00),
(160, 1, 'Clean Architecture', 'Robert C. Martin', '9780134494166', 'A craftsman guide to software structure and design.', 'Technology', '2017-09-10', 'Prentice Hall', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 550.00),
(161, 1, 'The Hitchhiker\'s Guide to the Galaxy', 'Douglas Adams', '9780345391803', 'A comic science fiction novel about life, the universe and everything.', 'Science Fiction', '1979-10-12', 'Pan Books', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 400.00),
(162, 1, 'Dune', 'Frank Herbert', '9780441013593', 'Epic science fiction novel set on the desert planet Arrakis.', 'Science Fiction', '1965-08-01', 'Chilton Books', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 420.00),
(163, 1, 'Brave New World', 'Aldous Huxley', '9780060850524', 'A dystopian novel set in a futuristic World State.', 'Dystopian Fiction', '1932-01-01', 'Chatto & Windus', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 390.00),
(164, 1, 'The Art of War', 'Sun Tzu', '9781590302255', 'Ancient Chinese military treatise on strategy.', 'Philosophy', '0500-01-01', 'Various', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 320.00),
(165, 1, 'Thinking Fast and Slow', 'Daniel Kahneman', '9780374533557', 'Explores the two systems that drive the way we think.', 'Psychology', '2011-10-25', 'Farrar Straus and Giroux', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 480.00),
(166, 1, 'Man\'s Search for Meaning', 'Viktor Frankl', '9780807014271', 'Psychiatrist\'s memoir of life in Nazi death camps.', 'Psychology', '1946-01-01', 'Beacon Press', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 350.00),
(167, 1, 'The Power of Now', 'Eckhart Tolle', '9781577314806', 'A guide to spiritual enlightenment.', 'Self-Help', '1997-01-01', 'New World Library', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 370.00),
(168, 1, 'Rich Dad Poor Dad', 'Robert Kiyosaki', '9781612680194', 'What the rich teach their kids about money.', 'Finance', '1997-04-01', 'Warner Books', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 410.00),
(169, 1, 'The Lean Startup', 'Eric Ries', '9780307887894', 'How entrepreneurs use continuous innovation.', 'Business', '2011-09-13', 'Crown Business', 4, 4, '', NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '', 3, '2026-04-10 00:53:51', 0, NULL, 430.00),
(170, 1, 'Good to Great', 'Jim Collins', '9780066620992', 'Why some companies make the leap and others don\'t.', 'Business', '2001-10-16', 'HarperBusiness', 3, 3, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 1, '2026-04-10 18:34:46', 0.00),
(171, 1, 'Educated', 'Tara Westover', '9780399590504', 'A memoir about a woman who grew up in a survivalist family.', 'Memoir', '2018-02-20', 'Random House', 4, 4, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 1, '2026-04-10 18:34:46', 0.00),
(172, 1, 'Becoming', 'Michelle Obama', '9781524763138', 'An intimate memoir by the former First Lady.', 'Memoir', '2018-11-13', 'Crown', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 1, '2026-04-10 18:34:46', 0.00),
(173, 1, 'The 48 Laws of Power', 'Robert Greene', '9780140280197', 'A guide to power, strategy and seduction.', 'Philosophy', '1998-09-01', 'Viking Press', 3, 3, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 1, '2026-04-10 18:34:46', 0.00),
(174, 1, 'Meditations', 'Marcus Aurelius', '9780812968255', 'Personal writings of the Roman Emperor.', 'Philosophy', '0180-01-01', 'Various', 6, 6, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 1, '2026-04-10 18:34:46', 0.00),
(175, 1, 'Zero to One', 'Peter Thiel', '9780804139021', 'Notes on startups and how to build the future.', 'Business', '2014-09-16', 'Crown Business', 4, 4, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 1, '2026-04-10 18:34:46', 0.00),
(176, 1, 'The Psychology of Money', 'Morgan Housel', '9780857197689', 'Timeless lessons on wealth, greed, and happiness.', 'Finance', '2020-09-08', 'Harriman House', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 1, '2026-04-10 18:34:46', 0.00),
(177, 1, 'Outliers', 'Malcolm Gladwell', '9780316017930', 'The story of success and what makes high-achievers different.', 'Psychology', '2008-11-18', 'Little Brown', 4, 4, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 1, '2026-04-10 18:34:46', 0.00),
(178, 2, 'You Don\'t Know JS', 'Kyle Simpson', '9781491924464', 'Deep dive into the JavaScript language.', 'Technology', '2015-03-01', 'O\'Reilly Media', 999, 999, 'PDF', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(179, 2, 'Eloquent JavaScript', 'Marijn Haverbeke', '9781593279509', 'Modern introduction to programming with JavaScript.', 'Technology', '2018-12-04', 'No Starch Press', 999, 999, 'PDF', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(180, 2, 'Python Crash Course', 'Eric Matthes', '9781593279288', 'Hands-on, project-based introduction to Python.', 'Technology', '2015-11-01', 'No Starch Press', 999, 999, 'PDF', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(181, 2, 'The DevOps Handbook', 'Gene Kim', '9781942788003', 'How to create world-class agility, reliability and security in tech orgs.', 'Technology', '2016-10-06', 'IT Revolution', 999, 999, 'PDF', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(182, 2, 'Designing Data-Intensive Applications', 'Martin Kleppmann', '9781449373320', 'The big ideas behind reliable, scalable, maintainable systems.', 'Technology', '2017-03-16', 'O\'Reilly Media', 999, 999, 'PDF', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(183, 2, 'The Subtle Art of Not Giving a F*ck', 'Mark Manson', '9780062457714', 'A counterintuitive approach to living a good life.', 'Self-Help', '2016-09-13', 'HarperOne', 999, 999, 'EPUB', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(184, 2, 'Can\'t Hurt Me', 'David Goggins', '9781544512273', 'Master your mind and defy the odds.', 'Self-Help', '2018-12-04', 'Lioncrest Publishing', 999, 999, 'EPUB', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(185, 2, 'Start with Why', 'Simon Sinek', '9781591846444', 'How great leaders inspire everyone to take action.', 'Business', '2009-10-29', 'Portfolio', 999, 999, 'EPUB', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(186, 2, 'Digital Minimalism', 'Cal Newport', '9780525536512', 'Choosing a focused life in a noisy world.', 'Self-Help', '2019-02-05', 'Portfolio', 999, 999, 'EPUB', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(187, 2, 'Never Split the Difference', 'Chris Voss', '9780062407801', 'Negotiating as if your life depended on it.', 'Business', '2016-05-17', 'Harper Business', 999, 999, 'EPUB', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(188, 2, 'A Brief History of Time', 'Stephen Hawking', '9780553380163', 'From the Big Bang to black holes.', 'Science', '1988-04-01', 'Bantam Books', 999, 999, 'PDF', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(189, 2, 'The Gene', 'Siddhartha Mukherjee', '9781476733500', 'An intimate history of genetics.', 'Science', '2016-05-17', 'Scribner', 999, 999, 'PDF', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(190, 2, 'Cosmos', 'Carl Sagan', '9780345539434', 'A personal voyage through the universe.', 'Science', '1980-10-12', 'Random House', 999, 999, 'PDF', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(191, 2, 'The Selfish Gene', 'Richard Dawkins', '9780198788607', 'A landmark work in evolutionary biology.', 'Science', '1976-01-01', 'Oxford University Press', 999, 999, 'PDF', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(192, 2, 'Ikigai', 'Hector Garcia', '9780143130727', 'The Japanese secret to a long and happy life.', 'Self-Help', '2016-08-31', 'Penguin Life', 999, 999, 'EPUB', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(193, 2, 'The Alchemist', 'Paulo Coelho', '9780062315007', 'A young shepherd journeys to find worldly treasure.', 'Adventure Fiction', '1988-01-01', 'HarperCollins', 999, 999, 'EPUB', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(194, 2, 'Tuesdays with Morrie', 'Mitch Albom', '9780767905923', 'An old man, a young man, and life\'s greatest lesson.', 'Memoir', '1997-08-18', 'Doubleday', 999, 999, 'EPUB', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(195, 2, 'The Midnight Library', 'Matt Haig', '9780525559474', 'Between life and death there is a library.', 'Fiction', '2020-08-13', 'Canongate', 999, 999, 'EPUB', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(196, 2, 'Principles', 'Ray Dalio', '9781501124020', 'Life and work principles from the founder of Bridgewater.', 'Business', '2017-09-19', 'Simon & Schuster', 999, 999, 'PDF', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(197, 2, 'The E-Myth Revisited', 'Michael E. Gerber', '9780887307287', 'Why most small businesses don\'t work and what to do about it.', 'Business', '1995-01-01', 'HarperCollins', 999, 999, 'PDF', 'assets/ebooks/sample.pdf', NULL, NULL, NULL, NULL, 3, '2026-04-10 00:53:51', 0, NULL, 0.00),
(198, 3, 'Nature', 'Various Authors', '0028-0836', 'One of the world\'s leading scientific journals.', 'Science', '1869-11-04', 'Springer Nature', 5, 5, NULL, NULL, NULL, NULL, 'Journal', 'Weekly', 3, '2026-04-10 00:53:51', 0, NULL, 250.00),
(199, 3, 'Science Magazine', 'Various Authors', '0036-8075', 'Peer-reviewed academic journal of the AAAS.', 'Science', '1880-01-01', 'AAAS', 5, 5, NULL, NULL, NULL, NULL, 'Journal', 'Weekly', 3, '2026-04-10 00:53:51', 0, NULL, 250.00),
(200, 3, 'IEEE Spectrum', 'Various Authors', '0018-9235', 'Technology magazine of the IEEE.', 'Technology', '1964-01-01', 'IEEE', 5, 5, NULL, NULL, NULL, NULL, 'Magazine', 'Monthly', 3, '2026-04-10 00:53:51', 0, NULL, 250.00),
(201, 3, 'Harvard Business Review', 'Various Authors', '0017-8012', 'Management and business practices journal.', 'Business', '1922-01-01', 'Harvard Business Publishing', 5, 5, NULL, NULL, NULL, NULL, 'Magazine', 'Monthly', 3, '2026-04-10 00:53:51', 0, NULL, 250.00),
(202, 3, 'National Geographic', 'Various Authors', '0027-9358', 'Geography, science, culture and world events.', 'Science', '1888-01-01', 'National Geographic Society', 5, 5, NULL, NULL, NULL, NULL, 'Magazine', 'Monthly', 3, '2026-04-10 00:53:51', 0, NULL, 250.00),
(203, 3, 'The Economist', 'Various Authors', '0013-0613', 'International news and current affairs.', 'Current Events', '1843-09-01', 'The Economist Group', 5, 5, NULL, NULL, NULL, NULL, 'Newspaper', 'Weekly', 3, '2026-04-10 00:53:51', 0, NULL, 250.00),
(204, 3, 'MIT Technology Review', 'Various Authors', '1099-274X', 'Covers technologies and their effects on society.', 'Technology', '1899-01-01', 'MIT', 5, 5, NULL, NULL, NULL, NULL, 'Magazine', 'Monthly', 3, '2026-04-10 00:53:51', 0, NULL, 250.00),
(205, 3, 'Scientific American', 'Various Authors', '0036-8733', 'Popular science magazine covering all sciences.', 'Science', '1845-08-28', 'Springer Nature', 5, 5, NULL, NULL, NULL, NULL, 'Magazine', 'Monthly', 3, '2026-04-10 00:53:51', 0, NULL, 250.00),
(206, 3, 'Time Magazine', 'Various Authors', '0040-781X', 'Weekly news magazine.', 'Current Events', '1923-03-03', 'Time USA LLC', 5, 5, NULL, NULL, NULL, NULL, 'Magazine', 'Weekly', 3, '2026-04-10 00:53:51', 0, NULL, 250.00),
(207, 3, 'Forbes', 'Various Authors', '0015-6914', 'Business and financial news.', 'Business', '1917-09-15', 'Forbes Media', 5, 5, NULL, NULL, NULL, NULL, 'Magazine', 'Monthly', 3, '2026-04-10 00:53:51', 0, NULL, 250.00),
(208, 3, 'Wired', 'Various Authors', '1059-1028', 'Technology, science, culture and business.', 'Technology', '1993-01-01', 'Condé Nast', 5, 5, NULL, NULL, NULL, NULL, 'Magazine', 'Monthly', 3, '2026-04-10 00:53:51', 0, NULL, 250.00),
(209, 3, 'Psychology Today', 'Various Authors', '0033-3107', 'Mental health and behavioral science.', 'Psychology', '1967-04-01', 'Sussex Publishers', 5, 5, NULL, NULL, NULL, NULL, 'Magazine', 'Monthly', 3, '2026-04-10 00:53:51', 0, NULL, 250.00),
(210, 3, 'Popular Science', 'Various Authors', '0161-7370', 'Science and technology for the general public.', 'Science', '1872-01-01', 'Bonnier Corporation', 5, 5, NULL, NULL, NULL, NULL, 'Magazine', 'Monthly', 3, '2026-04-10 00:53:51', 0, NULL, 250.00),
(211, 3, 'The Atlantic', 'Various Authors', '1072-7825', 'Politics, culture, society and arts.', 'Current Events', '1857-11-01', 'The Atlantic Monthly Group', 5, 5, NULL, NULL, NULL, NULL, 'Magazine', 'Monthly', 3, '2026-04-10 00:53:51', 0, NULL, 250.00),
(212, 3, 'New Scientist', 'Various Authors', '0262-4079', 'International science news and features.', 'Science', '1956-11-22', 'New Scientist Ltd', 5, 5, NULL, NULL, NULL, NULL, 'Magazine', 'Weekly', 3, '2026-04-10 00:53:51', 0, NULL, 250.00),
(213, 3, 'Vogue Philippines', 'Various Authors', 'N/A', 'Fashion, beauty, and culture in the Philippines.', 'Lifestyle', '2022-09-01', 'Condé Nast Philippines', 5, 5, NULL, NULL, NULL, NULL, 'Magazine', 'Monthly', 3, '2026-04-10 00:53:51', 1, '2026-04-10 18:34:46', 250.00),
(214, 3, 'BusinessWorld', 'Various Authors', 'N/A', 'Philippine business and financial daily.', 'Business', '1967-01-01', 'BusinessWorld Publishing', 5, 5, NULL, NULL, NULL, NULL, 'Newspaper', 'Daily', 3, '2026-04-10 00:53:51', 1, '2026-04-10 18:34:46', 250.00),
(215, 3, 'Philippine Daily Inquirer', 'Various Authors', 'N/A', 'Major Philippine broadsheet.', 'Current Events', '1985-12-09', 'Inquirer Publications', 5, 5, NULL, NULL, NULL, NULL, 'Newspaper', 'Daily', 3, '2026-04-10 00:53:51', 1, '2026-04-10 18:34:46', 250.00),
(216, 3, 'Discover Magazine', 'Various Authors', '0274-7529', 'Science for the curious.', 'Science', '1980-01-01', 'Kalmbach Publishing', 5, 5, NULL, NULL, NULL, NULL, 'Magazine', 'Monthly', 3, '2026-04-10 00:53:51', 1, '2026-04-10 18:34:46', 250.00),
(217, 3, 'PC Magazine', 'Various Authors', '0888-8507', 'Technology product reviews and news.', 'Technology', '1982-02-01', 'Ziff Davis', 5, 5, NULL, NULL, NULL, NULL, 'Magazine', 'Monthly', 3, '2026-04-10 00:53:51', 1, '2026-04-10 18:34:46', 250.00),
(218, 1, 'DELETE', 'TEST', '', '', '', '0000-00-00', 'DELETE', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-10 22:39:25', 1, '2026-04-10 22:39:32', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `materialtypes`
--

CREATE TABLE `materialtypes` (
  `TypeID` int(11) NOT NULL,
  `TypeName` enum('Book','EBook','Journal') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materialtypes`
--

INSERT INTO `materialtypes` (`TypeID`, `TypeName`) VALUES
(1, 'Book'),
(2, 'EBook'),
(3, 'Journal');

-- --------------------------------------------------------

--
-- Table structure for table `memberlogs`
--

CREATE TABLE `memberlogs` (
  `LogID` int(11) NOT NULL,
  `StaffID` int(11) NOT NULL,
  `AffectedMemberID` int(11) NOT NULL,
  `Action` enum('Created','Updated','Archived') NOT NULL,
  `LogTime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `memberlogs`
--

INSERT INTO `memberlogs` (`LogID`, `StaffID`, `AffectedMemberID`, `Action`, `LogTime`) VALUES
(40, 3, 12, 'Created', '2026-04-10 23:33:33');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `MemberID` int(11) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `DefaultPassword` tinyint(1) NOT NULL DEFAULT 1,
  `StatusID` int(11) NOT NULL,
  `DateCreated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`MemberID`, `FirstName`, `LastName`, `Email`, `Password`, `DefaultPassword`, `StatusID`, `DateCreated`) VALUES
(1, 'Member', 'Nyle', 'MemberNyle@gmail.com', '$2y$10$UC.N4kY1VfgMG58Q1lqvvucPApGn9/PqGb5vWNksd6AhUuzGG7LiW', 0, 1, '2026-04-01 01:04:33'),
(12, 'Nyle', 'Dalay', 'NyleDalay@gmail.com', '$2y$10$c4npp0FtFR.a/czT9Zbvx.EkphhoEESENxq7c1vFjsQt1gKiGBJY2', 0, 1, '2026-04-10 23:33:33');

-- --------------------------------------------------------

--
-- Table structure for table `memberstatus`
--

CREATE TABLE `memberstatus` (
  `StatusID` int(11) NOT NULL,
  `StatusName` enum('Active','Inactive','Suspended') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `memberstatus`
--

INSERT INTO `memberstatus` (`StatusID`, `StatusName`) VALUES
(1, 'Active'),
(2, 'Inactive'),
(3, 'Suspended');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `NotificationID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `UserType` enum('Member','Staff') NOT NULL,
  `Message` varchar(255) NOT NULL,
  `IsRead` tinyint(1) DEFAULT 0,
  `DateCreated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`NotificationID`, `UserID`, `UserType`, `Message`, `IsRead`, `DateCreated`) VALUES
(3, 1, 'Member', 'Your borrowed material \"To Kill a Mockingbird\" has been returned.', 1, '2026-04-03 23:01:04'),
(4, 6, 'Member', 'Your borrowed material \"Clean Code\" has been returned.', 1, '2026-04-04 01:02:28'),
(5, 6, 'Member', 'Your borrowed material \"Clean Code\" has been returned.', 1, '2026-04-04 01:04:23'),
(6, 1, 'Member', 'Your borrowed material \"Clean Code\" has been returned.', 1, '2026-04-04 01:04:57'),
(7, 6, 'Member', 'Your borrowed material \"Clean Code\" has been returned.', 1, '2026-04-04 01:06:24'),
(8, 6, 'Member', 'Your borrowed material \"Sapiens\" has been returned.', 1, '2026-04-04 01:06:26'),
(9, 1, 'Member', 'Your borrowed material \"Clean Code\" has been returned.', 1, '2026-04-04 01:06:28'),
(10, 1, 'Member', 'Your borrowed material \"Sapiens\" has been returned.', 1, '2026-04-04 01:06:30'),
(11, 5, 'Member', 'You have claimed \"To Kill a Mockingbird\". Due date: 2026-04-22. Enjoy!', 1, '2026-04-09 03:15:34'),
(12, 5, 'Member', 'You have claimed \"Clean Code\". Due date: 2026-04-22. Enjoy!', 1, '2026-04-09 03:15:36'),
(13, 5, 'Member', 'You have claimed \"The Lean Startup\". Due date: 2026-04-22. Enjoy!', 1, '2026-04-09 03:16:00'),
(14, 6, 'Member', 'You have claimed \"Clean Code\". Due date: 2026-04-22. Enjoy!', 1, '2026-04-09 03:16:02'),
(15, 6, 'Member', 'You have claimed \"Clean Code\". Due date: 2026-04-22. Enjoy!', 1, '2026-04-09 03:16:05'),
(16, 6, 'Member', 'You have claimed \"Clean Code\". Due date: 2026-04-22. Enjoy!', 1, '2026-04-09 03:16:06'),
(17, 6, 'Member', 'You have claimed \"Sapiens\". Due date: 2026-04-22. Enjoy!', 1, '2026-04-09 03:16:08'),
(18, 6, 'Member', '\"Clean Code\" has been returned.', 1, '2026-04-09 03:28:19'),
(19, 6, 'Member', '\"Clean Code\" has been returned.', 1, '2026-04-09 03:28:20'),
(20, 6, 'Member', '\"Clean Code\" has been returned.', 1, '2026-04-09 03:28:22'),
(21, 6, 'Member', '\"Sapiens\" has been returned.', 1, '2026-04-09 03:28:24'),
(22, 5, 'Member', '\"Clean Code\" has been returned.', 1, '2026-04-09 03:28:26'),
(23, 5, 'Member', '\"The Lean Startup\" has been returned.', 1, '2026-04-09 03:28:27'),
(24, 5, 'Member', '\"To Kill a Mockingbird\" has been returned.', 1, '2026-04-09 03:28:28'),
(25, 5, 'Member', 'Your borrow request has been approved. Claim the book at the library by .', 1, '2026-04-09 03:29:04'),
(26, 5, 'Member', 'You have claimed \"To Kill a Mockingbird\". Due date: 2026-04-22. Enjoy!', 1, '2026-04-09 03:29:29'),
(27, 5, 'Member', 'Your borrow request has been approved. Claim the book at the library by .', 1, '2026-04-09 03:29:49'),
(28, 5, 'Member', 'Your borrow request has been approved. Claim the book at the library by .', 1, '2026-04-09 03:34:06'),
(29, 5, 'Member', 'Your borrow request has been approved. Claim the book at the library by .', 1, '2026-04-09 03:34:48'),
(30, 5, 'Member', 'You have claimed \"To Kill a Mockingbird\". Due date: 2026-04-22. Enjoy!', 1, '2026-04-09 03:34:54'),
(31, 5, 'Member', '\"To Kill a Mockingbird\" has been returned.', 1, '2026-04-09 03:35:06'),
(32, 5, 'Member', '\"To Kill a Mockingbird\" has been returned.', 1, '2026-04-09 03:35:13'),
(33, 1, 'Member', 'Your borrow request has been approved. Claim the book at the library by .', 1, '2026-04-09 04:22:49'),
(34, 5, 'Member', 'Your borrow request has been approved. Claim the book at the library by .', 1, '2026-04-09 04:24:16'),
(35, 5, 'Member', 'You have claimed \"To Kill a Mockingbird\". Due date: 2026-04-22. Enjoy!', 1, '2026-04-09 04:55:18'),
(36, 1, 'Member', 'Your borrow request has been approved. Claim the book at the library by 2026-04-13.', 1, '2026-04-09 04:57:26'),
(37, 1, 'Member', 'You have claimed \"To Kill a Mockingbird\". Due date: 2026-04-22. Enjoy!', 1, '2026-04-09 04:57:32'),
(38, 1, 'Member', '\"To Kill a Mockingbird\" has been returned.', 1, '2026-04-09 04:57:43'),
(39, 5, 'Member', '\"To Kill a Mockingbird\" has been returned.', 1, '2026-04-09 04:57:45'),
(40, 1, 'Member', 'Your borrow request has been approved. Claim the book at the library by 2026-04-11.', 1, '2026-04-09 04:59:41'),
(41, 1, 'Member', 'You have claimed \"Harry Potter and the Sorcerers Stone\". Due date: 2026-04-22. Enjoy!', 1, '2026-04-09 05:00:39'),
(42, 1, 'Member', 'Your borrow request has been approved. Claim the book at the library by 2026-04-17.', 1, '2026-04-09 05:06:29'),
(43, 1, 'Member', '\"Harry Potter and the Sorcerers Stone\" has been returned.', 1, '2026-04-09 05:07:53'),
(44, 1, 'Member', 'You have claimed \"Harry Potter and the Sorcerers Stone\". Due date: 2026-04-22. Enjoy!', 1, '2026-04-09 05:07:55'),
(45, 1, 'Member', 'Your borrow request has been approved. Claim the book at the library by 2026-04-16.', 1, '2026-04-09 05:08:16'),
(46, 3, 'Staff', 'Member reported \"Harry Potter and the Sorcerers Stone\" as Lost. Replacement cost: ₱0.00.', 1, '2026-04-09 05:25:59'),
(47, 16, 'Staff', 'Member reported \"Harry Potter and the Sorcerers Stone\" as Lost. Replacement cost: ₱0.00.', 1, '2026-04-09 05:25:59'),
(48, 1, 'Member', 'You reported \"Harry Potter and the Sorcerers Stone\" as Lost. Fine: ₱0.00.', 1, '2026-04-09 05:25:59'),
(49, 5, 'Member', 'Your borrow request has been approved. Claim the book at the library by 2026-04-12.', 1, '2026-04-10 01:09:19'),
(50, 5, 'Member', 'You have claimed \"The Art of War\". Due date: 2026-04-23. Enjoy!', 1, '2026-04-10 01:09:22'),
(51, 1, 'Member', 'You have claimed \"To Kill a Mockingbird\". Due date: 2026-04-23. Enjoy!', 1, '2026-04-10 03:05:23'),
(52, 5, 'Member', '\"The Art of War\" has been returned.', 1, '2026-04-10 03:05:35'),
(53, 1, 'Member', '\"To Kill a Mockingbird\" has been returned.', 1, '2026-04-10 03:05:38'),
(54, 1, 'Member', 'Your borrow request has been approved. Claim the book at the library by 2026-04-13.', 1, '2026-04-10 12:39:25'),
(55, 1, 'Member', 'You have claimed \"Dune\". Due date: 2026-04-24. Enjoy!', 1, '2026-04-10 15:37:54'),
(56, 1, 'Member', '\"Dune\" has been returned.', 1, '2026-04-10 15:41:08'),
(57, 1, 'Member', 'Your borrow request has been approved. Claim the book at the library by 2026-04-16.', 1, '2026-04-10 15:41:43'),
(58, 5, 'Member', 'Your borrow request has been approved. Claim the book at the library by 2026-04-16.', 0, '2026-04-10 15:41:47'),
(59, 1, 'Member', 'You have claimed \"The 48 Laws of Power\". Due date: 2026-04-24. Enjoy!', 1, '2026-04-10 15:41:58'),
(60, 1, 'Member', '\"The 48 Laws of Power\" has been marked as lost.', 1, '2026-04-10 15:42:02'),
(61, 5, 'Member', 'You have claimed \"Vogue Philippines\". Due date: 2026-04-24. Enjoy!', 0, '2026-04-10 15:42:17'),
(62, 5, 'Member', '\"Vogue Philippines\" has been marked as damaged.', 0, '2026-04-10 15:42:20'),
(63, 6, 'Member', 'Your borrow request has been approved. Claim the book at the library by 2026-04-13.', 0, '2026-04-10 20:01:19'),
(64, 6, 'Member', 'You have claimed \"The Lean Startup\". Due date: 2026-04-24. Enjoy!', 0, '2026-04-10 20:01:55'),
(65, 6, 'Member', '\"The Lean Startup\" has been marked as lost. A replacement fine of ₱430.00 has been applied.', 0, '2026-04-10 20:02:01'),
(66, 7, 'Member', 'Your borrow request has been approved. Claim the book at the library by 2026-04-16.', 1, '2026-04-10 20:12:29'),
(67, 7, 'Member', 'You have claimed \"To Kill a Mockingbird\". Due date: 2026-04-24. Enjoy!', 1, '2026-04-10 20:12:47'),
(68, 7, 'Member', '\"To Kill a Mockingbird\" has been returned.', 1, '2026-04-10 20:12:52'),
(69, 1, 'Member', 'Your borrow request has been approved. Claim the book at the library by 2026-04-16.', 1, '2026-04-10 21:37:20'),
(70, 1, 'Member', 'You have claimed \"To Kill a Mockingbird\". Due date: 2026-04-24. Enjoy!', 1, '2026-04-10 21:37:36'),
(71, 1, 'Member', '\"To Kill a Mockingbird\" has been returned.', 1, '2026-04-10 21:37:47'),
(72, 5, 'Member', 'Your donation of \"Donation\" has been accepted. Thank you!', 0, '2026-04-10 22:40:47');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `SettingID` int(11) NOT NULL,
  `SettingKey` varchar(100) NOT NULL,
  `SettingValue` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`SettingID`, `SettingKey`, `SettingValue`) VALUES
(1, 'overdue_rate_per_day', '5.00'),
(2, 'max_borrow_days', '14'),
(3, 'max_borrow_limit', '3');

-- --------------------------------------------------------

--
-- Table structure for table `stafflogs`
--

CREATE TABLE `stafflogs` (
  `LogID` int(11) NOT NULL,
  `AdminID` int(11) NOT NULL,
  `AffectedStaffID` int(11) NOT NULL,
  `Action` enum('Created','Updated','Archived') NOT NULL,
  `LogTime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stafflogs`
--

INSERT INTO `stafflogs` (`LogID`, `AdminID`, `AffectedStaffID`, `Action`, `LogTime`) VALUES
(38, 3, 23, 'Created', '2026-04-10 23:39:08'),
(39, 3, 24, 'Created', '2026-04-10 23:39:47');

-- --------------------------------------------------------

--
-- Table structure for table `staffroles`
--

CREATE TABLE `staffroles` (
  `RoleID` int(11) NOT NULL,
  `RoleName` enum('Admin','CirculationLibrarian','DataAnalyst') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staffroles`
--

INSERT INTO `staffroles` (`RoleID`, `RoleName`) VALUES
(1, 'Admin'),
(2, 'CirculationLibrarian'),
(4, 'DataAnalyst');

-- --------------------------------------------------------

--
-- Table structure for table `staffs`
--

CREATE TABLE `staffs` (
  `StaffID` int(11) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `DefaultPassword` tinyint(1) NOT NULL DEFAULT 1,
  `RoleID` int(11) NOT NULL,
  `StatusID` int(11) NOT NULL,
  `DateCreated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staffs`
--

INSERT INTO `staffs` (`StaffID`, `FirstName`, `LastName`, `Email`, `Password`, `DefaultPassword`, `RoleID`, `StatusID`, `DateCreated`) VALUES
(3, 'Admin', 'Nyle', 'AdminNyle@gmail.com', '$2b$10$3FkQO8a5Mp3iXt7umHho6.itaHGmOTnYQQnU9mRrKZeZunl1ckQtS', 0, 1, 1, '2026-04-01 01:04:09'),
(16, 'Circ', 'Librarian', 'CircLib@gmail.com', '$2y$10$pdpsQqfQjg8DKaFFWofi..X/s44x9bgxjasEny.lPoUgX08IXpQ2.', 0, 2, 1, '2026-04-02 16:28:46'),
(23, 'Isaac', 'Oldton', 'IsaacLibrarian@gmail.com', '$2y$10$F7Owc301CeIb7Zg6e9nhq.UtRfUenIW57d2.vmT10eEWZjEs/t2yq', 1, 4, 1, '2026-04-10 23:39:08'),
(24, 'Lelouch', 'Britania', 'LelouchLibrarian@gmail.com', '$2y$10$dFePCH5Qt2J9snpo/I7.7uOmcmw7RNk3JLzF4WdP5HqhLnjQPZRzS', 1, 2, 1, '2026-04-10 23:39:47');

-- --------------------------------------------------------

--
-- Table structure for table `staffstatus`
--

CREATE TABLE `staffstatus` (
  `StatusID` int(11) NOT NULL,
  `StatusName` enum('Active','Resigned','Suspended') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staffstatus`
--

INSERT INTO `staffstatus` (`StatusID`, `StatusName`) VALUES
(1, 'Active'),
(2, 'Resigned'),
(3, 'Suspended');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `archives`
--
ALTER TABLE `archives`
  ADD PRIMARY KEY (`ArchiveID`);

--
-- Indexes for table `borrowlogs`
--
ALTER TABLE `borrowlogs`
  ADD PRIMARY KEY (`LogID`),
  ADD KEY `StaffID` (`StaffID`),
  ADD KEY `MemberID` (`MemberID`),
  ADD KEY `MaterialID` (`MaterialID`);

--
-- Indexes for table `borrowrecords`
--
ALTER TABLE `borrowrecords`
  ADD PRIMARY KEY (`RecordID`),
  ADD KEY `RequestID` (`RequestID`),
  ADD KEY `MemberID` (`MemberID`),
  ADD KEY `MaterialID` (`MaterialID`),
  ADD KEY `BorrowedBy` (`BorrowedBy`),
  ADD KEY `borrowrecords_ibfk_5` (`ReturnProcessedBy`);

--
-- Indexes for table `borrowrequests`
--
ALTER TABLE `borrowrequests`
  ADD PRIMARY KEY (`RequestID`),
  ADD KEY `MemberID` (`MemberID`),
  ADD KEY `MaterialID` (`MaterialID`),
  ADD KEY `ProcessedBy` (`ProcessedBy`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`DonationID`),
  ADD KEY `MemberID` (`MemberID`);

--
-- Indexes for table `ebookaccess`
--
ALTER TABLE `ebookaccess`
  ADD PRIMARY KEY (`AccessID`),
  ADD UNIQUE KEY `uniq_member_mat` (`MemberID`,`MaterialID`),
  ADD KEY `MemberID` (`MemberID`),
  ADD KEY `MaterialID` (`MaterialID`);

--
-- Indexes for table `loginlogs`
--
ALTER TABLE `loginlogs`
  ADD PRIMARY KEY (`LogID`);

--
-- Indexes for table `materiallogs`
--
ALTER TABLE `materiallogs`
  ADD PRIMARY KEY (`LogID`),
  ADD KEY `StaffID` (`StaffID`),
  ADD KEY `MaterialID` (`MaterialID`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`MaterialID`),
  ADD KEY `TypeID` (`TypeID`),
  ADD KEY `AddedBy` (`AddedBy`);

--
-- Indexes for table `materialtypes`
--
ALTER TABLE `materialtypes`
  ADD PRIMARY KEY (`TypeID`);

--
-- Indexes for table `memberlogs`
--
ALTER TABLE `memberlogs`
  ADD PRIMARY KEY (`LogID`),
  ADD KEY `StaffID` (`StaffID`),
  ADD KEY `AffectedMemberID` (`AffectedMemberID`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`MemberID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `StatusID` (`StatusID`);

--
-- Indexes for table `memberstatus`
--
ALTER TABLE `memberstatus`
  ADD PRIMARY KEY (`StatusID`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`NotificationID`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`SettingID`),
  ADD UNIQUE KEY `SettingKey` (`SettingKey`);

--
-- Indexes for table `stafflogs`
--
ALTER TABLE `stafflogs`
  ADD PRIMARY KEY (`LogID`),
  ADD KEY `AdminID` (`AdminID`),
  ADD KEY `AffectedStaffID` (`AffectedStaffID`);

--
-- Indexes for table `staffroles`
--
ALTER TABLE `staffroles`
  ADD PRIMARY KEY (`RoleID`);

--
-- Indexes for table `staffs`
--
ALTER TABLE `staffs`
  ADD PRIMARY KEY (`StaffID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `RoleID` (`RoleID`),
  ADD KEY `StatusID` (`StatusID`);

--
-- Indexes for table `staffstatus`
--
ALTER TABLE `staffstatus`
  ADD PRIMARY KEY (`StatusID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `archives`
--
ALTER TABLE `archives`
  MODIFY `ArchiveID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `borrowlogs`
--
ALTER TABLE `borrowlogs`
  MODIFY `LogID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=189;

--
-- AUTO_INCREMENT for table `borrowrecords`
--
ALTER TABLE `borrowrecords`
  MODIFY `RecordID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `borrowrequests`
--
ALTER TABLE `borrowrequests`
  MODIFY `RequestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `DonationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ebookaccess`
--
ALTER TABLE `ebookaccess`
  MODIFY `AccessID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `loginlogs`
--
ALTER TABLE `loginlogs`
  MODIFY `LogID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=216;

--
-- AUTO_INCREMENT for table `materiallogs`
--
ALTER TABLE `materiallogs`
  MODIFY `LogID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `MaterialID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=219;

--
-- AUTO_INCREMENT for table `materialtypes`
--
ALTER TABLE `materialtypes`
  MODIFY `TypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `memberlogs`
--
ALTER TABLE `memberlogs`
  MODIFY `LogID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `MemberID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `memberstatus`
--
ALTER TABLE `memberstatus`
  MODIFY `StatusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `SettingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stafflogs`
--
ALTER TABLE `stafflogs`
  MODIFY `LogID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `staffroles`
--
ALTER TABLE `staffroles`
  MODIFY `RoleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staffs`
--
ALTER TABLE `staffs`
  MODIFY `StaffID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `staffstatus`
--
ALTER TABLE `staffstatus`
  MODIFY `StatusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrowlogs`
--
ALTER TABLE `borrowlogs`
  ADD CONSTRAINT `borrowlogs_ibfk_1` FOREIGN KEY (`StaffID`) REFERENCES `staffs` (`StaffID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `borrowlogs_ibfk_2` FOREIGN KEY (`MemberID`) REFERENCES `members` (`MemberID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `borrowlogs_ibfk_3` FOREIGN KEY (`MaterialID`) REFERENCES `materials` (`MaterialID`) ON UPDATE CASCADE;

--
-- Constraints for table `borrowrecords`
--
ALTER TABLE `borrowrecords`
  ADD CONSTRAINT `borrowrecords_ibfk_1` FOREIGN KEY (`RequestID`) REFERENCES `borrowrequests` (`RequestID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `borrowrecords_ibfk_2` FOREIGN KEY (`MemberID`) REFERENCES `members` (`MemberID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `borrowrecords_ibfk_3` FOREIGN KEY (`MaterialID`) REFERENCES `materials` (`MaterialID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `borrowrecords_ibfk_4` FOREIGN KEY (`BorrowedBy`) REFERENCES `staffs` (`StaffID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `borrowrecords_ibfk_5` FOREIGN KEY (`ReturnProcessedBy`) REFERENCES `staffs` (`StaffID`) ON UPDATE CASCADE;

--
-- Constraints for table `borrowrequests`
--
ALTER TABLE `borrowrequests`
  ADD CONSTRAINT `borrowrequests_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `members` (`MemberID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `borrowrequests_ibfk_2` FOREIGN KEY (`MaterialID`) REFERENCES `materials` (`MaterialID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `borrowrequests_ibfk_3` FOREIGN KEY (`ProcessedBy`) REFERENCES `staffs` (`StaffID`) ON UPDATE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `members` (`MemberID`) ON UPDATE CASCADE;

--
-- Constraints for table `ebookaccess`
--
ALTER TABLE `ebookaccess`
  ADD CONSTRAINT `ebookaccess_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `members` (`MemberID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ebookaccess_ibfk_2` FOREIGN KEY (`MaterialID`) REFERENCES `materials` (`MaterialID`) ON UPDATE CASCADE;

--
-- Constraints for table `materiallogs`
--
ALTER TABLE `materiallogs`
  ADD CONSTRAINT `materiallogs_ibfk_1` FOREIGN KEY (`StaffID`) REFERENCES `staffs` (`StaffID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `materiallogs_ibfk_2` FOREIGN KEY (`MaterialID`) REFERENCES `materials` (`MaterialID`) ON UPDATE CASCADE;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`TypeID`) REFERENCES `materialtypes` (`TypeID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `materials_ibfk_2` FOREIGN KEY (`AddedBy`) REFERENCES `staffs` (`StaffID`) ON UPDATE CASCADE;

--
-- Constraints for table `memberlogs`
--
ALTER TABLE `memberlogs`
  ADD CONSTRAINT `memberlogs_ibfk_1` FOREIGN KEY (`StaffID`) REFERENCES `staffs` (`StaffID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `memberlogs_ibfk_2` FOREIGN KEY (`AffectedMemberID`) REFERENCES `members` (`MemberID`) ON UPDATE CASCADE;

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`StatusID`) REFERENCES `memberstatus` (`StatusID`) ON UPDATE CASCADE;

--
-- Constraints for table `stafflogs`
--
ALTER TABLE `stafflogs`
  ADD CONSTRAINT `stafflogs_ibfk_1` FOREIGN KEY (`AdminID`) REFERENCES `staffs` (`StaffID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `stafflogs_ibfk_2` FOREIGN KEY (`AffectedStaffID`) REFERENCES `staffs` (`StaffID`) ON UPDATE CASCADE;

--
-- Constraints for table `staffs`
--
ALTER TABLE `staffs`
  ADD CONSTRAINT `staffs_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `staffroles` (`RoleID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staffs_ibfk_2` FOREIGN KEY (`StatusID`) REFERENCES `staffstatus` (`StatusID`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
